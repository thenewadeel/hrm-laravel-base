<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\FeeDistributionRuleItem;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// RED: Test that FeeDistributionRule can be created with basic attributes
test('fee distribution rule can be created with required fields', function () {
    $organization = Organization::factory()->create();

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'Test Distribution Rule',
        'fee_type' => 'subscription',
        'rule_type' => 'percentage',
        'is_active' => true,
        'priority' => 1,
        'description' => 'Test rule for subscription fees',
    ]);

    expect($rule)->toBeInstanceOf(FeeDistributionRule::class);
    expect($rule->organization_id)->toBe($organization->id);
    expect($rule->name)->toBe('Test Distribution Rule');
    expect($rule->fee_type)->toBe('subscription');
    expect($rule->rule_type)->toBe('percentage');
    expect($rule->is_active)->toBeTrue();
    expect($rule->priority)->toBe(1);
    expect($rule->description)->toBe('Test rule for subscription fees');
});

// RED: Test that rule belongs to organization
test('fee distribution rule belongs to organization', function () {
    $organization = Organization::factory()->create();
    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);

    expect($rule->organization)->toBeInstanceOf(Organization::class);
    expect($rule->organization->id)->toBe($organization->id);
});

// RED: Test that rule has many items
test('fee distribution rule has many items', function () {
    $rule = FeeDistributionRule::factory()->create();
    $item1 = FeeDistributionRuleItem::factory()->create(['fee_distribution_rule_id' => $rule->id, 'priority' => 1]);
    $item2 = FeeDistributionRuleItem::factory()->create(['fee_distribution_rule_id' => $rule->id, 'priority' => 2]);

    expect($rule->items)->toHaveCount(2);
    expect($rule->items->first()->id)->toBe($item1->id);
});

// RED: Test that rule has many distribution logs
test('fee distribution rule has many distribution logs', function () {
    $rule = FeeDistributionRule::factory()->create();
    $log1 = \App\Models\Accounting\FeeDistributionLog::factory()->create(['fee_distribution_rule_id' => $rule->id]);
    $log2 = \App\Models\Accounting\FeeDistributionLog::factory()->create(['fee_distribution_rule_id' => $rule->id]);

    expect($rule->distributionLogs)->toHaveCount(2);
    expect($rule->distributionLogs->first()->id)->toBe($log1->id);
});

// RED: Test active scope
test('active scope returns only active rules', function () {
    $activeRule = FeeDistributionRule::factory()->create(['is_active' => true]);
    $inactiveRule = FeeDistributionRule::factory()->create(['is_active' => false]);

    $activeRules = FeeDistributionRule::active()->get();

    expect($activeRules)->toHaveCount(1);
    expect($activeRules->first()->id)->toBe($activeRule->id);
});

// RED: Test byFeeType scope
test('by fee type scope filters by fee type', function () {
    $subscriptionRule = FeeDistributionRule::factory()->create(['fee_type' => 'subscription']);
    $lateFeeRule = FeeDistributionRule::factory()->create(['fee_type' => 'late_fee']);

    $subscriptionRules = FeeDistributionRule::byFeeType('subscription')->get();

    expect($subscriptionRules)->toHaveCount(1);
    expect($subscriptionRules->first()->id)->toBe($subscriptionRule->id);
});

// RED: Test byPriority scope
test('by priority scope orders by priority', function () {
    $lowPriorityRule = FeeDistributionRule::factory()->create(['priority' => 10]);
    $highPriorityRule = FeeDistributionRule::factory()->create(['priority' => 1]);

    $rules = FeeDistributionRule::byPriority()->get();

    expect($rules->first()->id)->toBe($highPriorityRule->id);
    expect($rules->last()->id)->toBe($lowPriorityRule->id);
});

// RED: Test appliesTo method with matching fee
test('rule applies to matching fee', function () {
    $rule = FeeDistributionRule::factory()->create([
        'fee_type' => 'subscription',
        'is_active' => true,
    ]);

    $fee = MemberFee::factory()->create([
        'fee_type' => 'subscription',
        'amount' => 100,
    ]);

    expect($rule->appliesTo($fee))->toBeTrue();
});

// RED: Test appliesTo method with non-matching fee type
test('rule does not apply to different fee type', function () {
    $rule = FeeDistributionRule::factory()->create([
        'fee_type' => 'subscription',
        'is_active' => true,
    ]);

    $fee = MemberFee::factory()->create([
        'fee_type' => 'late_fee',
        'amount' => 100,
    ]);

    expect($rule->appliesTo($fee))->toBeFalse();
});

// RED: Test appliesTo method with inactive rule
test('inactive rule does not apply to any fee', function () {
    $rule = FeeDistributionRule::factory()->create([
        'fee_type' => 'subscription',
        'is_active' => false,
    ]);

    $fee = MemberFee::factory()->create([
        'fee_type' => 'subscription',
        'amount' => 100,
    ]);

    expect($rule->appliesTo($fee))->toBeFalse();
});

