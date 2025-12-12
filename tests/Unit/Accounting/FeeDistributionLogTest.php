<?php

use App\Models\Accounting\FeeDistributionLog;
use App\Models\Accounting\FeeDistributionRule;
use App\Models\Accounting\JournalEntry;
use App\Models\Membership\MemberFee;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// RED: Test that FeeDistributionLog can be created with required fields
test('fee distribution log can be created with required fields', function () {
    $organization = \App\Models\Organization::factory()->create();
    $memberFee = MemberFee::factory()->create();
    $rule = FeeDistributionRule::factory()->create();

    $log = FeeDistributionLog::factory()->create([
        'organization_id' => $organization->id,
        'member_fee_id' => $memberFee->id,
        'fee_distribution_rule_id' => $rule->id,
        'journal_entry_id' => null,
        'total_amount' => 1000.00,
        'distribution_breakdown' => ['account_1' => ['amount' => 600], 'account_2' => ['amount' => 400]],
        'status' => 'success',
        'error_message' => null,
        'distributed_at' => now(),
    ]);

    expect($log)->toBeInstanceOf(FeeDistributionLog::class);
    expect($log->organization_id)->toBe($organization->id);
    expect($log->member_fee_id)->toBe($memberFee->id);
    expect($log->fee_distribution_rule_id)->toBe($rule->id);
    expect($log->total_amount)->toBe('1000.00');
    expect($log->status)->toBe('success');
    expect($log->distributed_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

// RED: Test that log belongs to member fee
test('fee distribution log belongs to member fee', function () {
    $memberFee = MemberFee::factory()->create();
    $log = FeeDistributionLog::factory()->create(['member_fee_id' => $memberFee->id]);

    expect($log->memberFee)->toBeInstanceOf(MemberFee::class);
    expect($log->memberFee->id)->toBe($memberFee->id);
});

// RED: Test that log belongs to rule
test('fee distribution log belongs to rule', function () {
    $rule = FeeDistributionRule::factory()->create();
    $log = FeeDistributionLog::factory()->create(['fee_distribution_rule_id' => $rule->id]);

    expect($log->rule)->toBeInstanceOf(FeeDistributionRule::class);
    expect($log->rule->id)->toBe($rule->id);
});

// RED: Test that log belongs to journal entry
test('fee distribution log belongs to journal entry', function () {
    $organization = \App\Models\Organization::factory()->create();
    $user = \App\Models\User::factory()->create(['current_organization_id' => $organization->id]);

    $journalEntry = JournalEntry::factory()->create([
        'created_by' => $user->id,
        'organization_id' => $organization->id,
    ]);
    $log = FeeDistributionLog::factory()->create([
        'journal_entry_id' => $journalEntry->id,
        'organization_id' => $organization->id,
    ]);

    expect($log->journalEntry)->toBeInstanceOf(JournalEntry::class);
    expect($log->journalEntry->id)->toBe($journalEntry->id);
});

// RED: Test successful scope
test('successful scope returns only successful logs', function () {
    $successLog = FeeDistributionLog::factory()->create(['status' => 'success']);
    $failedLog = FeeDistributionLog::factory()->create(['status' => 'failed']);

    $successfulLogs = FeeDistributionLog::successful()->get();

    expect($successfulLogs)->toHaveCount(1);
    expect($successfulLogs->first()->id)->toBe($successLog->id);
});

// RED: Test failed scope
test('failed scope returns only failed logs', function () {
    $successLog = FeeDistributionLog::factory()->create(['status' => 'success']);
    $failedLog = FeeDistributionLog::factory()->create(['status' => 'failed']);

    $failedLogs = FeeDistributionLog::failed()->get();

    expect($failedLogs)->toHaveCount(1);
    expect($failedLogs->first()->id)->toBe($failedLog->id);
});

// RED: Test betweenDates scope
test('between dates scope filters logs by date range', function () {
    $log1 = FeeDistributionLog::factory()->create(['distributed_at' => now()->subDays(10)]);
    $log2 = FeeDistributionLog::factory()->create(['distributed_at' => now()]);
    $log3 = FeeDistributionLog::factory()->create(['distributed_at' => now()->addDays(10)]);

    $logs = FeeDistributionLog::betweenDates(
        now()->subDays(5),
        now()->addDays(5)
    )->get();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->id)->toBe($log2->id);
});

// RED: Test getActualDistributedAmount attribute
test('get actual distributed amount attribute', function () {
    $breakdown = [
        'account_1' => ['amount' => 600.00],
        'account_2' => ['amount' => 400.00],
    ];

    $log = FeeDistributionLog::factory()->create([
        'distribution_breakdown' => $breakdown,
    ]);

    expect($log->actual_distributed_amount)->toBe(1000.00);
});

// RED: Test getActualDistributedAmount with null breakdown
test('get actual distributed amount with null breakdown', function () {
    $log = FeeDistributionLog::factory()->create(['distribution_breakdown' => []]);

    expect($log->actual_distributed_amount)->toBe(0.0);
});

// RED: Test getIsFullyDistributed attribute for successful distribution
test('get is fully distributed attribute for successful distribution', function () {
    $log = FeeDistributionLog::factory()->create([
        'total_amount' => 1000.00,
        'distribution_breakdown' => [
            'account_1' => ['amount' => 600.00],
            'account_2' => ['amount' => 400.00],
        ],
        'status' => 'success',
    ]);

    expect($log->is_fully_distributed)->toBeTrue();
});

// RED: Test getIsFullyDistributed attribute for partial distribution
test('get is fully distributed attribute for partial distribution', function () {
    $log = FeeDistributionLog::factory()->create([
        'total_amount' => 1000.00,
        'distribution_breakdown' => [
            'account_1' => ['amount' => 600.00],
        ],
        'status' => 'success',
    ]);

    expect($log->is_fully_distributed)->toBeFalse();
});

// RED: Test getIsFullyDistributed attribute for failed distribution
test('get is fully distributed attribute for failed distribution', function () {
    $log = FeeDistributionLog::factory()->create([
        'total_amount' => 1000.00,
        'distribution_breakdown' => [],
        'status' => 'failed',
    ]);

    expect($log->is_fully_distributed)->toBeFalse();
});

// RED: Test getIsFullyDistributed attribute with small difference
test('get is fully distributed attribute with small difference', function () {
    $log = FeeDistributionLog::factory()->create([
        'total_amount' => 1000.00,
        'distribution_breakdown' => [
            'account_1' => ['amount' => 600.00],
            'account_2' => ['amount' => 399.99], // 0.01 difference
        ],
        'status' => 'success',
    ]);

    expect($log->is_fully_distributed)->toBeTrue(); // Within tolerance
});

// RED: Test total_amount casting
test('total amount is cast to decimal with 2 places', function () {
    $log = FeeDistributionLog::factory()->create(['total_amount' => '1234.5678']);

    expect($log->total_amount)->toBeString();
    expect($log->total_amount)->toBe('1234.57');
});

// RED: Test distribution_breakdown casting
test('distribution breakdown is cast to array', function () {
    $breakdown = ['account_1' => ['amount' => 600]];
    $log = FeeDistributionLog::factory()->create(['distribution_breakdown' => $breakdown]);

    expect($log->distribution_breakdown)->toBeArray();
    expect($log->distribution_breakdown)->toEqual($breakdown);
});

// RED: Test distributed_at casting
test('distributed at is cast to datetime', function () {
    $log = FeeDistributionLog::factory()->create(['distributed_at' => '2025-01-15 10:30:00']);

    expect($log->distributed_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($log->distributed_at->format('Y-m-d H:i:s'))->toBe('2025-01-15 10:30:00');
});

// RED: Test organization scoping
test('fee distribution log belongs to organization', function () {
    $organization = \App\Models\Organization::factory()->create();
    $log = FeeDistributionLog::factory()->create(['organization_id' => $organization->id]);

    expect($log->organization_id)->toBe($organization->id);
});

// RED: Test null relationships
test('log handles null relationships gracefully', function () {
    $log = FeeDistributionLog::factory()->create([
        'fee_distribution_rule_id' => null,
        'journal_entry_id' => null,
    ]);

    expect($log->rule)->toBeNull();
    expect($log->journalEntry)->toBeNull();
});

// RED: Test complex distribution breakdown
test('complex distribution breakdown calculation', function () {
    $breakdown = [
        'account_1' => [
            'account_id' => 1,
            'type' => 'percentage',
            'value' => 60,
            'amount' => 600.00,
        ],
        'account_2' => [
            'account_id' => 2,
            'type' => 'fixed',
            'value' => 200,
            'amount' => 400.00,
        ],
    ];

    $log = FeeDistributionLog::factory()->successful()->create([
        'distribution_breakdown' => $breakdown,
        'total_amount' => '1000.00',
    ]);

    expect($log->actual_distributed_amount)->toBe(1000.0);
    expect($log->is_fully_distributed)->toBeTrue();
});
