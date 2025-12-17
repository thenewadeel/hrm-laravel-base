<?php

use App\Events\Membership\FeeCreated;
use App\Events\Membership\FeePaymentProcessed;
use App\Events\Membership\FeeWaived;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use App\Services\Membership\FeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create();
    $this->member = Member::factory()->create([
        'organization_id' => $this->organization->id,
    ]);
    $this->service = app(FeeService::class);
});

test('creates fee with proper business rules validation', function () {
    Event::fake();

    $feeData = [
        'fee_type' => 'subscription',
        'description' => 'Annual membership fee',
        'amount' => 100.00,
        'due_date' => now()->addDays(30),
        'distribute_to_accounts' => false, // Disable for test simplicity
    ];

    $fee = $this->service->createFee($this->member, $feeData);

    expect($fee)->toBeInstanceOf(MemberFee::class)
        ->and($fee->member_id)->toBe($this->member->id)
        ->and($fee->organization_id)->toBe($this->organization->id)
        ->and($fee->fee_type)->toBe('subscription')
        ->and((float) $fee->amount)->toBe(100.0)
        ->and($fee->status)->toBe('pending');

    Event::assertDispatched(FeeCreated::class, function ($event) use ($fee) {
        return $event->fee->id === $fee->id;
    });
});

test('validates fee amount business rules', function () {
    expect(fn () => $this->service->createFee($this->member, [
        'fee_type' => 'subscription',
        'amount' => -50, // Negative amount
        'due_date' => now()->addDays(30),
        'distribute_to_accounts' => false,
    ]))->toThrow(\Exception::class);

    expect(fn () => $this->service->createFee($this->member, [
        'fee_type' => 'subscription',
        'amount' => 0, // Zero amount
        'due_date' => now()->addDays(30),
        'distribute_to_accounts' => false,
    ]))->toThrow(\Exception::class);

    expect(fn () => $this->service->createFee($this->member, [
        'fee_type' => 'subscription',
        'amount' => 1000000, // Excessive amount
        'due_date' => now()->addDays(30),
        'distribute_to_accounts' => false,
    ]))->toThrow(\Exception::class);
});

test('validates fee type business rules', function () {
    expect(fn () => $this->service->createFee($this->member, [
        'fee_type' => 'invalid_type',
        'amount' => 100,
        'due_date' => now()->addDays(30),
        'distribute_to_accounts' => false,
    ]))->toThrow(\Exception::class);
});

test('validates due date business rules', function () {
    expect(fn () => $this->service->createFee($this->member, [
        'fee_type' => 'subscription',
        'amount' => 100,
        'due_date' => now()->subDays(1), // Past date
        'distribute_to_accounts' => false,
    ]))->toThrow('InvalidArgumentException');

    expect(fn () => $this->service->createFee($this->member, [
        'fee_type' => 'subscription',
        'amount' => 100,
        'due_date' => now()->addDays(800), // More than 2 years (730 days)
        'distribute_to_accounts' => false,
    ]))->toThrow('InvalidArgumentException');
});

test('processes fee payment with proper accounting integration', function () {
    Event::fake();
    $this->actingAs($this->user);

    // Create required accounts manually
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '1000',
        'type' => 'asset',
        'description' => 'Cash and bank accounts',
    ]);

    $receivableAccount = ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '1200',
        'type' => 'asset',
        'description' => 'Accounts receivable',
    ]);

    $fee = MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100.00,
        'status' => 'pending',
    ]);

    $paymentData = [
        'amount' => 100.00,
        'payment_method' => 'cash',
        'payment_reference' => 'PAY-001',
    ];

    $result = $this->service->processFeePayment($fee, $paymentData);

    expect($result)->toBeTrue()
        ->and($fee->fresh()->status)->toBe('paid')
        ->and((float) $fee->fresh()->paid_amount)->toBe(100.0)
        ->and($fee->fresh()->payment_method)->toBe('cash');

    // Check accounting entries
    expect(JournalEntry::count())->toBe(1);
    expect(LedgerEntry::count())->toBe(2);

    Event::assertDispatched(FeePaymentProcessed::class);
});

test('validates payment amount business rules', function () {
    expect(fn () => $this->service->processFeePayment($fee, [
        'amount' => 0, // Zero payment
        'payment_method' => 'cash',
    ]))->toThrow(\Exception::class);

    expect(fn () => $this->service->processFeePayment($fee, [
        'amount' => -50, // Negative payment
        'payment_method' => 'cash',
    ]))->toThrow(\Exception::class);

    expect(fn () => $this->service->processFeePayment($fee, [
        'amount' => 150, // Overpayment
        'payment_method' => 'cash',
    ]))->toThrow(\Exception::class);
});

test('waives fee with proper authorization and accounting', function () {
    Event::fake();
    $this->actingAs($this->user);

    // Create required accounts manually
    $receivableAccount = ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '1200',
        'type' => 'asset',
        'description' => 'Accounts receivable',
    ]);

    $waiverExpenseAccount = ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '5000',
        'type' => 'expense',
        'description' => 'Operating expenses',
    ]);

    $fee = MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100.00,
        'status' => 'pending',
    ]);

    $result = $this->service->waiveFee($fee, 'Financial hardship');

    expect($result)->toBeTrue()
        ->and($fee->fresh()->status)->toBe('waived')
        ->and($fee->fresh()->notes)->toContain('Waived: Financial hardship');

    // Check accounting entries
    expect(JournalEntry::count())->toBe(1);
    expect(LedgerEntry::count())->toBe(2);

    Event::assertDispatched(FeeWaived::class);
});