// RED: Test appliesTo method with amount conditions
test('rule applies with amount within conditions', function () {
    $rule = FeeDistributionRule::factory()->create([
        'fee_type' => 'subscription',
        'is_active' => true,
        'conditions' => [
            'min_amount' => 50,
            'max_amount' => 200,
        ],
    ]);

    $fee = MemberFee::factory()->create([
        'fee_type' => 'subscription',
        'amount' => 100,
    ]);

    expect($rule->appliesTo($fee))->toBeTrue();
});

// RED: Test appliesTo method with amount outside conditions
test('rule does not apply with amount outside conditions', function () {
    $rule = FeeDistributionRule::factory()->create([
        'fee_type' => 'subscription',
        'is_active' => true,
        'conditions' => [
            'min_amount' => 50,
            'max_amount' => 200,
        ],
    ]);

    $fee = MemberFee::factory()->create([
        'fee_type' => 'subscription',
        'amount' => 300,
    ]);

    expect($rule->appliesTo($fee))->toBeFalse();
});

// RED: Test calculateDistribution with percentage items
test('calculate distribution with percentage items', function () {
    $rule = FeeDistributionRule::factory()->create();
    $account1 = ChartOfAccount::factory()->create();
    $account2 = ChartOfAccount::factory()->create();

    $item1 = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account1->id,
        'distribution_type' => 'percentage',
        'percentage' => 60,
        'priority' => 1,
    ]);

    $item2 = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account2->id,
        'distribution_type' => 'percentage',
        'percentage' => 40,
        'priority' => 2,
    ]);

    $distribution = $rule->calculateDistribution(1000);

    expect($distribution)->toHaveCount(2);
    expect($distribution[$item1->id]['amount'])->toBe(600.0);
    expect($distribution[$item2->id]['amount'])->toBe(400.0);
});

// RED: Test calculateDistribution with fixed amount items
test('calculate distribution with fixed amount items', function () {
    $organization = \App\Models\Organization::factory()->create();

    // Create rule without items first
    $rule = FeeDistributionRule::factory()->fixedBased()->create(['organization_id' => $organization->id]);
    $account1 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $account2 = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    // Create items directly, overriding the factory's rule creation
    $item1 = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account1->id,
        'distribution_type' => 'fixed',
        'fixed_amount' => 300,
        'priority' => 1,
    ]);

    $item2 = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account2->id,
        'distribution_type' => 'fixed',
        'fixed_amount' => 200,
        'priority' => 2,
    ]);

    // Make sure no other items exist for this rule
    expect($rule->items()->count())->toBe(2);

    // Clear any cached items and reload
    $rule->refresh();

    // Debug: Check how many items we actually have
    $items = $rule->items()->get();
    expect($items)->toHaveCount(2);

    $distribution = $rule->calculateDistribution(1000);

    expect($distribution)->toHaveCount(2);
    expect($distribution[$item1->id]['amount'])->toBe(300.0);
    expect($distribution[$item2->id]['amount'])->toBe(200.0);
});

// RED: Test calculateDistribution throws exception for invalid percentage
test('calculate distribution throws exception for invalid percentage', function () {
    $rule = FeeDistributionRule::factory()->create();
    $account1 = ChartOfAccount::factory()->create();
    $account2 = ChartOfAccount::factory()->create();

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account1->id,
        'distribution_type' => 'percentage',
        'percentage' => 80,
    ]);

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account2->id,
        'distribution_type' => 'percentage',
        'percentage' => 30, // Total: 110%
    ]);

    expect(fn () => $rule->calculateDistribution(1000))->toThrow(\InvalidArgumentException::class, 'Total percentage distribution cannot exceed 100%');
});

// RED: Test getTotalDistributedAmount
test('get total distributed amount', function () {
    $rule = FeeDistributionRule::factory()->create(['rule_type' => 'fixed']);
    $account = ChartOfAccount::factory()->create();

    FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account->id,
        'distribution_type' => 'percentage',
        'percentage' => 75,
    ]);

    $total = $rule->getTotalDistributedAmount(1000);

    expect($total)->toBe(750.0);
});

// RED: Test soft deletes
test('fee distribution rule uses soft deletes', function () {
    $rule = FeeDistributionRule::factory()->create();
    $ruleId = $rule->id;

    $rule->delete();

    expect(FeeDistributionRule::find($ruleId))->toBeNull();
    expect(FeeDistributionRule::withTrashed()->find($ruleId))->not->toBeNull();
});

// RED: Test conditions casting
test('conditions are cast to array', function () {
    $conditions = ['min_amount' => 100, 'max_amount' => 500];
    $rule = FeeDistributionRule::factory()->create(['conditions' => $conditions]);

    expect($rule->conditions)->toBeArray();
    expect($rule->conditions)->toEqual($conditions);
});

// RED: Test is_active casting
test('is active is cast to boolean', function () {
    $rule = FeeDistributionRule::factory()->create(['is_active' => 1]);

    expect($rule->is_active)->toBeBool();
    expect($rule->is_active)->toBeTrue();
});

// RED: Test priority casting
test('priority is cast to integer', function () {
    $rule = FeeDistributionRule::factory()->create(['priority' => '5']);

    expect($rule->priority)->toBeInt();
    expect($rule->priority)->toBe(5);
});
