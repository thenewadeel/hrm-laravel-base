<?php

namespace App\Services;

use App\Models\Accounting\TaxCalculation;
use App\Models\Accounting\TaxExemption;
use App\Models\Accounting\TaxRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TaxCalculationService
{
    public function calculateTaxes(Model $calculable, float $baseAmount, string $transactionType = 'sales', ?Model $entity = null): Collection
    {
        $taxCalculations = collect();
        $organizationId = $calculable->organization_id ?? request()->user()?->current_organization_id;

        // Get applicable tax rates
        $taxRates = TaxRate::active()
            ->effective()
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($transactionType) {
                $query->where('type', $transactionType)
                    ->orWhere('type', 'other');
            })
            ->orderBy('is_compound') // Non-compound taxes first
            ->get();

        $runningAmount = $baseAmount;

        foreach ($taxRates as $taxRate) {
            // Check for exemptions
            $exemption = $this->findApplicableExemption($taxRate, $entity, $organizationId);
            $exemptionPercentage = $exemption ? $exemption->exemption_percentage : 0;

            // Calculate tax
            $taxableAmount = $runningAmount * (1 - ($exemptionPercentage / 100));
            $taxAmount = $taxRate->calculateTax($taxableAmount, $exemptionPercentage);

            if ($taxAmount > 0) {
                $taxCalculation = TaxCalculation::create([
                    'organization_id' => $organizationId,
                    'calculable_type' => get_class($calculable),
                    'calculable_id' => $calculable->id,
                    'tax_rate_id' => $taxRate->id,
                    'tax_exemption_id' => $exemption?->id,
                    'base_amount' => $baseAmount,
                    'taxable_amount' => $taxableAmount,
                    'tax_rate' => $taxRate->rate,
                    'tax_amount' => $taxAmount,
                    'calculation_date' => now(),
                    'calculation_method' => 'percentage',
                    'calculation_details' => [
                        'exemption_percentage' => $exemptionPercentage,
                        'exemption_reason' => $exemption?->exemption_type,
                        'is_compound' => $taxRate->is_compound,
                    ],
                ]);

                $taxCalculations->push($taxCalculation);

                // Update running amount for compound taxes
                if ($taxRate->is_compound) {
                    $runningAmount += $taxAmount;
                }
            }
        }

        return $taxCalculations;
    }

    public function calculateIncomeTax(float $taxableIncome, int $organizationId): float
    {
        $taxBracket = \App\Models\TaxBracket::active()
            ->effective()
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($taxableIncome) {
                $query->where('min_income', '<=', $taxableIncome)
                    ->where(function ($q) use ($taxableIncome) {
                        $q->whereNull('max_income')
                            ->orWhere('max_income', '>=', $taxableIncome);
                    });
            })
            ->first();

        return $taxBracket ? $taxBracket->calculateTax($taxableIncome) : 0;
    }

    public function recalculateTaxes(Model $calculable): Collection
    {
        // Delete existing calculations
        TaxCalculation::where('calculable_type', get_class($calculable))
            ->where('calculable_id', $calculable->id)
            ->delete();

        // Get base amount from the calculable model
        $baseAmount = $this->extractBaseAmount($calculable);
        $transactionType = $this->extractTransactionType($calculable);
        $entity = $this->extractEntity($calculable);

        return $this->calculateTaxes($calculable, $baseAmount, $transactionType, $entity);
    }

    private function findApplicableExemption(TaxRate $taxRate, ?Model $entity, int $organizationId): ?TaxExemption
    {
        if (! $entity) {
            return null;
        }

        return TaxExemption::active()
            ->forEntity($entity)
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($taxRate) {
                $query->whereNull('tax_rate_id')
                    ->orWhere('tax_rate_id', $taxRate->id);
            })
            ->where(function ($query) use ($taxRate) {
                $query->whereNull('applicable_taxes')
                    ->orWhereJsonContains('applicable_taxes', $taxRate->type);
            })
            ->first();
    }

    private function extractBaseAmount(Model $calculable): float
    {
        return match (class_basename($calculable)) {
            'Voucher' => $calculable->amount ?? 0,
            'Invoice' => $calculable->total ?? 0,
            'PayrollSlip' => $calculable->gross_salary ?? 0,
            default => 0,
        };
    }

    private function extractTransactionType(Model $calculable): string
    {
        return match (class_basename($calculable)) {
            'Voucher' => $calculable->type ?? 'other',
            'Invoice' => 'sales',
            'PayrollSlip' => 'income',
            default => 'other',
        };
    }

    private function extractEntity(Model $calculable): ?Model
    {
        return match (class_basename($calculable)) {
            'Voucher' => null, // Vouchers don't have a specific entity
            'Invoice' => $calculable->customer ?? null,
            'PayrollSlip' => $calculable->employee ?? null,
            default => null,
        };
    }

    public function getTaxSummary(Model $calculable): array
    {
        $calculations = TaxCalculation::where('calculable_type', get_class($calculable))
            ->where('calculable_id', $calculable->id)
            ->with(['taxRate', 'exemption'])
            ->get();

        return [
            'total_tax' => $calculations->sum('tax_amount'),
            'total_base_amount' => $calculations->sum('base_amount'),
            'total_exemptions' => $calculations->sum(fn ($calc) => $calc->base_amount - $calc->taxable_amount),
            'tax_breakdown' => $calculations->map(fn ($calc) => [
                'tax_type' => $calc->taxRate->getTypeDisplayName(),
                'tax_rate' => $calc->tax_rate,
                'tax_amount' => $calc->tax_amount,
                'exemption' => $calc->exemption?->getExemptionTypeDisplayName(),
                'exemption_percentage' => $calc->calculation_details['exemption_percentage'] ?? 0,
            ]),
        ];
    }
}
