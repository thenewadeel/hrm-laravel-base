<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\FeeDistributionRuleItem;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// RED: Test FeeDistributionRuleManager component renders
test('fee distribution rule manager component renders', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->assertStatus(200);
});

// RED: Test component loads rules
test('fee distribution rule manager loads rules for organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'Test Rule',
    ]);

    // Create rule for different organization
    FeeDistributionRule::factory()->create([
        'organization_id' => Organization::factory()->create(),
        'name' => 'Other Rule',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->assertSee('Test Rule')
        ->assertDontSee('Other Rule');
});

// RED: Test component loads chart of accounts
test('fee distribution rule manager loads chart of accounts', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $account = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'code' => '4000',
        'name' => 'Test Account',
        'type' => 'revenue',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->assertSee('4000 - Test Account (revenue)');
});

// RED: Test creating a new rule
test('fee distribution rule manager can create new rule', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->set('name', 'New Test Rule')
        ->set('fee_type', 'subscription')
        ->set('rule_type', 'percentage')
        ->set('priority', 1)
        ->set('description', 'Test description')
        ->call('createRule')
        ->assertDispatched('rule-created', 'Rule \'New Test Rule\' created successfully.');

    $this->assertDatabaseHas('fee_distribution_rules', [
        'organization_id' => $organization->id,
        'name' => 'New Test Rule',
        'fee_type' => 'subscription',
        'rule_type' => 'percentage',
        'priority' => 1,
        'description' => 'Test description',
    ]);
});

// RED: Test rule validation
test('fee distribution rule manager validates rule creation', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->set('name', '') // Required field missing
        ->set('fee_type', 'subscription')
        ->call('createRule')
        ->assertHasErrors(['name' => 'required']);
});

// RED: Test editing an existing rule
test('fee distribution rule manager can edit rule', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'Original Rule',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('editRule', $rule)
        ->assertSet('editingRule.id', $rule->id)
        ->assertSet('name', 'Original Rule')
        ->set('name', 'Updated Rule')
        ->call('updateRule')
        ->assertDispatched('rule-updated', 'Rule \'Updated Rule\' updated successfully.');

    $this->assertDatabaseHas('fee_distribution_rules', [
        'id' => $rule->id,
        'name' => 'Updated Rule',
    ]);
});

// RED: Test deleting a rule
test('fee distribution rule manager can delete rule', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'Rule to Delete',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('deleteRule', $rule)
        ->assertDispatched('rule-deleted', 'Rule \'Rule to Delete\' deleted successfully.');

    $this->assertSoftDeleted('fee_distribution_rules', [
        'id' => $rule->id,
    ]);
});

// RED: Test managing rule items
test('fee distribution rule manager can manage rule items', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $account = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('manageItems', $rule)
        ->assertSet('selectedRule.id', $rule->id)
        ->assertSet('showItemsModal', true)
        ->set('item_chart_of_account_id', $account->id)
        ->set('item_distribution_type', 'percentage')
        ->set('item_percentage', 50)
        ->set('item_priority', 1)
        ->call('addItem')
        ->assertDispatched('item-added', 'Distribution item added successfully.');

    $this->assertDatabaseHas('fee_distribution_rule_items', [
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account->id,
        'distribution_type' => 'percentage',
        'percentage' => 50,
        'priority' => 1,
    ]);
});

// RED: Test item validation
test('fee distribution rule manager validates item creation', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('manageItems', $rule)
        ->set('item_distribution_type', 'percentage')
        ->set('item_percentage', '') // Required when type is percentage
        ->call('addItem')
        ->assertHasErrors(['item_percentage' => 'required_if']);
});

// RED: Test deleting rule items
test('fee distribution rule manager can delete rule items', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);
    $item = FeeDistributionRuleItem::factory()->create(['fee_distribution_rule_id' => $rule->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('manageItems', $rule)
        ->call('deleteItem', $item->id)
        ->assertDispatched('item-deleted', 'Distribution item deleted successfully.');

    $this->assertDatabaseMissing('fee_distribution_rule_items', [
        'id' => $item->id,
    ]);
});

// RED: Test fee types property
test('fee distribution rule manager provides fee types', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class);

    $feeTypes = $component->feeTypes;

    expect($feeTypes)->toBeArray();
    expect($feeTypes)->toHaveKey('subscription');
    expect($feeTypes)->toHaveKey('late_fee');
    expect($feeTypes)->toHaveKey('penalty');
    expect($feeTypes['subscription'])->toBe('Subscription Fee');
});

// RED: Test rule types property
test('fee distribution rule manager provides rule types', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class);

    $ruleTypes = $component->ruleTypes;

    expect($ruleTypes)->toBeArray();
    expect($ruleTypes)->toHaveKey('percentage');
    expect($ruleTypes)->toHaveKey('fixed');
    expect($ruleTypes)->toHaveKey('priority');
    expect($ruleTypes['percentage'])->toBe('Percentage-based');
});

// RED: Test form reset
test('fee distribution rule manager resets form after creation', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->set('name', 'Test Rule')
        ->set('fee_type', 'subscription')
        ->set('description', 'Test Description')
        ->call('createRule')
        ->assertSet('name', '')
        ->assertSet('fee_type', '')
        ->assertSet('description', '')
        ->assertSet('showCreateModal', false);
});

// RED: Test item form reset
test('fee distribution rule manager resets item form after adding item', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);
    $account = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('manageItems', $rule)
        ->set('item_chart_of_account_id', $account->id)
        ->set('item_percentage', 50)
        ->set('item_description', 'Test Item')
        ->call('addItem')
        ->assertSet('item_chart_of_account_id', '')
        ->assertSet('item_percentage', 0)
        ->assertSet('item_description', '');
});

// RED: Test organization isolation
test('fee distribution rule manager respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization1->id]);

    // Create rule for user's organization
    $userRule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization1->id,
        'name' => 'User Rule',
    ]);

    // Create rule for different organization
    $otherRule = FeeDistributionRule::factory()->create([
        'organization_id' => $organization2->id,
        'name' => 'Other Rule',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->assertSee('User Rule')
        ->assertDontSee('Other Rule');
});

// RED: Test authorization
test('fee distribution rule manager requires authentication', function () {
    Livewire::test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->assertStatus(401);
});

// RED: Test rule items are loaded with chart of accounts
test('fee distribution rule manager loads rule items with chart of accounts', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);
    $account = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $item = FeeDistributionRuleItem::factory()->create([
        'fee_distribution_rule_id' => $rule->id,
        'chart_of_account_id' => $account->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('manageItems', $rule)
        ->assertSee($account->name);
});

// RED: Test refresh rule items
test('fee distribution rule manager refreshes rule items', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionRuleManager::class)
        ->call('manageItems', $rule)
        ->assertSet('ruleItems', [])
        ->call('refreshRuleItems')
        ->assertSet('ruleItems', []);
});
