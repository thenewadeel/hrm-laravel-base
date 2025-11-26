<?php

// app/Services/OutstandingStatementsService.php

namespace App\Services;

use App\Models\Accounting\JournalEntry;
use App\Models\Customer;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OutstandingStatementsService
{
    /**
     * Generate comprehensive accounts receivable outstanding statement with aging.
     */
    public function generateReceivablesStatement(
        ?int $customerId = null,
        ?Carbon $asOfDate = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $asOfDate = $asOfDate ?? now();
        $startDate = $startDate ?? now()->subMonths(3);
        $endDate = $endDate ?? now();

        $query = JournalEntry::query()
            ->with(['customer'])
            ->where('organization_id', auth()->user()->current_organization_id)
            ->where('status', 'posted')
            ->where('voucher_type', 'SALES')
            ->whereNotNull('customer_id')
            ->where('due_date', '<=', $asOfDate);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        }

        $journalEntries = $query->orderBy('due_date')->get();

        // Group by customer and calculate aging
        $customerStatements = $journalEntries->groupBy('customer_id')->map(function ($entries) use ($asOfDate) {
            $customer = $entries->first()->customer;
            $totalOutstanding = 0;
            $agingBuckets = [
                'current' => 0,
                '30_days' => 0,
                '60_days' => 0,
                '90_days' => 0,
            ];

            $detailedEntries = $entries->map(function ($entry) use ($asOfDate, &$totalOutstanding, &$agingBuckets) {
                $outstanding = $entry->total_amount;
                $totalOutstanding += $outstanding;

                $daysOverdue = $asOfDate->diffInDays($entry->due_date, false);
                $daysOverdue = max(0, $daysOverdue);

                // Categorize into aging buckets
                if ($daysOverdue <= 0) {
                    $agingBuckets['current'] += $outstanding;
                } elseif ($daysOverdue <= 30) {
                    $agingBuckets['30_days'] += $outstanding;
                } elseif ($daysOverdue <= 60) {
                    $agingBuckets['60_days'] += $outstanding;
                } else {
                    $agingBuckets['90_days'] += $outstanding;
                }

                return [
                    'id' => $entry->id,
                    'reference_number' => $entry->reference_number,
                    'invoice_number' => $entry->invoice_number,
                    'entry_date' => $entry->entry_date->format('Y-m-d'),
                    'due_date' => $entry->due_date->format('Y-m-d'),
                    'days_overdue' => $daysOverdue,
                    'total_amount' => $outstanding,
                    'description' => $entry->description,
                ];
            });

            return [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                ],
                'total_outstanding' => $totalOutstanding,
                'aging' => $agingBuckets,
                'entries' => $detailedEntries,
            ];
        });

        // Calculate totals
        $totalReceivables = $customerStatements->sum('total_outstanding');
        $totalAging = [
            'current' => $customerStatements->sum('aging.current'),
            '30_days' => $customerStatements->sum('aging.30_days'),
            '60_days' => $customerStatements->sum('aging.60_days'),
            '90_days' => $customerStatements->sum('aging.90_days'),
        ];

        return [
            'type' => 'receivables',
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_customers' => $customerStatements->count(),
                'total_outstanding' => $totalReceivables,
                'aging' => $totalAging,
            ],
            'customer_statements' => $customerStatements->values(),
            'generated_at' => now(),
        ];
    }

    /**
     * Generate accounts receivable aging report (legacy method for backward compatibility)
     */
    public function generateReceivablesAging(?int $customerId = null): Collection
    {
        $statement = $this->generateReceivablesStatement($customerId);

        return collect($statement['customer_statements'])->flatMap(function ($customerStatement) {
            return collect($customerStatement['entries'])->map(function ($entry) use ($customerStatement) {
                return [
                    'customer' => $customerStatement['customer'],
                    'voucher_number' => $entry['reference_number'],
                    'invoice_number' => $entry['invoice_number'],
                    'invoice_date' => Carbon::parse($entry['entry_date']),
                    'due_date' => Carbon::parse($entry['due_date']),
                    'total_amount' => $entry['total_amount'],
                    'balance' => $entry['total_amount'],
                    'days_overdue' => $entry['days_overdue'],
                    'aging_bucket' => $this->getAgingBucket($entry['days_overdue']),
                ];
            });
        });
    }

    /**
     * Generate comprehensive accounts payable outstanding statement with aging.
     */
    public function generatePayablesStatement(
        ?int $vendorId = null,
        ?Carbon $asOfDate = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $asOfDate = $asOfDate ?? now();
        $startDate = $startDate ?? now()->subMonths(3);
        $endDate = $endDate ?? now();

        $query = JournalEntry::query()
            ->with(['vendor'])
            ->where('organization_id', auth()->user()->current_organization_id)
            ->where('status', 'posted')
            ->where('voucher_type', 'PURCHASE')
            ->whereNotNull('vendor_id')
            ->where('due_date', '<=', $asOfDate);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        }

        $journalEntries = $query->orderBy('due_date')->get();

        // Group by vendor and calculate aging
        $vendorStatements = $journalEntries->groupBy('vendor_id')->map(function ($entries) use ($asOfDate) {
            $vendor = $entries->first()->vendor;
            $totalOutstanding = 0;
            $agingBuckets = [
                'current' => 0,
                '30_days' => 0,
                '60_days' => 0,
                '90_days' => 0,
            ];

            $detailedEntries = $entries->map(function ($entry) use ($asOfDate, &$totalOutstanding, &$agingBuckets) {
                $outstanding = $entry->total_amount;
                $totalOutstanding += $outstanding;

                $daysOverdue = $asOfDate->diffInDays($entry->due_date, false);
                $daysOverdue = max(0, $daysOverdue);

                // Categorize into aging buckets
                if ($daysOverdue <= 0) {
                    $agingBuckets['current'] += $outstanding;
                } elseif ($daysOverdue <= 30) {
                    $agingBuckets['30_days'] += $outstanding;
                } elseif ($daysOverdue <= 60) {
                    $agingBuckets['60_days'] += $outstanding;
                } else {
                    $agingBuckets['90_days'] += $outstanding;
                }

                return [
                    'id' => $entry->id,
                    'reference_number' => $entry->reference_number,
                    'invoice_number' => $entry->invoice_number,
                    'entry_date' => $entry->entry_date->format('Y-m-d'),
                    'due_date' => $entry->due_date->format('Y-m-d'),
                    'days_overdue' => $daysOverdue,
                    'total_amount' => $outstanding,
                    'description' => $entry->description,
                ];
            });

            return [
                'vendor' => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'email' => $vendor->email,
                    'phone' => $vendor->phone,
                    'address' => $vendor->address,
                    'payment_terms' => $vendor->payment_terms,
                ],
                'total_outstanding' => $totalOutstanding,
                'aging' => $agingBuckets,
                'entries' => $detailedEntries,
            ];
        });

        // Calculate totals
        $totalPayables = $vendorStatements->sum('total_outstanding');
        $totalAging = [
            'current' => $vendorStatements->sum('aging.current'),
            '30_days' => $vendorStatements->sum('aging.30_days'),
            '60_days' => $vendorStatements->sum('aging.60_days'),
            '90_days' => $vendorStatements->sum('aging.90_days'),
        ];

        return [
            'type' => 'payables',
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_vendors' => $vendorStatements->count(),
                'total_outstanding' => $totalPayables,
                'aging' => $totalAging,
            ],
            'vendor_statements' => $vendorStatements->values(),
            'generated_at' => now(),
        ];
    }

    /**
     * Generate accounts payable aging report (legacy method for backward compatibility)
     */
    public function generatePayablesAging(?int $vendorId = null): Collection
    {
        $statement = $this->generatePayablesStatement($vendorId);

        return collect($statement['vendor_statements'])->flatMap(function ($vendorStatement) {
            return collect($vendorStatement['entries'])->map(function ($entry) use ($vendorStatement) {
                return [
                    'vendor' => $vendorStatement['vendor'],
                    'voucher_number' => $entry['reference_number'],
                    'invoice_number' => $entry['invoice_number'],
                    'invoice_date' => Carbon::parse($entry['entry_date']),
                    'due_date' => Carbon::parse($entry['due_date']),
                    'total_amount' => $entry['total_amount'],
                    'balance' => $entry['total_amount'],
                    'days_overdue' => $entry['days_overdue'],
                    'aging_bucket' => $this->getAgingBucket($entry['days_overdue']),
                ];
            });
        });
    }

    /**
     * Get customer outstanding balance summary
     */
    public function getCustomerOutstandingSummary(): Collection
    {
        return Customer::where('organization_id', auth()->user()->current_organization_id)
            ->withCount(['journalEntries' => function ($query) {
                $query->where('voucher_type', 'SALES')
                    ->where('status', 'posted');
            }])
            ->get()
            ->map(function ($customer) {
                $totalAmount = $customer->journalEntries()
                    ->where('voucher_type', 'SALES')
                    ->where('status', 'posted')
                    ->sum('total_amount');

                return [
                    'customer' => $customer,
                    'total_invoices' => $customer->journal_entries_count,
                    'total_outstanding' => $totalAmount,
                    'average_invoice_value' => $customer->journal_entries_count > 0
                        ? $totalAmount / $customer->journal_entries_count
                        : 0,
                ];
            });
    }

    /**
     * Get vendor outstanding balance summary
     */
    public function getVendorOutstandingSummary(): Collection
    {
        return Vendor::where('organization_id', auth()->user()->current_organization_id)
            ->withCount(['journalEntries' => function ($query) {
                $query->where('voucher_type', 'PURCHASE')
                    ->where('status', 'posted');
            }])
            ->get()
            ->map(function ($vendor) {
                $totalAmount = $vendor->journalEntries()
                    ->where('voucher_type', 'PURCHASE')
                    ->where('status', 'posted')
                    ->sum('total_amount');

                return [
                    'vendor' => $vendor,
                    'total_bills' => $vendor->journal_entries_count,
                    'total_outstanding' => $totalAmount,
                    'average_bill_value' => $vendor->journal_entries_count > 0
                        ? $totalAmount / $vendor->journal_entries_count
                        : 0,
                ];
            });
    }

    /**
     * Get aging bucket based on days overdue
     */
    private function getAgingBucket(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) {
            return 'Current';
        } elseif ($daysOverdue <= 30) {
            return '1-30 Days';
        } elseif ($daysOverdue <= 60) {
            return '31-60 Days';
        } elseif ($daysOverdue <= 90) {
            return '61-90 Days';
        } else {
            return '90+ Days';
        }
    }

    /**
     * Get aging summary for dashboard.
     */
    public function getAgingSummary(): array
    {
        $receivables = $this->generateReceivablesStatement();
        $payables = $this->generatePayablesStatement();

        return [
            'receivables' => [
                'total' => $receivables['summary']['total_outstanding'],
                'aging' => $receivables['summary']['aging'],
            ],
            'payables' => [
                'total' => $payables['summary']['total_outstanding'],
                'aging' => $payables['summary']['aging'],
            ],
        ];
    }

    /**
     * Export statement to array format for PDF/Excel generation.
     */
    public function exportStatement(array $statement, string $format = 'array'): array
    {
        $exportData = [
            'title' => $statement['type'] === 'receivables'
                ? 'Accounts Receivable Outstanding Statement'
                : 'Accounts Payable Outstanding Statement',
            'as_of_date' => $statement['as_of_date'],
            'period' => $statement['period'],
            'summary' => $statement['summary'],
            'generated_at' => $statement['generated_at'],
        ];

        if ($statement['type'] === 'receivables') {
            $exportData['customers'] = $statement['customer_statements']->map(function ($customerStatement) {
                return [
                    'customer_name' => $customerStatement['customer']['name'],
                    'email' => $customerStatement['customer']['email'],
                    'phone' => $customerStatement['customer']['phone'],
                    'total_outstanding' => $customerStatement['total_outstanding'],
                    'current' => $customerStatement['aging']['current'],
                    '30_days' => $customerStatement['aging']['30_days'],
                    '60_days' => $customerStatement['aging']['60_days'],
                    '90_days' => $customerStatement['aging']['90_days'],
                    'entries' => $customerStatement['entries'],
                ];
            });
        } else {
            $exportData['vendors'] = $statement['vendor_statements']->map(function ($vendorStatement) {
                return [
                    'vendor_name' => $vendorStatement['vendor']['name'],
                    'email' => $vendorStatement['vendor']['email'],
                    'phone' => $vendorStatement['vendor']['phone'],
                    'payment_terms' => $vendorStatement['vendor']['payment_terms'],
                    'total_outstanding' => $vendorStatement['total_outstanding'],
                    'current' => $vendorStatement['aging']['current'],
                    '30_days' => $vendorStatement['aging']['30_days'],
                    '60_days' => $vendorStatement['aging']['60_days'],
                    '90_days' => $vendorStatement['aging']['90_days'],
                    'entries' => $vendorStatement['entries'],
                ];
            });
        }

        return $exportData;
    }

    /**
     * Get aging summary totals (legacy method for backward compatibility)
     */
    public function getReceivablesAgingSummary(): array
    {
        $aging = $this->generateReceivablesAging();

        $summary = [
            'Current' => 0,
            '1-30 Days' => 0,
            '31-60 Days' => 0,
            '61-90 Days' => 0,
            '90+ Days' => 0,
            'Total' => 0,
        ];

        foreach ($aging as $item) {
            $bucket = $item['aging_bucket'];
            $summary[$bucket] += $item['balance'];
            $summary['Total'] += $item['balance'];
        }

        return $summary;
    }

    /**
     * Get payables aging summary totals (legacy method for backward compatibility)
     */
    public function getPayablesAgingSummary(): array
    {
        $aging = $this->generatePayablesAging();

        $summary = [
            'Current' => 0,
            '1-30 Days' => 0,
            '31-60 Days' => 0,
            '61-90 Days' => 0,
            '90+ Days' => 0,
            'Total' => 0,
        ];

        foreach ($aging as $item) {
            $bucket = $item['aging_bucket'];
            $summary[$bucket] += $item['balance'];
            $summary['Total'] += $item['balance'];
        }

        return $summary;
    }
}
