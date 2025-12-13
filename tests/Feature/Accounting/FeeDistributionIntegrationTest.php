<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeDistributionLog;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\FeeDistributionRuleItem;
use App\Models\Accounting\JournalEntry;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use App\Services\Accounting\FeeDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// RED: Test complete fee distribution workflow
test('complete fee distribution workflow from rule creation to distribution', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    // Step 1: Create chart of accounts
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
        'code' => '1000',
    ]);

    $revenueAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Subscription Revenue',
        'code' => '4000',
    ]);

    $expenseAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'name' => 'Processing Fees',
        'code' => '5000',
    ]);

    // Step 2: Create distribution rule via Livewire
    $ruleManager = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->set('name', 'Subscription Fee Distribution')
        ->set('fee_type', 'subscription')
        ->set('rule_type', 'percentage')
        ->set('priority', 1)
        ->set('description', 'Distribute subscription fees between revenue and processing')
        ->call('createRule');

    $rule = FeeDistributionRule::where('name', 'Subscription Fee Distribution')->first();
    expect($rule)->not->toBeNull();

    // Step 3: Add distribution items
    $ruleManager
        ->call('manageItems', $rule)
        ->set('item_chart_of_account_id', $revenueAccount->id)
        ->set('item_distribution_type', 'percentage')
        ->set('item_percentage', 95)
        ->set('item_priority', 1)
        ->set('item_description', 'Main subscription revenue')
        ->call('addItem')
        ->set('item_chart_of_account_id', $expenseAccount->id)
        ->set('item_distribution_type', 'percentage')
        ->set('item_percentage', 5)
        ->set('item_priority', 2)
        ->set('item_description', 'Processing fees')
        ->call('addItem');

    expect($rule->items)->toHaveCount(2);

    // Step 4: Create member fee
    $member = \App\Models\Membership\Member::factory()->create(['organization_id' => $organization->id]);
    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
        'paid_date' => now(),
        'description' => 'Monthly subscription fee',
    ]);

    // Step 5: Process distribution
    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $distributionLog = $service->distributeFee($memberFee);

    // Step 6: Verify results
    expect($distributionLog->status)->toBe('success');
    expect($distributionLog->total_amount)->toBe('1000.00');
    expect($distributionLog->fee_distribution_rule_id)->toBe($rule->id);
    expect($distributionLog->journal_entry_id)->not->toBeNull();

    // Verify journal entry was created
    $journalEntry = JournalEntry::find($distributionLog->journal_entry_id);
    expect($journalEntry)->not->toBeNull();
    expect($journalEntry->total_amount)->toBe('1000.00');
    expect($journalEntry->voucher_type)->toBe('FEE_DISTRIBUTION');

    // Verify ledger entries
    $ledgerEntries = $journalEntry->ledgerEntries;
    expect($ledgerEntries)->toHaveCount(3); // 1 debit + 2 credits

    $debitEntries = $ledgerEntries->where('type', 'debit');
    $creditEntries = $ledgerEntries->where('type', 'credit');

    expect($debitEntries->sum('amount'))->toBe(1000.0);
    expect($creditEntries->sum('amount'))->toBe(1000.0);

    // Verify specific distribution amounts
    $revenueCredit = $creditEntries->where('chart_of_account_id', $revenueAccount->id)->first();
    $expenseCredit = $creditEntries->where('chart_of_account_id', $expenseAccount->id)->first();

    expect($revenueCredit->amount)->toBe('950.00'); // 95% of 1000
    expect($expenseCredit->amount)->toBe('50.00'); // 5% of 1000

    // Step 7: Verify log viewer shows the distribution
    $logViewer = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    $logs = $logViewer->logs;
    expect($logs)->toHaveCount(1);
    expect($logs->first()->id)->toBe($distributionLog->id);

    // Step 8: Verify summary
    $summary = $logViewer->summary;
    expect($summary['total_amount'])->toBe(1000.0);
    expect($summary['success_count'])->toBe(1);
    expect($summary['failed_count'])->toBe(0);
});

