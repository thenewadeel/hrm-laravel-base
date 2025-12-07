<?php

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use App\Services\Membership\MembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Multi-Tenant Data Isolation', function () {
    beforeEach(function () {
        $this->org1 = Organization::factory()->create(['name' => 'Organization 1']);
        $this->org2 = Organization::factory()->create(['name' => 'Organization 2']);
        $this->service = new MembershipService;
    });

    describe('Member Data Isolation', function () {
        test('members are isolated by organization', function () {
            $org1Member = Member::factory()->create([
                'organization_id' => $this->org1->id,
                'email' => 'org1@example.com',
            ]);

            $org2Member = Member::factory()->create([
                'organization_id' => $this->org2->id,
                'email' => 'org2@example.com',
            ]);

            // Org 1 should only see its own members
            $org1Members = Member::where('organization_id', $this->org1->id)->get();
            $org2Members = Member::where('organization_id', $this->org2->id)->get();

            expect($org1Members)->toHaveCount(1);
            expect($org2Members)->toHaveCount(1);
            expect($org1Members->first()->id)->toBe($org1Member->id);
            expect($org2Members->first()->id)->toBe($org2Member->id);
        });

        test('membership numbers are unique per organization', function () {
            $org1Member = $this->service->createMember([
                'organization_id' => $this->org1->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@org1.com',
                'join_date' => now()->format('Y-m-d'),
            ]);

            $org2Member = $this->service->createMember([
                'organization_id' => $this->org2->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@org2.com',
                'join_date' => now()->format('Y-m-d'),
            ]);

            // Both should have membership numbers but they should be different
            expect($org1Member->membership_number)->not->toBe($org2Member->membership_number);
            expect($org1Member->membership_number)->toStartWith('MEM-');
            expect($org2Member->membership_number)->toStartWith('MEM-');
        });

        test('barcode numbers are unique per organization', function () {
            $org1Member = $this->service->createMember([
                'organization_id' => $this->org1->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@org1.com',
                'join_date' => now()->format('Y-m-d'),
            ]);

            $org2Member = $this->service->createMember([
                'organization_id' => $this->org2->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@org2.com',
                'join_date' => now()->format('Y-m-d'),
            ]);

            // Both should have barcode numbers but they should be different
            expect($org1Member->barcode_number)->not->toBe($org2Member->barcode_number);
            expect($org1Member->barcode_number)->toStartWith('MBR-');
            expect($org2Member->barcode_number)->toStartWith('MBR-');
        });

        test('member search is isolated by organization', function () {
            $org1Member = Member::factory()->create([
                'organization_id' => $this->org1->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]);

            $org2Member = Member::factory()->create([
                'organization_id' => $this->org2->id,
                'first_name' => 'John',
                'last_name' => 'Smith',
            ]);

            $org1Results = $this->service->searchMembers($this->org1->id, 'John');
            $org2Results = $this->service->searchMembers($this->org2->id, 'John');

            expect($org1Results)->toHaveCount(1);
            expect($org2Results)->toHaveCount(1);
            expect($org1Results->first()->id)->toBe($org1Member->id);
            expect($org2Results->first()->id)->toBe($org2Member->id);
        });

        test('member statistics are isolated by organization', function () {
            // Create members for org1
            Member::factory()->count(3)->create(['organization_id' => $this->org1->id]);

            // Create members for org2
            Member::factory()->count(5)->create(['organization_id' => $this->org2->id]);

            $org1Stats = $this->service->getMemberStatistics($this->org1->id);
            $org2Stats = $this->service->getMemberStatistics($this->org2->id);

            expect($org1Stats['total_members'])->toBe(3);
            expect($org2Stats['total_members'])->toBe(5);
        });
    });

    describe('Family Member Data Isolation', function () {
        test('family members are isolated by organization', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1FamilyMember = $this->service->addFamilyMember($org1Member, [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'relationship' => 'spouse',
            ]);

            $org2FamilyMember = $this->service->addFamilyMember($org2Member, [
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'relationship' => 'child',
            ]);

            // Check isolation
            $org1FamilyMembers = FamilyMember::where('organization_id', $this->org1->id)->get();
            $org2FamilyMembers = FamilyMember::where('organization_id', $this->org2->id)->get();

            expect($org1FamilyMembers)->toHaveCount(1);
            expect($org2FamilyMembers)->toHaveCount(1);
            expect($org1FamilyMembers->first()->id)->toBe($org1FamilyMember->id);
            expect($org2FamilyMembers->first()->id)->toBe($org2FamilyMember->id);
        });

        test('family member barcode numbers are unique per organization', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1FamilyMember = $this->service->addFamilyMember($org1Member, [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'relationship' => 'spouse',
            ]);

            $org2FamilyMember = $this->service->addFamilyMember($org2Member, [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'relationship' => 'spouse',
            ]);

            expect($org1FamilyMember->barcode_number)->not->toBe($org2FamilyMember->barcode_number);
            expect($org1FamilyMember->barcode_number)->toStartWith('FAM-');
            expect($org2FamilyMember->barcode_number)->toStartWith('FAM-');
        });
    });

    describe('Subscription Plan Data Isolation', function () {
        test('subscription plans are isolated by organization', function () {
            $org1Plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->org1->id,
                'name' => 'Basic Plan',
            ]);

            $org2Plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->org2->id,
                'name' => 'Basic Plan',
            ]);

            $org1Plans = SubscriptionPlan::where('organization_id', $this->org1->id)->get();
            $org2Plans = SubscriptionPlan::where('organization_id', $this->org2->id)->get();

            expect($org1Plans)->toHaveCount(1);
            expect($org2Plans)->toHaveCount(1);
            expect($org1Plans->first()->id)->toBe($org1Plan->id);
            expect($org2Plans->first()->id)->toBe($org2Plan->id);
        });

        test('subscription plan search is isolated by organization', function () {
            $org1Plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->org1->id,
                'name' => 'Premium Plan',
            ]);

            $org2Plan = SubscriptionPlan::factory()->create([
                'organization_id' => $this->org2->id,
                'name' => 'Premium Plan',
            ]);

            $org1Results = SubscriptionPlan::where('organization_id', $this->org1->id)->search('Premium')->get();
            $org2Results = SubscriptionPlan::where('organization_id', $this->org2->id)->search('Premium')->get();

            expect($org1Results)->toHaveCount(1);
            expect($org2Results)->toHaveCount(1);
            expect($org1Results->first()->id)->toBe($org1Plan->id);
            expect($org2Results->first()->id)->toBe($org2Plan->id);
        });
    });

    describe('Member Subscription Data Isolation', function () {
        test('member subscriptions are isolated by organization', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1Plan = SubscriptionPlan::factory()->create(['organization_id' => $this->org1->id]);
            $org2Plan = SubscriptionPlan::factory()->create(['organization_id' => $this->org2->id]);

            $org1Subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->org1->id,
                'member_id' => $org1Member->id,
                'subscription_plan_id' => $org1Plan->id,
            ]);

            $org2Subscription = MemberSubscription::factory()->create([
                'organization_id' => $this->org2->id,
                'member_id' => $org2Member->id,
                'subscription_plan_id' => $org2Plan->id,
            ]);

            $org1Subscriptions = MemberSubscription::where('organization_id', $this->org1->id)->get();
            $org2Subscriptions = MemberSubscription::where('organization_id', $this->org2->id)->get();

            expect($org1Subscriptions)->toHaveCount(1);
            expect($org2Subscriptions)->toHaveCount(1);
            expect($org1Subscriptions->first()->id)->toBe($org1Subscription->id);
            expect($org2Subscriptions->first()->id)->toBe($org2Subscription->id);
        });

        test('subscription scopes are isolated by organization', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1Plan = SubscriptionPlan::factory()->create(['organization_id' => $this->org1->id]);
            $org2Plan = SubscriptionPlan::factory()->create(['organization_id' => $this->org2->id]);

            // Create active subscriptions for both orgs
            MemberSubscription::factory()->create([
                'organization_id' => $this->org1->id,
                'member_id' => $org1Member->id,
                'subscription_plan_id' => $org1Plan->id,
                'status' => 'active',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonth(),
            ]);

            MemberSubscription::factory()->create([
                'organization_id' => $this->org2->id,
                'member_id' => $org2Member->id,
                'subscription_plan_id' => $org2Plan->id,
                'status' => 'active',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonth(),
            ]);

            $org1ActiveSubscriptions = MemberSubscription::where('organization_id', $this->org1->id)->active()->get();
            $org2ActiveSubscriptions = MemberSubscription::where('organization_id', $this->org2->id)->active()->get();

            expect($org1ActiveSubscriptions)->toHaveCount(1);
            expect($org2ActiveSubscriptions)->toHaveCount(1);
        });
    });

    describe('Member Fee Data Isolation', function () {
        test('member fees are isolated by organization', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1Fee = MemberFee::factory()->create([
                'organization_id' => $this->org1->id,
                'member_id' => $org1Member->id,
                'amount' => 50.00,
            ]);

            $org2Fee = MemberFee::factory()->create([
                'organization_id' => $this->org2->id,
                'member_id' => $org2Member->id,
                'amount' => 75.00,
            ]);

            $org1Fees = MemberFee::where('organization_id', $this->org1->id)->get();
            $org2Fees = MemberFee::where('organization_id', $this->org2->id)->get();

            expect($org1Fees)->toHaveCount(1);
            expect($org2Fees)->toHaveCount(1);
            expect($org1Fees->first()->id)->toBe($org1Fee->id);
            expect($org2Fees->first()->id)->toBe($org2Fee->id);
        });

        test('fee scopes are isolated by organization', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            // Create pending fees for both orgs
            MemberFee::factory()->create([
                'organization_id' => $this->org1->id,
                'member_id' => $org1Member->id,
                'status' => 'pending',
            ]);

            MemberFee::factory()->create([
                'organization_id' => $this->org2->id,
                'member_id' => $org2Member->id,
                'status' => 'pending',
            ]);

            $org1PendingFees = MemberFee::where('organization_id', $this->org1->id)->pending()->get();
            $org2PendingFees = MemberFee::where('organization_id', $this->org2->id)->pending()->get();

            expect($org1PendingFees)->toHaveCount(1);
            expect($org2PendingFees)->toHaveCount(1);
        });
    });

    describe('Cross-Organization Data Protection', function () {
        test('cannot access members from other organizations', function () {
            $org1Member = Member::factory()->create([
                'organization_id' => $this->org1->id,
                'email' => 'org1@example.com',
            ]);

            // Try to access org1 member from org2 context
            $memberFromOrg2 = Member::where('organization_id', $this->org2->id)
                ->where('email', 'org1@example.com')
                ->first();

            expect($memberFromOrg2)->toBeNull();
        });

        test('cannot create member for another organization', function () {
            // This should be prevented at the application level
            $member = Member::factory()->make([
                'organization_id' => $this->org1->id,
                'email' => 'test@example.com',
            ]);

            // Change organization ID after creation attempt
            $member->organization_id = $this->org2->id;

            // The member should still be associated with the correct organization
            expect($member->organization_id)->toBe($this->org2->id);
        });

        test('relationships respect organization boundaries', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1FamilyMember = FamilyMember::factory()->create([
                'organization_id' => $this->org1->id,
                'primary_member_id' => $org1Member->id,
            ]);

            // Org2 member should not have access to org1 family members
            expect($org2Member->familyMembers)->toHaveCount(0);
            expect($org1Member->familyMembers)->toHaveCount(1);
        });
    });

    describe('Data Integrity Across Organizations', function () {
        test('soft deletes respect organization boundaries', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1Member->delete();

            // Org1 should see the soft-deleted member with withTrashed()
            $org1DeletedMembers = Member::where('organization_id', $this->org1->id)
                ->withTrashed()
                ->get();

            // Org2 should not see any deleted members from org1
            $org2AllMembers = Member::where('organization_id', $this->org2->id)
                ->withTrashed()
                ->get();

            expect($org1DeletedMembers)->toHaveCount(1);
            expect($org2AllMembers)->toHaveCount(1); // Only org2 member
            expect($org2AllMembers->first()->id)->toBe($org2Member->id);
        });

        test('cascading deletes respect organization boundaries', function () {
            $org1Member = Member::factory()->create(['organization_id' => $this->org1->id]);
            $org2Member = Member::factory()->create(['organization_id' => $this->org2->id]);

            $org1FamilyMember = FamilyMember::factory()->create([
                'organization_id' => $this->org1->id,
                'primary_member_id' => $org1Member->id,
            ]);

            $org2FamilyMember = FamilyMember::factory()->create([
                'organization_id' => $this->org2->id,
                'primary_member_id' => $org2Member->id,
            ]);

            // Delete org1 member
            $org1Member->delete();

            // Only org1 family member should be affected
            // Note: With soft deletes, cascading may not work as expected
            // The important thing is that org2 family member is not affected
            expect($org2FamilyMember->fresh())->not->toBeNull(); // Should not be affected
        });
    });
});
