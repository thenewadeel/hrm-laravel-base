<?php
// app/Services/OutstandingStatementsService.php

namespace App\Services;

use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use Illuminate\Support\Collection;

class OutstandingStatementsService
{
    /**
     * Generate accounts receivable aging report
     */
    public function generateReceivablesAging(?int $customerId = null): Collection
    {
        $query = JournalEntry::with(['customer'])
            ->where('organization_id', auth()->user()->current_organization_id)
            ->where('voucher_type', 'SALES')
            ->where('status', 'posted')
            ->whereNotNull('customer_id');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $salesVouchers = $query->get();

        return $salesVouchers->map(function ($voucher) {
            $daysOverdue = $voucher->due_date->isPast() ? now()->diffInDays($voucher->due_date) : 0;
            
            return [
                'customer' => $voucher->customer,
                'voucher_number' => $voucher->reference_number,
                'invoice_number' => $voucher->invoice_number,
                'invoice_date' => $voucher->entry_date,
                'due_date' => $voucher->due_date,
                'total_amount' => $voucher->total_amount,
                'balance' => $voucher->total_amount, // Simplified - no payments tracked yet
                'days_overdue' => max(0, $daysOverdue),
                'aging_bucket' => $this->getAgingBucket($daysOverdue),
            ];
        });
    }

    /**
     * Generate accounts payable aging report
     */
    public function generatePayablesAging(?int $vendorId = null): Collection
    {
        $query = JournalEntry::with(['vendor'])
            ->where('organization_id', auth()->user()->current_organization_id)
            ->where('voucher_type', 'PURCHASE')
            ->where('status', 'posted')
            ->whereNotNull('vendor_id');

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        $purchaseVouchers = $query->get();

        return $purchaseVouchers->map(function ($voucher) {
            $daysOverdue = $voucher->due_date->isPast() ? now()->diffInDays($voucher->due_date) : 0;
            
            return [
                'vendor' => $voucher->vendor,
                'voucher_number' => $voucher->reference_number,
                'invoice_number' => $voucher->invoice_number,
                'invoice_date' => $voucher->entry_date,
                'due_date' => $voucher->due_date,
                'total_amount' => $voucher->total_amount,
                'balance' => $voucher->total_amount, // Simplified - no payments tracked yet
                'days_overdue' => max(0, $daysOverdue),
                'aging_bucket' => $this->getAgingBucket($daysOverdue),
            ];
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
     * Get aging summary totals
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
     * Get payables aging summary totals
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