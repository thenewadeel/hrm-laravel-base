<?php

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MemberFee Model', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
        $this->member = Member::factory()->create(['organization_id' => $this->organization->id]);
    });

    test('can create a member fee with required fields', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'fee_type' => 'subscription',
            'amount' => 50.00,
        ]);

        expect($fee)->toBeInstanceOf(MemberFee::class);
        expect($fee->member_id)->toBe($this->member->id);
        expect($fee->fee_type)->toBe('subscription');
        expect($fee->amount)->toBe('50.00');
        expect($fee->organization_id)->toBe($this->organization->id);
    });

    test('casts dates correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'due_date' => '2024-12-31',
            'paid_date' => '2024-12-15',
        ]);

        expect($fee->due_date)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($fee->paid_date)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($fee->due_date->format('Y-m-d'))->toBe('2024-12-31');
        expect($fee->paid_date->format('Y-m-d'))->toBe('2024-12-15');
    });

    test('casts decimal fields correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'amount' => 50.99,
            'paid_amount' => 25.50,
        ]);

        expect($fee->amount)->toBeString();
        expect($fee->amount)->toBe('50.99');
        expect($fee->paid_amount)->toBeString();
        expect($fee->paid_amount)->toBe('25.50');
    });

    test('formatted_amount accessor returns correctly formatted string', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'amount' => 50.99,
        ]);

        expect($fee->formatted_amount)->toBe('50.99');
    });

    test('is_paid accessor works correctly', function () {
        $paidFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
        ]);

        $pendingFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        expect($paidFee->is_paid)->toBeTrue();
        expect($pendingFee->is_paid)->toBeFalse();
    });

    test('is_overdue accessor works correctly', function () {
        $overdueFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->subDays(10),
        ]);

        $pendingFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->addDays(10),
        ]);

        $paidFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
            'due_date' => now()->subDays(10),
        ]);

        expect($overdueFee->is_overdue)->toBeTrue();
        expect($pendingFee->is_overdue)->toBeFalse();
        expect($paidFee->is_overdue)->toBeFalse();
    });

    test('days_overdue accessor works correctly', function () {
        $overdueFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);

        $pendingFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        expect(abs($overdueFee->days_overdue))->toBe(5);
        expect($pendingFee->days_overdue)->toBe(0);
    });

    test('mark_as_paid works correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'amount' => 50.00,
            'paid_amount' => 0,
        ]);

        $paymentData = [
            'payment_method' => 'credit_card',
            'payment_reference' => 'PAY-12345',
        ];

        $result = $fee->markAsPaid($paymentData);

        expect($result)->toBeTrue();
        expect($fee->status)->toBe('paid');
        expect($fee->paid_date)->not->toBeNull();
        expect($fee->payment_method)->toBe('credit_card');
        expect($fee->payment_reference)->toBe('PAY-12345');
    });

    test('mark_as_paid works with minimal data', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $result = $fee->markAsPaid([]);

        expect($result)->toBeTrue();
        expect($fee->status)->toBe('paid');
        expect($fee->paid_date)->not->toBeNull();
        expect($fee->payment_method)->toBeNull();
        expect($fee->payment_reference)->toBeNull();
    });

    test('mark_as_waived works correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $result = $fee->markAsWaived();

        expect($result)->toBeTrue();
        expect($fee->status)->toBe('waived');
    });

    test('mark_as_overdue works correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);

        $result = $fee->markAsOverdue();

        expect($result)->toBeTrue();
        expect($fee->status)->toBe('overdue');
    });

    test('mark_as_overdue fails for future due date', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        $result = $fee->markAsOverdue();

        expect($result)->toBeFalse();
        expect($fee->status)->toBe('pending');
    });

    test('mark_as_overdue fails for non-pending status', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
            'due_date' => now()->subDays(5),
        ]);

        $result = $fee->markAsOverdue();

        expect($result)->toBeFalse();
        expect($fee->status)->toBe('paid');
    });

    test('pending scope works correctly', function () {
        $pendingFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $paidFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
        ]);

        $pendingFees = MemberFee::pending()->get();

        expect($pendingFees)->toHaveCount(1);
        expect($pendingFees->first()->id)->toBe($pendingFee->id);
    });

    test('paid scope works correctly', function () {
        $paidFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
        ]);

        $pendingFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $paidFees = MemberFee::paid()->get();

        expect($paidFees)->toHaveCount(1);
        expect($paidFees->first()->id)->toBe($paidFee->id);
    });

    test('overdue scope works correctly', function () {
        $overdueFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'overdue',
        ]);

        $pendingOverdueFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);

        $pendingFee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        $overdueFees = MemberFee::overdue()->get();

        expect($overdueFees)->toHaveCount(2);
        expect($overdueFees->pluck('id'))->toContain($overdueFee->id);
        expect($overdueFees->pluck('id'))->toContain($pendingOverdueFee->id);
        expect($overdueFees->pluck('id'))->not->toContain($pendingFee->id);
    });

    test('by_type scope works correctly', function () {
        $subscriptionFee = MemberFee::factory()->subscription()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
        ]);

        $lateFee = MemberFee::factory()->lateFee()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
        ]);

        $subscriptionFees = MemberFee::byType('subscription')->get();
        $lateFees = MemberFee::byType('late_fee')->get();

        expect($subscriptionFees)->toHaveCount(1);
        expect($lateFees)->toHaveCount(1);
        expect($subscriptionFees->first()->id)->toBe($subscriptionFee->id);
        expect($lateFees->first()->id)->toBe($lateFee->id);
    });

    test('by_member scope works correctly', function () {
        $member1 = Member::factory()->create(['organization_id' => $this->organization->id]);
        $member2 = Member::factory()->create(['organization_id' => $this->organization->id]);

        $fee1 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member1->id,
        ]);

        $fee2 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member2->id,
        ]);

        $member1Fees = MemberFee::byMember($member1->id)->get();
        $member2Fees = MemberFee::byMember($member2->id)->get();

        expect($member1Fees)->toHaveCount(1);
        expect($member2Fees)->toHaveCount(1);
        expect($member1Fees->first()->id)->toBe($fee1->id);
        expect($member2Fees->first()->id)->toBe($fee2->id);
    });

    test('due_between scope works correctly', function () {
        $fee1 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'due_date' => now()->addDays(10),
        ]);

        $fee2 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'due_date' => now()->addDays(20),
        ]);

        $fee3 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'due_date' => now()->addDays(40),
        ]);

        $startDate = now()->addDays(5);
        $endDate = now()->addDays(30);

        $feesInRange = MemberFee::dueBetween($startDate, $endDate)->get();

        expect($feesInRange)->toHaveCount(2);
        expect($feesInRange->pluck('id'))->toContain($fee1->id);
        expect($feesInRange->pluck('id'))->toContain($fee2->id);
        expect($feesInRange->pluck('id'))->not->toContain($fee3->id);
    });

    test('paid_between scope works correctly', function () {
        $fee1 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
            'paid_date' => now()->subDays(10),
        ]);

        $fee2 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
            'paid_date' => now()->subDays(20),
        ]);

        $fee3 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'status' => 'paid',
            'paid_date' => now()->subDays(40),
        ]);

        $startDate = now()->subDays(30);
        $endDate = now()->subDays(5);

        $feesInRange = MemberFee::paidBetween($startDate, $endDate)->get();

        expect($feesInRange)->toHaveCount(2);
        expect($feesInRange->pluck('id'))->toContain($fee1->id);
        expect($feesInRange->pluck('id'))->toContain($fee2->id);
        expect($feesInRange->pluck('id'))->not->toContain($fee3->id);
    });

    test('member relationship works correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
        ]);

        expect($fee->member)->toBeInstanceOf(Member::class);
        expect($fee->member->id)->toBe($this->member->id);
    });

    test('organization relationship works correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
        ]);

        expect($fee->organization)->toBeInstanceOf(Organization::class);
        expect($fee->organization->id)->toBe($this->organization->id);
    });

    test('soft deletes work correctly', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
        ]);

        $fee->delete();

        expect($fee->trashed())->toBeTrue();
        expect(MemberFee::find($fee->id))->toBeNull();
        expect(MemberFee::withTrashed()->find($fee->id))->not->toBeNull();
    });

    test('fillable attributes are correct', function () {
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'fee_type' => 'late_fee',
            'description' => 'Late payment fee',
            'amount' => 15.00,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_reference' => 'REF-123',
        ]);

        expect($fee->fee_type)->toBe('late_fee');
        expect($fee->description)->toBe('Late payment fee');
        expect($fee->amount)->toBeString();
        expect($fee->amount)->toBe('15.00');
        expect($fee->status)->toBe('pending');
        expect($fee->payment_method)->toBe('cash');
        expect($fee->payment_reference)->toBe('REF-123');
    });

    test('mass assignment protection works', function () {
        $fee = new MemberFee;

        expect($fee->getFillable())->toContain('member_id');
        expect($fee->getFillable())->toContain('fee_type');
        expect($fee->getFillable())->toContain('amount');
        expect($fee->getFillable())->toContain('organization_id');
        expect($fee->getFillable())->not->toContain('id');
        expect($fee->getFillable())->not->toContain('created_at');
        expect($fee->getFillable())->not->toContain('updated_at');
    });
});