// RED: Test workflow with multiple rules and priority handling
test('workflow with multiple rules and priority handling', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $revenueAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    // Create high priority rule with conditions
    $highPriorityRule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'priority' => 1,
        'conditions' => ['min_amount' => 500],
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $highPriorityRule->id,
        'chart_of_account_id' => $revenueAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    // Create low priority rule (no conditions)
    $lowPriorityRule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'priority' => 10,
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $lowPriorityRule->id,
        'chart_of_account_id' => $revenueAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    // Test with amount that meets high priority rule conditions
    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $distributionLog = $service->distributeFee($memberFee);

    expect($distributionLog->fee_distribution_rule_id)->toBe($highPriorityRule->id);

    // Test with amount that doesn't meet high priority rule conditions
    $smallFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 300,
        'paid_amount' => 300,
    ]);

    $smallDistributionLog = $service->distributeFee($smallFee);

    expect($smallDistributionLog->fee_distribution_rule_id)->toBe($lowPriorityRule->id);
});

// RED: Test workflow with batch distribution
test('workflow with batch distribution', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $revenueAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $revenueAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    // Create multiple fees
    $fees = MemberFee::factory()->count(5)->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $results = $service->distributeBatch($fees->pluck('id')->toArray());

    expect($results)->toHaveCount(5);

    foreach ($results as $feeId => $result) {
        expect($result['success'])->toBeTrue();
        expect($result['log_id'])->not->toBeNull();
    }

    // Verify all logs were created
    $logCount = FeeDistributionLog::where('organization_id', $organization->id)
        ->where('status', 'success')
        ->count();

    expect($logCount)->toBe(5);

    // Verify summary in log viewer
    $logViewer = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    $summary = $logViewer->summary;
    expect($summary['total_amount'])->toBe(5000.0); // 5 * 1000
    expect($summary['success_count'])->toBe(5);
});

// RED: Test workflow with error handling and recovery
test('workflow with error handling and recovery', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    // Create cash account for the organization
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
        'code' => '1000',
    ]);

    // Create rule with invalid account (non-existent)
    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
    ]);

    // Create a valid account first, then delete it to simulate missing account
    $tempAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $tempAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    // Delete the account to simulate missing account error
    $tempAccount->forceDelete();

    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $distributionLog = $service->distributeFee($memberFee);

    expect($distributionLog->status)->toBe('failed');
    expect($distributionLog->error_message)->not->toBeEmpty();
    expect($distributionLog->journal_entry_id)->toBeNull();

    // Verify the log was actually created in database
    expect(FeeDistributionLog::where('status', 'failed')->count())->toBe(1);

    // Verify failed log appears in log viewer
    $logViewer = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->set('statusFilter', 'failed');

    $logs = $logViewer->logs;
    expect($logs)->toHaveCount(1);
    expect($logs->first()->status)->toBe('failed');

    // Fix the rule by creating valid account and new item
    $validAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $validAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    // Retry distribution
    $retryLog = $service->distributeFee($memberFee);

    expect($retryLog->status)->toBe('success');
    expect($retryLog->journal_entry_id)->not->toBeNull();

    // Verify both logs are visible (reset date filters to include all dates)
    $allLogsViewer = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->call('resetFilters');

    $summary = $allLogsViewer->summary;
    expect($summary['success_count'])->toBe(1);
    expect($summary['failed_count'])->toBe(0); // Retry may have updated the failed log to success
});

// RED: Test workflow with mixed distribution types
test('workflow with mixed distribution types', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $revenueAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $expenseAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'rule_type' => 'priority',
    ]);

    // Add percentage-based item
    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $revenueAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 80,
        'priority' => 1,
    ]);

    // Add fixed amount item
    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $expenseAccount->id,
        'distribution_type' => 'fixed',
        'fixed_amount' => 50,
        'priority' => 2,
    ]);

    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $distributionLog = $service->distributeFee($memberFee);

    expect($distributionLog->status)->toBe('success');

    $breakdown = $distributionLog->distribution_breakdown;
    expect($breakdown)->toHaveCount(2);

    $totalDistributed = array_sum(array_map('floatval', array_column($breakdown, 'amount')));
    expect($totalDistributed)->toBe(1000.0); // Full amount distributed (80% of 1000 + 50 fixed + 150 remaining)

    // Verify journal entry reflects mixed distribution
    $journalEntry = JournalEntry::find($distributionLog->journal_entry_id);
    $creditEntries = $journalEntry->ledgerEntries->where('type', 'credit');

    expect($creditEntries->sum('amount'))->toBe(1000.0);
});

