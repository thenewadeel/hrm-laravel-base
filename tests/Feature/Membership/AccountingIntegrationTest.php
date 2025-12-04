<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use App\Models\User;
use App\Services\Membership\FeeService;
use App\Services\Membership\MembershipService;
use App\Services\Membership\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Membership Accounting Integration', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->membershipService = new MembershipService;
        $this->subscriptionService = new SubscriptionService;
        $this->feeService = new FeeService;

        // Authenticate as user for created_by field
        $this->actingAs($this->user);

        // Setup accounting structure
        $this->membershipRevenueAccount = ChartOfAccount::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Membership Revenue',
            'type' => 'revenue',
            'code' => '4001',
        ]);

        $this->cashAccount = ChartOfAccount::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Cash',
            'type' => 'asset',
            'code' => '1001',
        ]);

        $this->accountsReceivableAccount = ChartOfAccount::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Accounts Receivable',
            'type' => 'asset',
            'code' => '1200',
        ]);
    });

    describe('Subscription Payment Integration', function () {
        test('creates journal entry for subscription payment', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->organization->id,
                'amount' => 299.99,
            ]);

            $subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'total_amount' => 299.99,
                'paid_amount' => 0,
                'status' => 'active',
            ]);

            // Process payment
            $paymentData = [
                'amount' => 299.99,
                'payment_method' => 'credit_card',
                'payment_reference' => 'SUB-'.uniqid(),
            ];

            $result = $this->subscriptionService->processSubscriptionPayment($subscription, $paymentData);

            expect($result)->toBeTrue();

            // Verify journal entry was created
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('reference_number', $paymentData['payment_reference'])
                ->first();

            expect($journalEntry)->not->toBeNull();
            expect($journalEntry->description)->toContain('Subscription payment');
            expect((float) $journalEntry->total_amount)->toBe(299.99);

            // Verify ledger entries
            $ledgerEntries = $journalEntry->ledgerEntries;
            expect($ledgerEntries)->toHaveCount(2);

            // Should debit cash and credit revenue
            $debitEntry = $ledgerEntries->where('type', 'debit')->first();
            $creditEntry = $ledgerEntries->where('type', 'credit')->first();

            expect($debitEntry->chart_of_account_id)->toBe($this->cashAccount->id);
            expect((float) $debitEntry->amount)->toBe(299.99);

            expect($creditEntry->chart_of_account_id)->toBe($this->membershipRevenueAccount->id);
            expect((float) $creditEntry->amount)->toBe(299.99);
        });

        test('creates accounts receivable for partial subscription payment', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->organization->id,
                'amount' => 299.99,
            ]);

            $subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'total_amount' => 299.99,
                'paid_amount' => 0,
                'status' => 'active',
            ]);

            // Process partial payment
            $paymentData = [
                'amount' => 150.00,
                'payment_method' => 'credit_card',
                'payment_reference' => 'SUB-PARTIAL-'.uniqid(),
            ];

            $result = $this->subscriptionService->processSubscriptionPayment($subscription, $paymentData);

            expect($result)->toBeTrue();

            // Verify journal entry was created
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('reference_number', $paymentData['payment_reference'])
                ->first();

            expect($journalEntry)->not->toBeNull();

            // Verify ledger entries
            $ledgerEntries = $journalEntry->ledgerEntries;
            expect($ledgerEntries)->toHaveCount(3); // Cash debit, Revenue credit, Receivable debit

            $entries = $ledgerEntries->keyBy('chart_of_account_id');

            expect($entries->has($this->cashAccount->id))->toBeTrue();
            expect($entries->has($this->membershipRevenueAccount->id))->toBeTrue();
            expect($entries->has($this->accountsReceivableAccount->id))->toBeTrue();

            expect((float) $entries[$this->cashAccount->id]->amount)->toBe(150.00);
            expect((float) $entries[$this->membershipRevenueAccount->id]->amount)->toBe(150.00);
            expect((float) $entries[$this->accountsReceivableAccount->id]->amount)->toBe(149.99); // 299.99 - 150 = 149.99
        });
    });

    describe('Member Fee Payment Integration', function () {
        test('creates journal entry for member fee payment', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $fee = $this->feeService->createFee($member, [
                'fee_type' => 'additional_service',
                'description' => 'Test fee',
                'amount' => 50.00,
                'due_date' => now(),
                'distribute_to_accounts' => true,
            ]);

            $paymentData = [
                'amount' => 50.00,
                'payment_method' => 'cash',
                'payment_reference' => 'FEE-'.uniqid(),
            ];

            $result = $this->feeService->processFeePayment($fee, $paymentData);

            expect($result)->toBeTrue();

            // Verify journal entry was created for payment
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('reference_number', $paymentData['payment_reference'])
                ->first();

            expect($journalEntry)->not->toBeNull();
            expect($journalEntry->description)->toContain('Member fee payment');
            expect((float) $journalEntry->total_amount)->toBe(50.00);

            // Verify ledger entries for payment
            $ledgerEntries = $journalEntry->ledgerEntries;
            expect($ledgerEntries)->toHaveCount(2);

            $debitEntry = $ledgerEntries->where('type', 'debit')->first();
            $creditEntry = $ledgerEntries->where('type', 'credit')->first();

            expect($debitEntry->chart_of_account_id)->toBe($this->cashAccount->id);
            expect((float) $debitEntry->amount)->toBe(50.00);

            expect($creditEntry->chart_of_account_id)->toBe($this->accountsReceivableAccount->id); // Payment reduces receivable
            expect((float) $creditEntry->amount)->toBe(50.00);
        });

        test('handles fee waiver with correct accounting entries', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $fee = $this->feeService->createFee($member, [
                'fee_type' => 'additional_service',
                'description' => 'Test fee for waiver',
                'amount' => 25.00,
                'due_date' => now(),
                'distribute_to_accounts' => true,
            ]);

            $result = $this->feeService->waiveFee($fee);

            expect($result)->toBeTrue();

            // Verify journal entry was created for waiver
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('description', 'like', '%waiver%')
                ->first();

            expect($journalEntry)->not->toBeNull();

            // Verify ledger entries for waiver (should credit revenue, debit expense/allowance)
            $ledgerEntries = $journalEntry->ledgerEntries;
            expect($ledgerEntries)->toHaveCount(2);

            $creditEntry = $ledgerEntries->where('type', 'credit')->first();
            expect($creditEntry->chart_of_account_id)->toBe($this->membershipRevenueAccount->id);
            expect((float) $creditEntry->amount)->toBe(25.00);
        });
    });

    describe('Family Member Fee Integration', function () {
        test('creates journal entries for family member additional fees', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->familyPlan()->create([
                'organization_id' => $this->organization->id,
            ]);

            // Add family members (2 additional, 1 included = 1 additional fee)
            $familyMembers = \App\Models\Membership\FamilyMember::factory()->count(3)->create([
                'organization_id' => $this->organization->id,
                'primary_member_id' => $member->id,
            ]);

            $subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'total_amount' => 299.99,
                'paid_amount' => 0,
                'status' => 'active',
            ]);

            // Process payment
            $paymentData = [
                'amount' => 80.00,
                'payment_method' => 'credit_card',
                'payment_reference' => 'FAMILY-SUB-'.uniqid(),
            ];

            $result = $this->subscriptionService->processSubscriptionPayment($subscription, $paymentData);

            expect($result)->toBeTrue();

            // Verify journal entry was created
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('reference_number', $paymentData['payment_reference'])
                ->first();

            expect($journalEntry)->not->toBeNull();
            expect((float) $journalEntry->total_amount)->toBe(80.00);

            // Verify ledger entries
            $ledgerEntries = $journalEntry->ledgerEntries;
            expect($ledgerEntries)->toHaveCount(3); // Cash debit, Revenue credit, Receivable debit

            $entries = $ledgerEntries->keyBy('chart_of_account_id');

            expect($entries->has($this->cashAccount->id))->toBeTrue();
            expect($entries->has($this->membershipRevenueAccount->id))->toBeTrue();
            expect($entries->has($this->accountsReceivableAccount->id))->toBeTrue();

            expect((float) $entries[$this->cashAccount->id]->amount)->toBe(80.00);
            expect((float) $entries[$this->membershipRevenueAccount->id]->amount)->toBe(80.00);
            expect((float) $entries[$this->accountsReceivableAccount->id]->amount)->toBe(219.99); // Actual outstanding amount based on factory values
        });
    });

    describe('Refund Integration', function () {
        test('processes subscription refunds with correct accounting entries', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->organization->id,
                'amount' => 299.99,
            ]);

            $subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'paid_amount' => 0,
                'status' => 'active',
            ]);

            // Process refund
            $refundData = [
                'amount' => 150.00,
                'reason' => 'Member cancellation',
                'refund_reference' => 'REF-'.uniqid(),
            ];

            $result = $this->subscriptionService->processRefund($subscription, $refundData);

            expect($result)->toBeTrue();

            // Verify journal entry was created for refund
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('reference_number', $refundData['refund_reference'])
                ->first();

            expect($journalEntry)->not->toBeNull();
            expect($journalEntry->description)->toContain('refund');
            expect((float) $journalEntry->total_amount)->toBe(150.00);

            // Verify ledger entries (reverse of payment)
            $ledgerEntries = $journalEntry->ledgerEntries;
            expect($ledgerEntries)->toHaveCount(2);

            // Should debit revenue (reduce revenue) and credit cash (reduce cash)
            $debitEntry = $ledgerEntries->where('type', 'debit')->first();
            $creditEntry = $ledgerEntries->where('type', 'credit')->first();

            expect($debitEntry->chart_of_account_id)->toBe($this->membershipRevenueAccount->id);
            expect((float) $debitEntry->amount)->toBe(150.00);

            expect($creditEntry->chart_of_account_id)->toBe($this->cashAccount->id);
            expect((float) $creditEntry->amount)->toBe(150.00);
        });
    });

    describe('Accounting Reports Integration', function () {
        test('membership revenue is reflected in accounting reports', function () {
            // Create multiple members with subscriptions
            $members = Member::factory()->count(3)->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->organization->id,
                'amount' => 100.00,
            ]);

            foreach ($members as $member) {
                $subscription = MemberSubscription::factory()->create([
                    'organization_id' => $this->organization->id,
                    'member_id' => $member->id,
                    'subscription_plan_id' => $plan->id,
                    'total_amount' => 100.00,
                    'paid_amount' => 100.00,
                    'status' => 'active',
                ]);

                // Process payment
                $this->subscriptionService->processPayment($subscription, [
                    'amount' => 100.00,
                    'payment_method' => 'credit_card',
                    'payment_reference' => 'SUB-'.$member->id,
                ]);
            }

            // Create some member fees
            foreach ($members as $member) {
                $fee = $this->feeService->createFee($member, [
                    'fee_type' => 'additional_service',
                    'description' => 'Test fee for reports',
                    'amount' => 25.00,
                    'due_date' => now(),
                    'distribute_to_accounts' => true,
                ]);

                $this->feeService->processFeePayment($fee, [
                    'amount' => 25.00,
                    'payment_method' => 'cash',
                    'payment_reference' => 'FEE-'.$member->id,
                ]);
            }

            // Check total revenue in membership account
            $totalRevenue = LedgerEntry::where('chart_of_account_id', $this->membershipRevenueAccount->id)
                ->where('type', 'credit')
                ->sum('amount');

            expect((float) $totalRevenue)->toBe(375.00); // 3 * 100 (subscriptions) + 3 * 25 (fees)
        });

        test('accounts receivable tracks unpaid membership fees', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->organization->id,
                'amount' => 300.00,
            ]);

            $subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'total_amount' => 300.00,
                'paid_amount' => 100.00, // Partial payment
                'status' => 'active',
            ]);

            // Process partial payment
            $this->subscriptionService->processPayment($subscription, [
                'amount' => 100.00,
                'payment_method' => 'credit_card',
                'payment_reference' => 'PARTIAL-'.uniqid(),
            ]);

            // Check accounts receivable balance
            $receivableBalance = LedgerEntry::where('chart_of_account_id', $this->accountsReceivableAccount->id)
                ->where('type', 'debit')
                ->sum('amount');

            expect((float) $receivableBalance)->toBe(100.00); // 300 total - 100 initial - 100 payment = 100 remaining
        });
    });

    describe('Transaction Rollback on Errors', function () {
        test('rolls back accounting entries on payment failure', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->organization->id,
                'amount' => 299.99,
            ]);

            $subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'total_amount' => 299.99,
                'paid_amount' => 0,
                'status' => 'active',
            ]);

            // Mock payment failure
            DB::shouldReceive('transaction')->once()->andThrow(new \Exception('Payment failed'));

            $paymentData = [
                'amount' => 299.99,
                'payment_method' => 'credit_card',
                'payment_reference' => 'FAIL-'.uniqid(),
            ];

            expect(fn () => $this->subscriptionService->processPayment($subscription, $paymentData))
                ->toThrow(\Exception::class);

            // Verify no journal entries were created
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('reference_number', $paymentData['payment_reference'])
                ->first();

            expect($journalEntry)->toBeNull();

            // Verify subscription was not updated
            expect((float) $subscription->fresh()->paid_amount)->toBe(0.0);
            expect($subscription->fresh()->status)->toBe('active');
        });
    });

    describe('Audit Trail Integration', function () {
        test('creates audit trail for membership transactions', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);
            $plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->organization->id,
                'amount' => 100.00,
            ]);

            $subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'total_amount' => 80.00,
                'paid_amount' => 0,
                'status' => 'active',
            ]);

            // Process payment
            $this->subscriptionService->processPayment($subscription, [
                'amount' => 100.00,
                'payment_method' => 'credit_card',
                'payment_reference' => 'AUDIT-'.uniqid(),
            ]);

            // Verify journal entry has audit information
            $journalEntry = JournalEntry::where('organization_id', $this->organization->id)
                ->where('reference_number', 'like', 'AUDIT-%')
                ->first();

            expect($journalEntry)->not->toBeNull();
            expect($journalEntry->created_at)->not->toBeNull();
            expect($journalEntry->updated_at)->not->toBeNull();

            // Verify ledger entries have proper timestamps
            foreach ($journalEntry->ledgerEntries as $ledgerEntry) {
                expect($ledgerEntry->created_at)->not->toBeNull();
                expect($ledgerEntry->updated_at)->not->toBeNull();
            }
        });
    });
});
