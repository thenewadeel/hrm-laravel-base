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
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

// RED: Test successful fee distribution
test('fee distribution service distributes fee successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Auth::login($user);
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    // Ensure no cash accounts exist for this organization to trigger failure
    ChartOfAccount::where('organization_id', $organization->id)
        ->where(function ($query) {
            $query->where('name', 'like', '%cash%')
                ->orWhere('name', 'like', '%bank%')
                ->orWhere('code', 'like', '100%')
                ->orWhere('type', 'asset');
        })
        ->delete();

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'rule_type' => 'percentage',
        'is_active' => true,
    ]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $account1 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $account2 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account1->id,
        'distribution_type' => 'percentage',
        'percentage' => 60,
        'fixed_amount' => null,
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account2->id,
        'distribution_type' => 'percentage',
        'percentage' => 40,
        'fixed_amount' => null,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $result = $service->distributeFee($fee);

    expect($result->status)->toBe('success');
    expect($result->distribution_breakdown)->toHaveCount(2);

    $breakdown = $result->distribution_breakdown;
    $totalDistributed = array_sum(array_map('floatval', array_column($breakdown, 'amount')));
    expect($totalDistributed)->toBe(1000.0); // 600 + 400 = 1000
});

// RED: Test fee distribution with fixed amount rule
test('fee distribution service handles fixed amount distribution', function () {
    $organization = Organization::factory()->create();
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'rule_type' => 'fixed',
        'is_active' => true,
    ]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $account1 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $account2 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account1->id,
        'distribution_type' => 'fixed',
        'fixed_amount' => 600,
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account2->id,
        'distribution_type' => 'fixed',
        'fixed_amount' => 400,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $result = $service->distributeFee($fee);

    expect($result->status)->toBe('success');
    expect($result->distribution_breakdown)->toHaveCount(2);

    $breakdown = $result->distribution_breakdown;
    $totalDistributed = array_sum(array_map('floatval', array_column($breakdown, 'amount')));
    expect($totalDistributed)->toBe(1000.0); // 600 + 400
});

// RED: Test fee distribution with mixed rule types
test('fee distribution service handles mixed distribution types', function () {
    $organization = Organization::factory()->create();
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'rule_type' => 'priority',
        'is_active' => true,
    ]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $account1 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $account2 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account1->id,
        'distribution_type' => 'percentage',
        'percentage' => 80,
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account2->id,
        'distribution_type' => 'fixed',
        'fixed_amount' => 200,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $result = $service->distributeFee($fee);

    expect($result->status)->toBe('success');
    expect($result->distribution_breakdown)->toHaveCount(2);

    $breakdown = $result->distribution_breakdown;
    $totalDistributed = array_sum(array_map('floatval', array_column($breakdown, 'amount')));
    expect($totalDistributed)->toBe(1000.0); // 800 (80% of 1000) + 200 = 1000
});

// RED: Test fee distribution with conditions
test('fee distribution service respects rule conditions', function () {
    $organization = Organization::factory()->create();
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    // Rule with amount condition that doesn't match
    FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'is_active' => true,
        'conditions' => ['min_amount' => 1500, 'max_amount' => 2000],
    ]);

    // Rule with amount condition that matches
    $matchingRule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'is_active' => true,
        'conditions' => ['min_amount' => 500, 'max_amount' => 1500],
    ]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $distributionAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $matchingRule->id,
        'chart_of_account_id' => $distributionAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $result = $service->distributeFee($fee);

    expect($result->status)->toBe('success');
    expect($result->fee_distribution_rule_id)->toBe($matchingRule->id);
});

// RED: Test fee distribution priority handling
test('fee distribution service respects rule priority', function () {
    $organization = Organization::factory()->create();
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    // Low priority rule
    $lowPriorityRule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'is_active' => true,
        'priority' => 10,
    ]);

    // High priority rule
    $highPriorityRule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'is_active' => true,
        'priority' => 1,
    ]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $distributionAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $lowPriorityRule->id,
        'chart_of_account_id' => $distributionAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $highPriorityRule->id,
        'chart_of_account_id' => $distributionAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $result = $service->distributeFee($fee);

    expect($result->status)->toBe('success');
    expect($result->fee_distribution_rule_id)->toBe($highPriorityRule->id);
});

// RED: Test batch distribution
test('fee distribution service handles batch distribution', function () {
    $organization = Organization::factory()->create();

    $fees = MemberFee::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'is_active' => true,
    ]);

    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
    ]);

    $distributionAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $distributionAccount->id,
        'distribution_type' => 'percentage',
        'percentage' => 100,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $results = $service->distributeBatch($fees->pluck('id')->toArray());

    expect($results)->toHaveCount(3);

    foreach ($results as $feeId => $result) {
        expect($result['success'])->toBeTrue();
        expect($result['message'])->toBe('Successfully distributed');
        expect($result['log_id'])->not->toBeNull();
    }
});

