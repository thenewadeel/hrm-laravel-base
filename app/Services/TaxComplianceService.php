<?php

namespace App\Services;

use App\Models\Accounting\TaxCalculation;
use App\Models\Accounting\TaxExemption;
use App\Models\Accounting\TaxFiling;
use App\Models\Accounting\TaxJurisdiction;
use App\Models\Accounting\TaxRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaxComplianceService
{
    public function createTaxFiling(int $organizationId, int $taxRateId, string $filingType, string $periodStart, string $periodEnd, ?User $user = null): TaxFiling
    {
        $taxRate = TaxRate::findOrFail($taxRateId);
        $jurisdictionId = $taxRate->tax_jurisdiction_id;

        // Calculate tax amounts for the period
        $calculations = TaxCalculation::where('organization_id', $organizationId)
            ->where('tax_rate_id', $taxRateId)
            ->whereBetween('calculation_date', [$periodStart, $periodEnd])
            ->get();

        $totalTaxCollected = $calculations->sum('tax_amount');

        // Generate filing number
        $filingNumber = $this->generateFilingNumber($taxRate, $filingType, $periodEnd);

        // Calculate due date based on jurisdiction requirements
        $dueDate = $this->calculateDueDate($jurisdictionId, $periodEnd);

        return TaxFiling::create([
            'organization_id' => $organizationId,
            'tax_jurisdiction_id' => $jurisdictionId,
            'tax_rate_id' => $taxRateId,
            'filing_number' => $filingNumber,
            'filing_type' => $filingType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'filing_date' => now(),
            'due_date' => $dueDate,
            'status' => 'draft',
            'total_tax_collected' => $totalTaxCollected,
            'total_tax_paid' => 0,
            'tax_due' => $totalTaxCollected,
            'penalty_amount' => 0,
            'interest_amount' => 0,
            'filing_data' => [
                'transaction_count' => $calculations->pluck('calculable_id')->unique()->count(),
                'calculation_ids' => $calculations->pluck('id'),
                'average_tax_rate' => $calculations->avg('tax_rate'),
            ],
            'created_by' => $user?->id,
        ]);
    }

    public function generateQuarterlyFilings(int $organizationId, string $quarter, string $year, ?User $user = null): Collection
    {
        $filings = collect();
        $taxRates = TaxRate::where('organization_id', $organizationId)
            ->active()
            ->whereHas('jurisdiction', function ($query) {
                $query->whereJsonContains('filing_requirements->frequency', 'quarterly');
            })
            ->get();

        $quarterDates = $this->getQuarterDates($quarter, $year);

        foreach ($taxRates as $taxRate) {
            $filing = $this->createTaxFiling(
                $organizationId,
                $taxRate->id,
                'quarterly',
                $quarterDates['start'],
                $quarterDates['end'],
                $user
            );
            $filings->push($filing);
        }

        return $filings;
    }

    public function checkExpiryDates(): Collection
    {
        $upcomingExpiries = TaxExemption::active()
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where('expiry_date', '>=', now())
            ->with(['exemptible', 'taxRate'])
            ->get();

        return $upcomingExpiries->map(function ($exemption) {
            return [
                'id' => $exemption->id,
                'certificate_number' => $exemption->certificate_number,
                'exemption_type' => $exemption->getExemptionTypeDisplayName(),
                'entity_type' => class_basename($exemption->exemptible_type),
                'entity_name' => $exemption->exemptible?->name ?? 'Unknown',
                'expiry_date' => $exemption->expiry_date,
                'days_until_expiry' => now()->diffInDays($exemption->expiry_date, false),
                'tax_rate' => $exemption->taxRate?->name,
            ];
        });
    }

    public function calculatePenaltiesAndInterest(): void
    {
        $overdueFilings = TaxFiling::overdue()
            ->where('status', '!=', 'paid')
            ->get();

        foreach ($overdueFilings as $filing) {
            $daysOverdue = now()->diffInDays($filing->due_date);

            if ($daysOverdue > 0) {
                $penaltyRate = $this->getPenaltyRate($filing->tax_jurisdiction_id);
                $interestRate = $this->getInterestRate($filing->tax_jurisdiction_id);

                $penaltyAmount = $filing->tax_due * ($penaltyRate / 100);
                $dailyInterest = ($filing->tax_due * ($interestRate / 100)) / 365;
                $interestAmount = $dailyInterest * $daysOverdue;

                $filing->update([
                    'penalty_amount' => $penaltyAmount,
                    'interest_amount' => $interestAmount,
                ]);
            }
        }
    }

    public function validateTaxExemptions(int $organizationId): Collection
    {
        $issues = collect();

        // Check for expired exemptions
        $expiredExemptions = TaxExemption::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->where('expiry_date', '<', now())
            ->get();

        foreach ($expiredExemptions as $exemption) {
            $issues->push([
                'type' => 'expired_exemption',
                'severity' => 'high',
                'message' => "Tax exemption certificate {$exemption->certificate_number} expired on {$exemption->expiry_date}",
                'exemption_id' => $exemption->id,
                'recommendation' => 'Renew exemption certificate or deactivate it',
            ]);
        }

        // Check for duplicate certificates
        $duplicateCertificates = TaxExemption::where('organization_id', $organizationId)
            ->selectRaw('certificate_number, COUNT(*) as count')
            ->groupBy('certificate_number')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateCertificates as $duplicate) {
            $issues->push([
                'type' => 'duplicate_certificate',
                'severity' => 'medium',
                'message' => "Duplicate tax exemption certificate number: {$duplicate->certificate_number}",
                'recommendation' => 'Review and consolidate duplicate certificates',
            ]);
        }

        // Check for missing tax rate mappings
        $unmappedExemptions = TaxExemption::where('organization_id', $organizationId)
            ->whereNull('tax_rate_id')
            ->whereNull('applicable_taxes')
            ->get();

        foreach ($unmappedExemptions as $exemption) {
            $issues->push([
                'type' => 'unmapped_exemption',
                'severity' => 'low',
                'message' => "Exemption {$exemption->certificate_number} has no tax rate or type mappings",
                'exemption_id' => $exemption->id,
                'recommendation' => 'Specify which taxes this exemption applies to',
            ]);
        }

        return $issues;
    }

    public function getComplianceDashboard(int $organizationId): array
    {
        $now = now();

        return [
            'summary' => [
                'total_tax_rates' => TaxRate::where('organization_id', $organizationId)->active()->count(),
                'active_exemptions' => TaxExemption::where('organization_id', $organizationId)->active()->count(),
                'pending_filings' => TaxFiling::where('organization_id', $organizationId)->where('status', 'draft')->count(),
                'overdue_filings' => TaxFiling::where('organization_id', $organizationId)->overdue()->count(),
            ],
            'upcoming_deadlines' => TaxFiling::where('organization_id', $organizationId)
                ->dueSoon(30)
                ->with(['taxRate', 'jurisdiction'])
                ->get()
                ->map(function ($filing) {
                    return [
                        'filing_number' => $filing->filing_number,
                        'tax_type' => $filing->taxRate->getTypeDisplayName(),
                        'jurisdiction' => $filing->jurisdiction->name,
                        'due_date' => $filing->due_date,
                        'days_until_due' => $now->diffInDays($filing->due_date, false),
                        'amount_due' => $filing->getTotalDue(),
                    ];
                }),
            'expiring_exemptions' => $this->checkExpiryDates()->take(10),
            'compliance_issues' => $this->validateTaxExemptions($organizationId)->take(10),
            'recent_filings' => TaxFiling::where('organization_id', $organizationId)
                ->latest()
                ->limit(5)
                ->with(['taxRate', 'creator'])
                ->get()
                ->map(function ($filing) {
                    return [
                        'filing_number' => $filing->filing_number,
                        'tax_type' => $filing->taxRate->getTypeDisplayName(),
                        'period' => "{$filing->period_start} to {$filing->period_end}",
                        'status' => $filing->getStatusDisplayName(),
                        'amount' => $filing->getTotalDue(),
                        'filed_by' => $filing->creator?->name,
                        'filed_date' => $filing->filing_date,
                    ];
                }),
        ];
    }

    private function generateFilingNumber(TaxRate $taxRate, string $filingType, string $periodEnd): string
    {
        $year = Carbon::parse($periodEnd)->year;
        $period = match ($filingType) {
            'monthly' => Carbon::parse($periodEnd)->format('m'),
            'quarterly' => 'Q'.ceil(Carbon::parse($periodEnd)->month / 3),
            'annual' => 'Y',
            default => 'S',
        };

        return sprintf('%s-%s-%s-%04d',
            $taxRate->code,
            $period,
            $year,
            TaxFiling::where('tax_rate_id', $taxRate->id)->count() + 1
        );
    }

    private function calculateDueDate(?int $jurisdictionId, string $periodEnd): string
    {
        if (! $jurisdictionId) {
            return Carbon::parse($periodEnd)->addDays(30)->toDateString();
        }

        $jurisdiction = TaxJurisdiction::find($jurisdictionId);
        $dueDays = $jurisdiction?->getDueDays() ?? 30;

        return Carbon::parse($periodEnd)->addDays($dueDays)->toDateString();
    }

    private function getQuarterDates(string $quarter, string $year): array
    {
        $year = (int) $year;
        $quarter = (int) str_replace('Q', '', $quarter);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        return [
            'start' => Carbon::create($year, $startMonth, 1)->toDateString(),
            'end' => Carbon::create($year, $endMonth, 1)->endOfMonth()->toDateString(),
        ];
    }

    private function getPenaltyRate(?int $jurisdictionId): float
    {
        // Default penalty rate - could be stored in jurisdiction settings
        return 5.0;
    }

    private function getInterestRate(?int $jurisdictionId): float
    {
        // Default interest rate - could be stored in jurisdiction settings
        return 10.0;
    }
}
