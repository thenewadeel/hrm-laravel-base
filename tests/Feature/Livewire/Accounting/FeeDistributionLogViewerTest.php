<?php

use App\Models\Accounting\FeeDistributionLog;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\JournalEntry;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// RED: Test FeeDistributionLogViewer component renders
test('fee distribution log viewer component renders', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->assertStatus(200);
});

// RED: Test component loads logs for organization
test('fee distribution log viewer loads logs for organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee = MemberFee::factory()->create(['organization_id' => $organization->id]);
    $log = FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'total_amount' => 1000,
    ]);

    // Create log for different organization
    $otherOrg = Organization::factory()->create();
    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $otherOrg->id,
        'total_amount' => 500,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->assertSee(1000)
        ->assertDontSee(500);
});

// RED: Test search functionality
test('fee distribution log viewer searches by description', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee1 = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'description' => 'Monthly Subscription Fee',
    ]);

    $memberFee2 = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'description' => 'Late Payment Penalty',
    ]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee1->id,
    ]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee2->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->set('search', 'Subscription')
        ->assertSee('Monthly Subscription Fee')
        ->assertDontSee('Late Payment Penalty');
});

// RED: Test search by member name
test('fee distribution log viewer searches by member name', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $member = \App\Models\Membership\Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
    ]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->set('search', 'John')
        ->assertSee('John Doe');
});

// RED: Test status filter
test('fee distribution log viewer filters by status', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee = MemberFee::factory()->create(['organization_id' => $organization->id]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
    ]);

    FeeDistributionLog::factory()->failed()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->set('statusFilter', 'success')
        ->assertSee('success')
        ->assertDontSee('failed');
});

// RED: Test fee type filter
test('fee distribution log viewer filters by fee type', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $subscriptionFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'subscription',
    ]);

    $lateFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'fee_type' => 'late_fee',
    ]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $subscriptionFee->id,
    ]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $lateFee->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->set('feeTypeFilter', 'subscription')
        ->assertSee('subscription')
        ->assertDontSee('late_fee');
});

// RED: Test date range filter
test('fee distribution log viewer filters by date range', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee = MemberFee::factory()->create(['organization_id' => $organization->id]);

    $oldLog = FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'distributed_at' => now()->subDays(10),
    ]);

    $recentLog = FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'distributed_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->set('dateFrom', now()->subDays(5)->format('Y-m-d'))
        ->set('dateTo', now()->addDays(5)->format('Y-m-d'))
        ->assertSee($recentLog->total_amount)
        ->assertDontSee($oldLog->total_amount);
});

// RED: Test showing log details
test('fee distribution log viewer shows log details', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee = MemberFee::factory()->create(['organization_id' => $organization->id]);
    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);
    $journalEntry = JournalEntry::factory()->create(['organization_id' => $organization->id]);

    $log = FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'fee_distribution_rule_id' => $rule->id,
        'journal_entry_id' => $journalEntry->id,
        'distribution_breakdown' => [
            'account_1' => ['amount' => 600, 'type' => 'percentage'],
            'account_2' => ['amount' => 400, 'type' => 'fixed'],
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->call('showDetails', $log)
        ->assertSet('selectedLog.id', $log->id)
        ->assertSet('showDetailsModal', true)
        ->assertSee(600)
        ->assertSee(400);
});

// RED: Test summary calculation
test('fee distribution log viewer calculates summary correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee = MemberFee::factory()->create(['organization_id' => $organization->id]);

    // Create successful logs
    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'total_amount' => 1000,
        'distributed_at' => now(),
    ]);

    FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'total_amount' => 500,
        'distributed_at' => now(),
    ]);

    // Create failed logs
    FeeDistributionLog::factory()->failed()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'total_amount' => 200,
        'distributed_at' => now(),
    ]);

    // Create partial logs
    FeeDistributionLog::factory()->partial()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'total_amount' => 300,
        'distributed_at' => now(),
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    $summary = $component->summary;

    expect($summary['total_amount'])->toBe(2000.0); // 1000 + 500 (successful only)
    expect($summary['success_count'])->toBe(2);
    expect($summary['failed_count'])->toBe(1);
    expect($summary['partial_count'])->toBe(1);
    expect($summary['total_count'])->toBe(4);
});

// RED: Test fee types property
test('fee distribution log viewer provides fee types', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    $feeTypes = $component->feeTypes;

    expect($feeTypes)->toBeArray();
    expect($feeTypes)->toHaveKey('subscription');
    expect($feeTypes)->toHaveKey('late_fee');
    expect($feeTypes['subscription'])->toBe('Subscription Fee');
});

// RED: Test status options property
test('fee distribution log viewer provides status options', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    $statusOptions = $component->statusOptions;

    expect($statusOptions)->toBeArray();
    expect($statusOptions)->toHaveKey('success');
    expect($statusOptions)->toHaveKey('failed');
    expect($statusOptions)->toHaveKey('partial');
    expect($statusOptions['success'])->toBe('Success');
});

// RED: Test reset filters
test('fee distribution log viewer resets filters', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->set('search', 'test')
        ->set('statusFilter', 'success')
        ->set('feeTypeFilter', 'subscription')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('statusFilter', '')
        ->assertSet('feeTypeFilter', '');
});

// RED: Test default date range
test('fee distribution log viewer sets default date range', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    expect($component->dateFrom)->toBe(now()->startOfMonth()->format('Y-m-d'));
    expect($component->dateTo)->toBe(now()->endOfMonth()->format('Y-m-d'));
});

// RED: Test query string parameters
test('fee distribution log viewer maintains query string parameters', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class, [
            'search' => 'test',
            'statusFilter' => 'success',
            'feeTypeFilter' => 'subscription',
        ])
        ->assertSet('search', 'test')
        ->assertSet('statusFilter', 'success')
        ->assertSet('feeTypeFilter', 'subscription');
});

// RED: Test pagination
test('fee distribution log viewer paginates results', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee = MemberFee::factory()->create(['organization_id' => $organization->id]);

    // Create more than 15 logs to test pagination
    FeeDistributionLog::factory()->count(20)->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->assertViewHas('logs', function ($logs) {
            return $logs->count() <= 15; // Default pagination
        });
});

// RED: Test organization isolation
test('fee distribution log viewer respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization1->id]);

    // Create log for user's organization
    $userLog = FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization1->id,
        'total_amount' => 1000,
    ]);

    // Create log for different organization
    $otherLog = FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization2->id,
        'total_amount' => 500,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->assertSee(1000)
        ->assertDontSee(500);
});

// RED: Test authorization
test('fee distribution log viewer requires authentication', function () {
    Livewire::test(\App\Livewire\Accounting\FeeDistributionLogViewer::class)
        ->assertStatus(401);
});

// RED: Test logs are loaded with relationships
test('fee distribution log viewer loads logs with relationships', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->update(['current_organization_id' => $organization->id]);

    $memberFee = MemberFee::factory()->create(['organization_id' => $organization->id]);
    $rule = FeeDistributionRule::factory()->create(['organization_id' => $organization->id]);
    $journalEntry = JournalEntry::factory()->create(['organization_id' => $organization->id]);

    $log = FeeDistributionLog::factory()->successful()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'fee_distribution_rule_id' => $rule->id,
        'journal_entry_id' => $journalEntry->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Accounting\FeeDistributionLogViewer::class);

    $logs = $component->logs;
    $loadedLog = $logs->first();

    expect($loadedLog->relationLoaded('rule'))->toBeTrue();
    expect($loadedLog->relationLoaded('memberFee'))->toBeTrue();
    expect($loadedLog->relationLoaded('journalEntry'))->toBeTrue();
});
