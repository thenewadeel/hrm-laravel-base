<?php

namespace App\Services\Membership;

use App\Models\Membership\MemberFee;
use App\Models\Membership\Member;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeService
{
    /**
     * Create a new fee for a member
     */
    public function createFee(Member $member, array $feeData): MemberFee
    {
        return DB::transaction(function () use ($member, $feeData) {
            $fee = MemberFee::create([
                'organization_id' => $member->organization_id,
                'member_id' => $member->id,
                'fee_type' => $feeData['fee_type'],
                'description' => $feeData['description'] ?? null,
                'amount' => $feeData['amount'],
                'due_date' => $feeData['due_date'],
                'status' => 'pending',
            ]);
            
            // Distribute to accounting if enabled
            if ($feeData['distribute_to_accounts'] ?? true) {
                $this->distributeFeeToAccounts($fee);
            }
            
            return $fee;
        });
    }

    /**
     * Process fee payment
     */
    public function processFeePayment(MemberFee $fee, array $paymentData): bool
    {
        return DB::transaction(function () use ($fee, $paymentData) {
            $fee->update([
                'status' => 'paid',
                'paid_date' => now(),
                'payment_method' => $paymentData['payment_method'],
                'payment_reference' => $paymentData['payment_reference'] ?? null,
            ]);
            
            // Create accounting entry for payment
            $this->createPaymentAccountingEntry($fee, $paymentData);
            
            return true;
        });
    }

    /**
     * Waive a fee
     */
    public function waiveFee(MemberFee $fee, string $reason = null): bool
    {
        return DB::transaction(function () use ($fee, $reason) {
            $fee->update([
                'status' => 'waived',
                'notes' => ($fee->notes ?? '') . "\n\nWaived: " . ($reason ?? 'No reason provided') . " - " . now()->toDateTimeString()
            ]);
            
            // Create accounting entry for waiver
            $this->createWaiverAccountingEntry($fee, $reason);
            
            return true;
        });
    }

    /**
     * Generate overdue fees for members
     */
    public function generateOverdueFees(int $organizationId): int
    {
        $generatedCount = 0;
        
        // Get unpaid fees that are past due date
        $overdueFees = MemberFee::where('organization_id', $organizationId)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->with('member')
            ->get();
        
        foreach ($overdueFees as $fee) {
            try {
                // Check if late fee already exists for this period
                $existingLateFee = MemberFee::where('organization_id', $organizationId)
                    ->where('member_id', $fee->member_id)
                    ->where('fee_type', 'late_fee')
                    ->where('description', 'like', '%Late fee for fee #' . $fee->id . '%')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->exists();
                
                if (!$existingLateFee) {
                    $lateFeeAmount = $this->calculateLateFeeAmount($fee);
                    
                    if ($lateFeeAmount > 0) {
                        MemberFee::create([
                            'organization_id' => $organizationId,
                            'member_id' => $fee->member_id,
                            'fee_type' => 'late_fee',
                            'description' => 'Late fee for fee #' . $fee->id . ' - ' . $fee->description,
                            'amount' => $lateFeeAmount,
                            'due_date' => now()->addDays(7), // Due in 7 days
                            'status' => 'pending',
                        ]);
                        
                        $generatedCount++;
                    }
                }
                
                // Mark original fee as overdue
                $fee->markAsOverdue();
            } catch (\Exception $e) {
                \Log::error('Failed to generate overdue fee for fee ' . $fee->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $generatedCount;
    }

    /**
     * Get fee statistics for an organization
     */
    public function getFeeStatistics(int $organizationId, array $filters = []): array
    {
        $query = MemberFee::where('organization_id', $organizationId);
        
        // Apply date filters
        if (isset($filters['start_date'])) {
            $query->where('due_date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('due_date', '<=', $filters['end_date']);
        }
        
        $total = $query->count();
        $pending = $query->where('status', 'pending')->count();
        $paid = $query->where('status', 'paid')->count();
        $waived = $query->where('status', 'waived')->count();
        $overdue = $query->overdue()->count();
        
        $totalAmount = $query->sum('amount');
        $paidAmount = $query->where('status', 'paid')->sum('amount');
        $outstandingAmount = $query->where('status', 'pending')->sum('amount');
        
        // Group by fee type
        $feesByType = $query->selectRaw('fee_type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('fee_type')
            ->pluck('total', 'count')
            ->toArray();
        
        // Monthly trend (last 12 months)
        $monthlyTrend = MemberFee::where('organization_id', $organizationId)
            ->where('paid_date', '>=', now()->subMonths(12))
            ->selectRaw('strftime("%Y-%m", paid_date) as month, SUM(amount) as total')
            ->whereNotNull('paid_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
        
        return [
            'total_fees' => $total,
            'pending_fees' => $pending,
            'paid_fees' => $paid,
            'waived_fees' => $waived,
            'overdue_fees' => $overdue,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount,
            'collection_rate' => $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 2) : 0,
            'fees_by_type' => $feesByType,
            'monthly_trend' => $monthlyTrend,
        ];
    }

    /**
     * Get member fee summary
     */
    public function getMemberFeeSummary(Member $member): array
    {
        $fees = $member->fees;
        
        $totalFees = $fees->count();
        $pendingFees = $fees->where('status', 'pending')->count();
        $paidFees = $fees->where('status', 'paid')->count();
        $overdueFees = $fees->where('status', 'overdue')->count();
        
        $totalAmount = $fees->sum('amount');
        $paidAmount = $fees->where('status', 'paid')->sum('amount');
        $outstandingAmount = $fees->where('status', 'pending')->sum('amount');
        
        return [
            'total_fees' => $totalFees,
            'pending_fees' => $pendingFees,
            'paid_fees' => $paidFees,
            'overdue_fees' => $overdueFees,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount,
            'recent_fees' => $fees->sortByDesc('created_at')->take(5),
        ];
    }

    /**
     * Distribute fee to accounting system
     */
    private function distributeFeeToAccounts(MemberFee $fee): void
    {
        // Get or create default accounts for membership fees
        $receivableAccount = $this->getOrCreateAccount($fee->organization_id, 'Membership Fees Receivable', '1200');
        $revenueAccount = $this->getOrCreateAccount($fee->organization_id, 'Membership Revenue', '4000');
        
        // Create journal entry
        $journalEntry = JournalEntry::create([
            'organization_id' => $fee->organization_id,
            'date' => $fee->due_date,
            'description' => "Member Fee: {$fee->member->full_name} - {$fee->description}",
            'reference' => "FEE-{$fee->id}",
            'total_debit' => $fee->amount,
            'total_credit' => $fee->amount,
        ]);
        
        // Create ledger entries
        LedgerEntry::create([
            'organization_id' => $fee->organization_id,
            'journal_entry_id' => $journalEntry->id,
            'chart_of_account_id' => $receivableAccount->id,
            'debit' => $fee->amount,
            'credit' => 0,
            'description' => "Member fee receivable - {$fee->member->full_name}",
        ]);
        
        LedgerEntry::create([
            'organization_id' => $fee->organization_id,
            'journal_entry_id' => $journalEntry->id,
            'chart_of_account_id' => $revenueAccount->id,
            'debit' => 0,
            'credit' => $fee->amount,
            'description' => "Membership revenue - {$fee->member->full_name}",
        ]);
    }

    /**
     * Create payment accounting entry
     */
    private function createPaymentAccountingEntry(MemberFee $fee, array $paymentData): void
    {
        $receivableAccount = $this->getOrCreateAccount($fee->organization_id, 'Membership Fees Receivable', '1200');
        $cashAccount = $this->getOrCreateAccount($fee->organization_id, 'Cash/Bank', '1000');
        
        // Create journal entry for payment
        $journalEntry = JournalEntry::create([
            'organization_id' => $fee->organization_id,
            'date' => now(),
            'description' => "Payment received for member fee - {$fee->member->full_name}",
            'reference' => "PAY-{$fee->id}-{$paymentData['payment_reference']}",
            'total_debit' => $fee->amount,
            'total_credit' => $fee->amount,
        ]);
        
        // Debit receivable (reduce what's owed)
        LedgerEntry::create([
            'organization_id' => $fee->organization_id,
            'journal_entry_id' => $journalEntry->id,
            'chart_of_account_id' => $receivableAccount->id,
            'debit' => $fee->amount,
            'credit' => 0,
            'description' => "Payment received - {$fee->member->full_name}",
        ]);
        
        // Credit cash/bank (reduce cash)
        LedgerEntry::create([
            'organization_id' => $fee->organization_id,
            'journal_entry_id' => $journalEntry->id,
            'chart_of_account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => $fee->amount,
            'description' => "Cash/Bank payment - {$fee->member->full_name}",
        ]);
    }

    /**
     * Create waiver accounting entry
     */
    private function createWaiverAccountingEntry(MemberFee $fee, string $reason = null): void
    {
        $receivableAccount = $this->getOrCreateAccount($fee->organization_id, 'Membership Fees Receivable', '1200');
        $waiverExpenseAccount = $this->getOrCreateAccount($fee->organization_id, 'Fee Waivers', '5000');
        
        // Create journal entry for waiver
        $journalEntry = JournalEntry::create([
            'organization_id' => $fee->organization_id,
            'date' => now(),
            'description' => "Fee waiver - {$fee->member->full_name} - {$reason ?? 'No reason'}",
            'reference' => "WAIVE-{$fee->id}",
            'total_debit' => $fee->amount,
            'total_credit' => $fee->amount,
        ]);
        
        // Debit waiver expense
        LedgerEntry::create([
            'organization_id' => $fee->organization_id,
            'journal_entry_id' => $journalEntry->id,
            'chart_of_account_id' => $waiverExpenseAccount->id,
            'debit' => $fee->amount,
            'credit' => 0,
            'description' => "Fee waiver expense - {$fee->member->full_name}",
        ]);
        
        // Credit receivable (write off what's owed)
        LedgerEntry::create([
            'organization_id' => $fee->organization_id,
            'journal_entry_id' => $journalEntry->id,
            'chart_of_account_id' => $receivableAccount->id,
            'debit' => 0,
            'credit' => $fee->amount,
            'description' => "Fee waiver write-off - {$fee->member->full_name}",
        ]);
    }

    /**
     * Get or create chart of account
     */
    private function getOrCreateAccount(int $organizationId, string $name, string $defaultCode): ChartOfAccount
    {
        return ChartOfAccount::firstOrCreate(
            [
                'organization_id' => $organizationId,
                'code' => $defaultCode,
            ],
            [
                'name' => $name,
                'type' => $defaultCode === '1200' || $defaultCode === '1000' ? 'asset' : 'revenue',
                'category' => match($defaultCode) {
                    '1200' => 'accounts_receivable',
                    '1000' => 'cash_and_bank',
                    '4000' => 'membership_income',
                    '5000' => 'operating_expenses',
                    default => 'other',
                },
            ]
        );
    }

    /**
     * Calculate late fee amount
     */
    private function calculateLateFeeAmount(MemberFee $fee): float
    {
        $daysOverdue = $fee->days_overdue;
        
        // Late fee calculation: 5% of original fee + $1 per day overdue
        $percentageFee = $fee->amount * 0.05;
        $dailyFee = $daysOverdue * 1.00;
        
        return round($percentageFee + $dailyFee, 2);
    }
}