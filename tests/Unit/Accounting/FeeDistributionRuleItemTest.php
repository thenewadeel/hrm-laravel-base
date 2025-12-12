<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\FeeDistributionRuleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// RED: Test that FeeDistributionRuleItem can be created with required fields
test('fee distribution rule item can be created with required fields', function () {
    $rule = FeeDistributionRule::factory()->create();
    $account = ChartOfAccount::factory()->create();

    $item = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account->id,
        'distribution_type' => 'percentage',
        'percentage' => 50.00,
        'priority' => 1,
        'description' => 'Test distribution item',
    ]);

    expect($item)->toBeInstanceOf(FeeDistributionRuleItem::class);
    expect($item->fee_distribution_rule_id)->toBe($rule->id);
    expect($item->chart_of_account_id)->toBe($account->id);
    expect($item->distribution_type)->toBe('percentage');
    expect($item->percentage)->toBe('50.00');
    expect($item->priority)->toBe(1);
    expect($item->description)->toBe('Test distribution item');
});

// RED: Test that item belongs to rule
test('fee distribution rule item belongs to rule', function () {
    $rule = FeeDistributionRule::factory()->create();
    $item = FeeDistributionRuleItem::factory()->create(['fee_distribution_rule_id' => $rule->id]);

    expect($item->rule)->toBeInstanceOf(FeeDistributionRule::class);
    expect($item->rule->id)->toBe($rule->id);
});

// RED: Test that item belongs to chart of account
test('fee distribution rule item belongs to chart of account', function () {
    $organization = \App\Models\Organization::factory()->create();
    $account = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $item = FeeDistributionRuleItem::factory()->create(['chart_of_account_id' => $account->id]);

    expect($item->chartOfAccount)->toBeInstanceOf(ChartOfAccount::class);
    expect($item->chartOfAccount->id)->toBe($account->id);
});

// RED: Test calculateAmount for percentage type
test('calculate amount for percentage distribution type', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'percentage',
        'percentage' => 25.50,
    ]);

    $amount = $item->calculateAmount(1000);

    expect($amount)->toBe(255.0);
});

// RED: Test calculateAmount for fixed type
test('calculate amount for fixed distribution type', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'fixed',
        'fixed_amount' => 150.75,
    ]);

    $amount = $item->calculateAmount(1000);

    expect($amount)->toBe(150.75);
});

// RED: Test validate for valid percentage item
test('validate returns true for valid percentage item', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'percentage',
        'percentage' => 75.00,
    ]);

    expect($item->validate())->toBeTrue();
});

// RED: Test validate for invalid percentage item (zero)
test('validate returns false for zero percentage item', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'percentage',
        'percentage' => 0,
    ]);

    expect($item->validate())->toBeFalse();
});

// RED: Test validate for invalid percentage item (over 100)
test('validate returns false for percentage over 100', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'percentage',
        'percentage' => 150.00,
    ]);

    expect($item->validate())->toBeFalse();
});

// RED: Test validate for valid fixed amount item
test('validate returns true for valid fixed amount item', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'fixed',
        'fixed_amount' => 100.00,
    ]);

    expect($item->validate())->toBeTrue();
});

// RED: Test validate for invalid fixed amount item (zero)
test('validate returns false for zero fixed amount item', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'fixed',
        'fixed_amount' => 0,
    ]);

    expect($item->validate())->toBeFalse();
});

// RED: Test validate for negative fixed amount item
test('validate returns false for negative fixed amount item', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'fixed',
        'fixed_amount' => -50.00,
    ]);

    expect($item->validate())->toBeFalse();
});

// RED: Test percentage casting
test('percentage is cast to decimal with 2 places', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'percentage',
        'percentage' => '33.333333',
    ]);

    expect($item->percentage)->toBeString();
    expect($item->percentage)->toBe('33.33');
});

// RED: Test fixed_amount casting
test('fixed amount is cast to decimal with 2 places', function () {
    $item = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'fixed',
        'fixed_amount' => '123.456789',
    ]);

    expect($item->fixed_amount)->toBeString();
    expect($item->fixed_amount)->toBe('123.46');
});

// RED: Test priority casting
test('priority is cast to integer', function () {
    $item = FeeDistributionRuleItem::factory()->create(['priority' => '5']);

    expect($item->priority)->toBeInt();
    expect($item->priority)->toBe(5);
});

// RED: Test organization scoping through rule
test('item inherits organization from rule', function () {
    $organization = \App\Models\Organization::factory()->create();
    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);
    $item = FeeDistributionRuleItem::factory()->create(['fee_distribution_rule_id' => $rule->id]);

    expect($item->organization_id)->toBe($organization->id);
});

// RED: Test items are ordered by priority when retrieved through rule
test('items are ordered by priority when retrieved through rule', function () {
    $rule = FeeDistributionRule::factory()->create();

    $lowPriorityItem = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'priority' => 10,
    ]);

    $highPriorityItem = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'priority' => 1,
    ]);

    $items = $rule->items;

    expect($items->first()->id)->toBe($highPriorityItem->id);
    expect($items->last()->id)->toBe($lowPriorityItem->id);
});

// RED: Test calculateAmount with zero base amount
test('calculate amount with zero base amount', function () {
    $percentageItem = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'percentage',
        'percentage' => 50,
    ]);

    $fixedItem = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'fixed',
        'fixed_amount' => 100,
    ]);

    expect($percentageItem->calculateAmount(0))->toBe(0.0);
    expect($fixedItem->calculateAmount(0))->toBe(100.0);
});

// RED: Test calculateAmount with negative base amount
test('calculate amount with negative base amount', function () {
    $percentageItem = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'percentage',
        'percentage' => 50,
    ]);

    $fixedItem = FeeDistributionRuleItem::factory()->create([
        'distribution_type' => 'fixed',
        'fixed_amount' => 100,
    ]);

    expect($percentageItem->calculateAmount(-1000))->toBe(-500.0);
    expect($fixedItem->calculateAmount(-1000))->toBe(100.0);
});
