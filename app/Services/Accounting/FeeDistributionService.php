<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeDistributionLog;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\JournalEntry;
use App\Models\Membership\MemberFee;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeDistributionService
{
    public function __construct(
        protected AccountingService $accountingService
    ) {}

    /**
     * Distribute a fee payment according to applicable rules
     */
    public function distributeFee(MemberFee $fee): FeeDistributionLog
    {
        return DB::transaction(function () use ($fee) {
            $rule = $this->findApplicableRule($fee);

            if (! $rule) {
                return $this->createDistributionLog($fee, null, null, 'failed', 'No applicable distribution rule found');
            }

            try {
                // Validate rule before processing
                $validationErrors = $this->validateRule($rule);
                if (! empty($validationErrors)) {
                    throw new \InvalidArgumentException(implode(', ', $validationErrors));
                }

                $distribution = $rule->calculateDistribution($fee->paid_amount);
                if (empty($distribution)) {
                    throw new \InvalidArgumentException('Rule has no valid distribution items');
                }

                $journalEntry = $this->createJournalEntry($fee, $distribution, $rule);

                return $this->createDistributionLog($fee, $rule->id, $journalEntry->id, 'success', null, $distribution);
            } catch (\Exception $e) {
                Log::error('Fee distribution failed', [
                    'fee_id' => $fee->id,
                    'rule_id' => $rule->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return $this->createDistributionLog($fee, $rule->id, null, 'failed', $e->getMessage(), []);
            }
        });
    }

    /**
     * Find the most applicable rule for a fee
     */
    private function findApplicableRule(MemberFee $fee): ?FeeDistributionRule
    {
        $rules = FeeDistributionRule::where('organization_id', $fee->organization_id)
            ->active()
            ->byFeeType($fee->fee_type)
            ->byPriority()
            ->get();

        foreach ($rules as $rule) {
            if ($rule->appliesTo($fee)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Create journal entry for fee distribution
     */
    private function createJournalEntry(MemberFee $fee, array $distribution, FeeDistributionRule $rule): JournalEntry
    {
        // Create journal entry (without separate transaction since outer method already has one)
        $attributes = [
            'organization_id' => $fee->organization_id,
            'entry_date' => $fee->paid_date ?? now(),
            'description' => "Fee distribution: {$fee->description} (Member: ".($fee->member->name ?? 'Unknown').')',
            'voucher_type' => 'FEE_DISTRIBUTION',
            'total_amount' => $fee->paid_amount,
            'status' => 'draft',
        ];

        if (! isset($attributes['created_by'])) {
            $userId = \Illuminate\Support\Facades\Auth::id();
            if (! $userId) {
                // Create or get system user for automated entries
                $systemUser = \App\Models\User::firstOrCreate([
                    'email' => 'system@hrm.local',
                ], [
                    'name' => 'System',
                    'password' => bcrypt('password'),
                ]);
                $userId = $systemUser->id;
            }
            $attributes['created_by'] = $userId;
        }

        $journalEntry = JournalEntry::create($attributes);

        // Prepare ledger entries for double-entry bookkeeping
        $ledgerEntries = [];

        // Debit: Cash/Bank account (receiving payment)
        try {
            $cashAccount = $this->getCashAccount($fee->organization_id);
            $ledgerEntries[] = [
                'account' => $cashAccount,
                'type' => 'debit',
                'amount' => $fee->paid_amount,
                'description' => "Fee payment received: {$fee->description}",
            ];
        } catch (\Exception $e) {
            // Cash account not found, return failed distribution without creating journal entry
            throw $e; // Re-throw to be caught by outer try-catch
        }

        // Credits: Distribution to various accounts according to rules
        foreach ($distribution as $item) {
            $account = ChartOfAccount::find($item['account_id']);
            if (! $account) {
                throw new \InvalidArgumentException("Chart of account not found: {$item['account_id']}");
            }

            $ledgerEntries[] = [
                'account' => $account,
                'type' => 'credit',
                'amount' => $item['amount'],
                'description' => "Fee distribution: {$item['type']} - {$item['value']}%",
            ];
        }

        // Post the journal entry
        $journalEntry->post($ledgerEntries);

        return $journalEntry;
    }

    /**
     * Get the default cash/bank account for the organization
     */
    private function getCashAccount(int $organizationId): ChartOfAccount
    {
        // Try to find a cash or bank account
        $cashAccount = ChartOfAccount::where('organization_id', $organizationId)
            ->where(function ($query) {
                $query->where('name', 'like', '%cash%')
                    ->orWhere('name', 'like', '%bank%')
                    ->orWhere('code', 'like', '100%') // Typical asset account codes
                    ->orWhere('type', 'asset');
            })
            ->first();

        if (! $cashAccount) {
            Log::error('No cash/bank account found for organization', ['organization_id' => $organizationId]);
            throw new \RuntimeException('No cash/bank account found for fee distribution');
        }

        Log::info('Cash account found', ['organization_id' => $organizationId, 'account_id' => $cashAccount->id, 'account_name' => $cashAccount->name]);

        return $cashAccount;
    }

    /**
     * Create distribution log entry
     */
    private function createDistributionLog(
        MemberFee $fee,
        ?int $ruleId,
        ?int $journalEntryId,
        string $status,
        ?string $errorMessage,
        ?array $distribution = null
    ): FeeDistributionLog {
        return FeeDistributionLog::create([
            'organization_id' => $fee->organization_id,
            'member_fee_id' => $fee->id,
            'fee_distribution_rule_id' => $ruleId,
            'journal_entry_id' => $journalEntryId,
            'total_amount' => $fee->paid_amount,
            'distribution_breakdown' => $distribution ?? [],
            'status' => $status,
            'error_message' => $errorMessage,
            'distributed_at' => now(),
        ]);
    }

    /**
     * Process multiple fees in batch
     */
    public function distributeBatch(array $feeIds): array
    {
        $results = [];

        foreach ($feeIds as $feeId) {
            $fee = MemberFee::find($feeId);
            if (! $fee) {
                $results[$feeId] = ['success' => false, 'message' => 'Fee not found'];

                continue;
            }

            try {
                $log = $this->distributeFee($fee);
                $results[$feeId] = [
                    'success' => $log->status === 'success',
                    'message' => $log->status === 'success' ? 'Successfully distributed' : $log->error_message,
                    'log_id' => $log->id,
                ];
            } catch (\Exception $e) {
                $results[$feeId] = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Validate distribution rule before saving
     */
    public function validateRule(FeeDistributionRule $rule): array
    {
        $errors = [];

        // Check if rule has items
        if ($rule->items->isEmpty()) {
            $errors[] = 'Rule must have at least one distribution item';
        }

        // Validate distribution items
        $totalPercentage = 0;
        foreach ($rule->items as $item) {
            if (! $item->validate()) {
                $errors[] = "Invalid distribution item: {$item->id}";
            }

            if ($item->distribution_type === 'percentage') {
                $totalPercentage += $item->percentage;
            }
        }

        // Check if total percentage exceeds 100 for percentage-based rules
        if ($rule->rule_type === 'percentage' && $totalPercentage > 100) {
            $errors[] = 'Total percentage distribution cannot exceed 100%';
        }

        // Check if total percentage is less than 100 for percentage-based rules
        if ($rule->rule_type === 'percentage' && $totalPercentage < 100) {
            $errors[] = 'Total percentage distribution should equal 100%';
        }

        return $errors;
    }

    /**
     * Get distribution summary for reporting
     */
    public function getDistributionSummary(int $organizationId, array $filters = []): array
    {
        $query = FeeDistributionLog::where('organization_id', $organizationId)
            ->with(['rule', 'memberFee.member', 'journalEntry']);

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->whereDate('distributed_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('distributed_at', '<=', $filters['date_to']);
        }

        if (isset($filters['fee_type'])) {
            $query->whereHas('memberFee', function ($q) use ($filters) {
                $q->where('fee_type', $filters['fee_type']);
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $logs = $query->orderBy('distributed_at', 'desc')->get();

        return [
            'total_distributed' => $logs->sum('total_amount'),
            'successful_distributions' => $logs->where('status', 'success')->count(),
            'failed_distributions' => $logs->where('status', 'failed')->count(),
            'logs' => $logs,
        ];
    }
}
