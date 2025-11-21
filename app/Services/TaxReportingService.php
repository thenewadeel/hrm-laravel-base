<?php

namespace App\Services;

use App\Models\Accounting\TaxCalculation;
use App\Models\Accounting\TaxFiling;
use App\Models\Accounting\TaxRate;
use Carbon\Carbon;

class TaxReportingService
{
    public function generateTaxReport(int $organizationId, string $startDate, string $endDate, ?string $taxType = null): array
    {
        $query = TaxCalculation::where('organization_id', $organizationId)
            ->whereBetween('calculation_date', [$startDate, $endDate])
            ->with(['taxRate', 'exemption', 'calculable']);

        if ($taxType) {
            $query->whereHas('taxRate', function ($q) use ($taxType) {
                $q->where('type', $taxType);
            });
        }

        $calculations = $query->get();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1,
            ],
            'summary' => [
                'total_transactions' => $calculations->pluck('calculable_id')->unique()->count(),
                'total_base_amount' => $calculations->sum('base_amount'),
                'total_taxable_amount' => $calculations->sum('taxable_amount'),
                'total_tax_collected' => $calculations->sum('tax_amount'),
                'total_exemptions' => $calculations->sum(fn ($calc) => $calc->base_amount - $calc->taxable_amount),
                'average_tax_rate' => $calculations->avg('tax_rate'),
            ],
            'by_tax_type' => $calculations->groupBy('taxRate.type')->map(function ($group) {
                return [
                    'tax_type' => $group->first()->taxRate->getTypeDisplayName(),
                    'total_base_amount' => $group->sum('base_amount'),
                    'total_tax_amount' => $group->sum('tax_amount'),
                    'transaction_count' => $group->pluck('calculable_id')->unique()->count(),
                    'average_rate' => $group->avg('tax_rate'),
                ];
            }),
            'by_tax_rate' => $calculations->groupBy('tax_rate_id')->map(function ($group) {
                $taxRate = $group->first()->taxRate;

                return [
                    'tax_rate_name' => $taxRate->name,
                    'tax_rate' => $taxRate->rate,
                    'tax_type' => $taxRate->getTypeDisplayName(),
                    'total_base_amount' => $group->sum('base_amount'),
                    'total_tax_amount' => $group->sum('tax_amount'),
                    'transaction_count' => $group->pluck('calculable_id')->unique()->count(),
                ];
            }),
            'exemptions' => $calculations->whereNotNull('tax_exemption_id')->groupBy('tax_exemption_id')->map(function ($group) {
                $exemption = $group->first()->exemption;

                return [
                    'exemption_type' => $exemption->getExemptionTypeDisplayName(),
                    'certificate_number' => $exemption->certificate_number,
                    'total_exemption_amount' => $group->sum(fn ($calc) => $calc->base_amount - $calc->taxable_amount),
                    'transaction_count' => $group->count(),
                ];
            }),
            'monthly_breakdown' => $calculations->groupBy(function ($calc) {
                return Carbon::parse($calc->calculation_date)->format('Y-m');
            })->map(function ($group) {
                return [
                    'month' => Carbon::parse($group->first()->calculation_date)->format('F Y'),
                    'total_tax_amount' => $group->sum('tax_amount'),
                    'transaction_count' => $group->pluck('calculable_id')->unique()->count(),
                ];
            }),
        ];
    }

    public function generateTaxLiabilityReport(int $organizationId, string $asOfDate): array
    {
        $taxRates = TaxRate::where('organization_id', $organizationId)
            ->active()
            ->with(['calculations' => function ($query) use ($asOfDate) {
                $query->where('calculation_date', '<=', $asOfDate);
            }])
            ->get();

        return [
            'as_of_date' => $asOfDate,
            'liabilities' => $taxRates->map(function ($taxRate) {
                $totalCollected = $taxRate->calculations->sum('tax_amount');
                $totalPaid = TaxFiling::where('organization_id', $organizationId)
                    ->where('tax_rate_id', $taxRate->id)
                    ->where('status', 'paid')
                    ->sum('tax_due');

                return [
                    'tax_rate_name' => $taxRate->name,
                    'tax_type' => $taxRate->getTypeDisplayName(),
                    'rate' => $taxRate->rate,
                    'total_collected' => $totalCollected,
                    'total_paid' => $totalPaid,
                    'outstanding_liability' => $totalCollected - $totalPaid,
                    'transaction_count' => $taxRate->calculations->pluck('calculable_id')->unique()->count(),
                ];
            }),
            'total_liability' => $taxRates->sum(function ($taxRate) {
                $totalCollected = $taxRate->calculations->sum('tax_amount');
                $totalPaid = TaxFiling::where('organization_id', $organizationId)
                    ->where('tax_rate_id', $taxRate->id)
                    ->where('status', 'paid')
                    ->sum('tax_due');

                return $totalCollected - $totalPaid;
            }),
        ];
    }

    public function generateFilingScheduleReport(int $organizationId, int $monthsAhead = 12): array
    {
        $taxRates = TaxRate::where('organization_id', $organizationId)
            ->active()
            ->with('jurisdiction')
            ->get();

        $schedule = collect();

        foreach ($taxRates as $taxRate) {
            $frequency = $taxRate->jurisdiction?->getFilingFrequency() ?? 'quarterly';
            $dueDays = $taxRate->jurisdiction?->getDueDays() ?? 30;

            $periods = $this->generateFilingPeriods($frequency, now(), $monthsAhead);

            foreach ($periods as $period) {
                $dueDate = Carbon::parse($period['end'])->addDays($dueDays);

                $existingFiling = TaxFiling::where('organization_id', $organizationId)
                    ->where('tax_rate_id', $taxRate->id)
                    ->where('period_start', $period['start'])
                    ->where('period_end', $period['end'])
                    ->first();

                $schedule->push([
                    'tax_rate_name' => $taxRate->name,
                    'tax_type' => $taxRate->getTypeDisplayName(),
                    'jurisdiction' => $taxRate->jurisdiction?->name,
                    'filing_frequency' => $frequency,
                    'period_start' => $period['start'],
                    'period_end' => $period['end'],
                    'due_date' => $dueDate->toDateString(),
                    'days_until_due' => now()->diffInDays($dueDate, false),
                    'status' => $existingFiling ? $existingFiling->getStatusDisplayName() : 'Pending',
                    'filing_id' => $existingFiling?->id,
                ]);
            }
        }

        return [
            'organization_id' => $organizationId,
            'generated_date' => now()->toDateString(),
            'months_ahead' => $monthsAhead,
            'schedule' => $schedule->sortBy('due_date')->values(),
            'upcoming_filings' => $schedule->where('days_until_due', '>=', 0)->where('days_until_due', '<=', 30)->count(),
            'overdue_filings' => $schedule->where('days_until_due', '<', 0)->where('status', 'Pending')->count(),
        ];
    }

    public function getTaxMetrics(int $organizationId, string $period = 'year'): array
    {
        $startDate = match ($period) {
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfYear(),
        };

        $calculations = TaxCalculation::where('organization_id', $organizationId)
            ->where('calculation_date', '>=', $startDate)
            ->get();

        return [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => now()->toDateString(),
            'total_tax_collected' => $calculations->sum('tax_amount'),
            'total_transactions_taxed' => $calculations->pluck('calculable_id')->unique()->count(),
            'average_tax_rate' => $calculations->avg('tax_rate'),
            'total_exemptions' => $calculations->whereNotNull('tax_exemption_id')->count(),
            'tax_effectiveness' => $calculations->sum('base_amount') > 0
                ? ($calculations->sum('tax_amount') / $calculations->sum('base_amount')) * 100
                : 0,
            'top_tax_types' => $calculations->groupBy('taxRate.type')
                ->map->sum('tax_amount')
                ->sortDesc()
                ->take(5),
        ];
    }

    private function generateFilingPeriods(string $frequency, Carbon $startDate, int $monthsAhead): array
    {
        $periods = [];
        $current = $startDate->copy();

        for ($i = 0; $i < $monthsAhead; $i++) {
            switch ($frequency) {
                case 'monthly':
                    $periods[] = [
                        'start' => $current->startOfMonth()->toDateString(),
                        'end' => $current->endOfMonth()->toDateString(),
                    ];
                    $current->addMonth();
                    break;
                case 'quarterly':
                    if ($current->month % 3 === 1) {
                        $periods[] = [
                            'start' => $current->startOfQuarter()->toDateString(),
                            'end' => $current->endOfQuarter()->toDateString(),
                        ];
                    }
                    $current->addMonth();
                    break;
                case 'annual':
                    if ($current->month === 1) {
                        $periods[] = [
                            'start' => $current->startOfYear()->toDateString(),
                            'end' => $current->endOfYear()->toDateString(),
                        ];
                    }
                    $current->addMonth();
                    break;
            }
        }

        return $periods;
    }
}