test('prevents waiver of paid fees', function () {
    $fee = MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100.00,
        'status' => 'paid',
        'paid_amount' => 100.00,
    ]);

    expect(fn () => $this->service->waiveFee($fee, 'Test waiver'))
        ->toThrow(\Exception::class);
});

test('generates overdue fees with proper business rules', function () {
    // Clean up any existing late fees for this organization to ensure test isolation
    MemberFee::where('organization_id', $this->organization->id)
        ->where('fee_type', 'late_fee')
        ->delete();

    // Create a new member to avoid conflicts
    $newMember = Member::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    // Create overdue fee for new member - use a specific past date
    $overdueFee = MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $newMember->id,
        'amount' => 100.00,
        'due_date' => now()->subDays(10)->format('Y-m-d'),
        'status' => 'pending',
    ]);

    $count = $this->service->generateOverdueFees($this->organization->id);

    expect($count)->toBe(1);

    // Check late fee was created
    $lateFee = MemberFee::where('fee_type', 'late_fee')
        ->where('member_id', $newMember->id)
        ->first();

    expect($lateFee)->toBeTruthy()
        ->and((float) $lateFee->amount)->toBeGreaterThan(0)
        ->and($lateFee->description)->toContain('Late fee for fee #'.$overdueFee->id);

    // Check original fee is marked as overdue
    expect($overdueFee->fresh()->status)->toBe('overdue');
});

test('prevents duplicate late fees', function () {
    $overdueFee = MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100.00,
        'due_date' => now()->subDays(10),
        'status' => 'pending',
    ]);

    // Generate overdue fees twice
    $this->service->generateOverdueFees($this->organization->id);
    $count = $this->service->generateOverdueFees($this->organization->id);

    expect($count)->toBe(0); // No new late fees should be created
});

test('calculates late fee amount correctly', function () {
    // Clean up any existing late fees for this organization to ensure test isolation
    MemberFee::where('organization_id', $this->organization->id)
        ->where('fee_type', 'late_fee')
        ->delete();

    // Create overdue fee
    $overdueFee = MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100.00,
        'due_date' => now()->subDays(10),
        'status' => 'pending',
    ]);

    $this->service->generateOverdueFees($this->organization->id);

    $lateFee = MemberFee::where('fee_type', 'late_fee')
        ->where('member_id', $this->member->id)
        ->first();

    // Expected: 5% of 100 + 10 days * $1 = $5 + $10 = $15
    expect((float) $lateFee->amount)->toBe(15.0);
});

test('provides comprehensive fee statistics', function () {
    // Create various fees
    MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100,
        'status' => 'paid',
        'paid_amount' => 100,
    ]);

    MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 50,
        'status' => 'pending',
    ]);

    MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 25,
        'status' => 'waived',
    ]);

    $stats = $this->service->getFeeStatistics($this->organization->id);

    expect($stats['total_fees'])->toBe(3)
        ->and($stats['paid_fees'])->toBe(1)
        ->and($stats['pending_fees'])->toBe(1)
        ->and($stats['waived_fees'])->toBe(1)
        ->and($stats['total_amount'])->toBe(175)
        ->and($stats['paid_amount'])->toBe(100)
        ->and($stats['collection_rate'])->toBe(57.14); // 100/175 * 100
});

test('handles transaction rollback on errors', function () {
    // This test ensures that if any part of the fee creation fails,
    // the entire transaction is rolled back

    $initialFeeCount = MemberFee::count();

    expect(fn () => $this->service->createFee($this->member, [
        'fee_type' => 'membership',
        'amount' => 100.00,
        'due_date' => now()->addDays(30),
        'distribute_to_accounts' => true, // This will fail due to missing accounts
    ]))->toThrow(Exception::class);

    expect(MemberFee::count())->toBe($initialFeeCount);
});

test('validates member status before fee creation', function () {
    $inactiveMember = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => 'inactive',
    ]);

    expect(fn () => $this->service->createFee($inactiveMember, [
        'fee_type' => 'subscription',
        'amount' => 100.00,
        'due_date' => now()->addDays(30),
        'distribute_to_accounts' => false,
    ]))->toThrow(\Exception::class);
});

test('sends payment reminders successfully', function () {
    $fee = MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100.00,
        'status' => 'pending',
        'due_date' => now()->addDays(5),
    ]);

    $result = $this->service->sendPaymentReminder($fee, 'Your payment is due soon', 'email');

    expect($result)->toBeTrue();
});

test('exports fees data to csv format', function () {
    MemberFee::factory()->create([
        'organization_id' => $this->organization->id,
        'member_id' => $this->member->id,
        'amount' => 100.00,
        'status' => 'paid',
        'paid_amount' => 100.00,
    ]);

    $csv = $this->service->exportToCsv($this->organization->id);

    expect($csv)->toContain('ID,Member Name,Membership Number')
        ->and($csv)->toContain($this->member->full_name)
        ->and($csv)->toContain('100.00');
});