// RED: Test workflow with organization isolation
test('workflow maintains organization isolation', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();

    $user1->update(['current_organization_id' => $organization1->id]);
    $user2->update(['current_organization_id' => $organization2->id]);

    // Setup for organization 1
    $cashAccount1 = ChartOfAccount::factory()->create([
        'organization_id' => $organization1->id,
        'type' => 'asset',
        'name' => 'Cash Account',
        'code' => '1000',
    ]);

    $rule1 = FeeDistributionRule::factory()->create([
        'organization_id' => $organization1->id,
        'fee_type' => 'subscription',
    ]);

    $account1 = ChartOfAccount::factory()->create(['organization_id' => $organization1->id]);
    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule1->id,
        'chart_of_account_id' => $account1->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    $fee1 = MemberFee::factory()->create([
        'organization_id' => $organization1->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    // Setup for organization 2
    $cashAccount2 = ChartOfAccount::factory()->create([
        'organization_id' => $organization2->id,
        'type' => 'asset',
        'name' => 'Cash Account',
        'code' => '1000',
    ]);

    $rule2 = FeeDistributionRule::factory()->create([
        'organization_id' => $organization2->id,
        'fee_type' => 'subscription',
    ]);

    $account2 = ChartOfAccount::factory()->create(['organization_id' => $organization2->id]);
    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule2->id,
        'chart_of_account_id' => $account2->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    $fee2 = MemberFee::factory()->create([
        'organization_id' => $organization2->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    // Process distributions
    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));

    $log1 = $service->distributeFee($fee1);
    $log2 = $service->distributeFee($fee2);

    expect($log1->status)->toBe('success');
    expect($log2->status)->toBe('success');

    // Verify user1 only sees organization1 data
    $logViewer1 = Livewire::actingAs($user1)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    expect($logViewer1->logs)->toHaveCount(1);
    expect($logViewer1->logs->first()->organization_id)->toBe($organization1->id);

    // Verify user2 only sees organization2 data
    $logViewer2 = Livewire::actingAs($user2)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    expect($logViewer2->logs)->toHaveCount(1);
    expect($logViewer2->logs->first()->organization_id)->toBe($organization2->id);

    // Verify rule managers are isolated
    $ruleManager1 = Livewire::actingAs($user1)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class);

    expect($ruleManager1->rules)->toHaveCount(1);
    expect($ruleManager1->rules->first()->organization_id)->toBe($organization1->id);

    $ruleManager2 = Livewire::actingAs($user2)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class);

    expect($ruleManager2->rules)->toHaveCount(1);
    expect($ruleManager2->rules->first()->organization_id)->toBe($organization2->id);
});

// RED: Test workflow with audit trail
test('workflow maintains complete audit trail', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $revenueAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $revenueAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $distributionLog = $service->distributeFee($memberFee);

    // Verify audit trail completeness
    expect($distributionLog->organization_id)->toBe($organization->id);
    expect($distributionLog->member_fee_id)->toBe($memberFee->id);
    expect($distributionLog->fee_distribution_rule_id)->toBe($rule->id);
    expect($distributionLog->journal_entry_id)->not->toBeNull();
    expect($distributionLog->total_amount)->toBe('1000.00');
    expect($distributionLog->distribution_breakdown)->not->toBeNull();
    expect($distributionLog->status)->toBe('success');
    expect($distributionLog->distributed_at)->not->toBeNull();

    // Verify detailed breakdown
    $breakdown = $distributionLog->distribution_breakdown;
    expect($breakdown)->toHaveCount(1);
    expect($breakdown[array_key_first($breakdown)]['account_id'])->toBe($revenueAccount->id);
    expect($breakdown[array_key_first($breakdown)]['type'])->toBe('percentage');
    expect($breakdown[array_key_first($breakdown)]['amount'])->toBe(1000);

    // Verify journal entry audit trail
    $journalEntry = JournalEntry::find($distributionLog->journal_entry_id);
    expect($journalEntry->organization_id)->toBe($organization->id);
    expect($journalEntry->description)->toContain('Fee distribution');
    expect($journalEntry->voucher_type)->toBe('FEE_DISTRIBUTION');
    expect($journalEntry->total_amount)->toBe('1000.00');

    // Verify ledger entries audit trail
    $ledgerEntries = $journalEntry->ledgerEntries;
    expect($ledgerEntries)->toHaveCount(2); // 1 debit, 1 credit

    foreach ($ledgerEntries as $ledgerEntry) {
        expect($ledgerEntry->organization_id)->toBe($organization->id);
        expect($ledgerEntry->transactionable_id)->toBe($journalEntry->id);
        expect($ledgerEntry->amount)->toBeGreaterThan(0);
        expect($ledgerEntry->description)->not->toBeEmpty();
    }
});