// RED: Test batch distribution with missing fees
test('fee distribution service handles batch distribution with missing fees', function () {
    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $results = $service->distributeBatch([999, 1000]);

    expect($results)->toHaveCount(2);
    expect($results[999]['success'])->toBeFalse();
    expect($results[999]['message'])->toBe('Fee not found');
    expect($results[1000]['success'])->toBeFalse();
    expect($results[1000]['message'])->toBe('Fee not found');
});

// RED: Test rule validation
test('fee distribution service validates rules correctly', function () {
    $rule = FeeDistributionRule::factory()->fixedBased()->create();

    // Test valid rule with items (fixed-based rules don't need 100% total)
    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'distribution_type' => 'fixed',
        'fixed_amount' => 50,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $errors = $service->validateRule($rule);

    expect($errors)->toBeArray();
    expect($errors)->toBeEmpty(); // Should have no errors for valid rule
});

// RED: Test rule validation with no items
test('fee distribution service detects rules with no items', function () {
    $rule = FeeDistributionRule::factory()->create();

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $errors = $service->validateRule($rule);

    expect($errors)->toContain('Rule must have at least one distribution item');
});

// RED: Test rule validation with invalid percentage total
test('fee distribution service detects invalid percentage totals', function () {
    $rule = FeeDistributionRule::factory()->create(['rule_type' => 'percentage']);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'distribution_type' => 'percentage',
        'percentage' => 80,
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'distribution_type' => 'percentage',
        'percentage' => 30, // Total: 110%
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $errors = $service->validateRule($rule);

    expect($errors)->toContain('Total percentage distribution cannot exceed 100%');
});

// RED: Test rule validation with incomplete percentage
test('fee distribution service detects incomplete percentage totals', function () {
    $rule = FeeDistributionRule::factory()->create(['rule_type' => 'percentage']);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'distribution_type' => 'percentage',
        'percentage' => 80, // Only 80%, should be 100%
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $errors = $service->validateRule($rule);

    expect($errors)->toContain('Total percentage distribution should equal 100%');
});

// RED: Test distribution summary
test('fee distribution service provides distribution summary', function () {
    $organization = Organization::factory()->create();

    // Create successful logs
    FeeDistributionLog::factory()->count(3)->successful()->create([
        'organization_id' => $organization->id,
        'total_amount' => 1000,
    ]);

    // Create failed logs
    FeeDistributionLog::factory()->count(2)->failed()->create([
        'organization_id' => $organization->id,
        'total_amount' => 500,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $summary = $service->getDistributionSummary($organization->id);

    expect($summary['total_distributed'])->toBe(4000.0); // 3 * 1000 + 2 * 500
    expect($summary['successful_distributions'])->toBe(3);
    expect($summary['failed_distributions'])->toBe(2);
    expect($summary['logs'])->toHaveCount(5);
});

// RED: Test distribution summary with filters
test('fee distribution service provides filtered distribution summary', function () {
    $organization = Organization::factory()->create();

    // Create logs with different dates
    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'total_amount' => 1000,
        'distributed_at' => now()->subDays(10),
    ]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'total_amount' => 2000,
        'distributed_at' => now(),
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $summary = $service->getDistributionSummary($organization->id, [
        'date_from' => now()->subDays(5)->format('Y-m-d'),
        'date_to' => now()->addDays(5)->format('Y-m-d'),
    ]);

    expect($summary['total_distributed'])->toBe(2000.0);
    expect($summary['successful_distributions'])->toBe(1);
    expect($summary['logs'])->toHaveCount(1);
});

// RED: Test transaction rollback on failure
test('fee distribution service rolls back transaction on failure', function () {
    $organization = Organization::factory()->create();
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    // Create rule with no items to trigger validation failure
    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
        'is_active' => true,
    ]);

    $initialLogCount = FeeDistributionLog::count();
    $initialJournalCount = JournalEntry::count();

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $result = $service->distributeFee($fee);

    expect($result->status)->toBe('failed');
    expect(FeeDistributionLog::count())->toBe($initialLogCount + 1); // Only the failed log
    expect(JournalEntry::count())->toBe($initialJournalCount); // No journal entry created
});

// RED: Test organization isolation
test('fee distribution service respects organization isolation', function () {
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();

    $fee = MemberFee::factory()->create([
        'organization_id' => $organization1->id,
        'fee_type' => 'subscription',
        'amount' => 1000,
        'paid_amount' => 1000,
    ]);

    // Create rule for different organization
    FeeDistributionRule::factory()->create([
        'organization_id' => $organization2->id,
        'fee_type' => 'subscription',
        'is_active' => true,
    ]);

    $service = new FeeDistributionService(app(\App\Services\AccountingService::class));
    $result = $service->distributeFee($fee);

    expect($result->status)->toBe('failed');
    expect($result->error_message)->toBe('No applicable distribution rule found');
});
