<?php

namespace Tests\Traits;

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use App\Roles\MembershipRoles;

trait SetupMembership
{
    use SetupOrganization;

    protected Organization $membershipOrganization;

    protected User $membershipAdmin;

    protected User $membershipStaff;

    protected Member $testMember;

    protected Member $familyMember;

    protected Member $expiredMember;

    protected Member $suspendedMember;

    protected SubscriptionPlan $basicPlan;

    protected SubscriptionPlan $premiumPlan;

    protected SubscriptionPlan $familyPlan;

    protected MemberSubscription $activeSubscription;

    protected MemberSubscription $expiredSubscription;

    protected MemberFee $pendingFee;

    protected MemberFee $paidFee;

    protected MemberFee $overdueFee;

    /**
     * Setup comprehensive membership test environment
     */
    protected function setupMembershipManagement(): void
    {
        $this->setupMembershipOrganization();
        $this->createMembershipUsers();
        $this->createSubscriptionPlans();
        $this->createTestMembers();
        $this->createSubscriptions();
        $this->createMemberFees();
    }

    /**
     * Setup organization for membership testing
     */
    protected function setupMembershipOrganization(): void
    {
        $this->membershipOrganization = Organization::factory()->create([
            'name' => 'Test Membership Organization '.uniqid(),
            'is_active' => true,
        ]);
    }

    /**
     * Create users with membership roles
     */
    protected function createMembershipUsers(): void
    {
        // Membership Admin
        $this->membershipAdmin = User::factory()->create([
            'name' => 'Membership Admin',
            'email' => 'membership'.uniqid().'@admin.com',
            'current_organization_id' => $this->membershipOrganization->id,
        ]);

        $this->membershipAdmin->organizations()->attach($this->membershipOrganization, [
            'roles' => json_encode([MembershipRoles::MEMBERSHIP_ADMIN]),
        ]);

        $this->membershipAdmin->givePermissionTo(
            MembershipPermissions::all(),
            $this->membershipOrganization
        );

        // Membership Staff
        $this->membershipStaff = User::factory()->create([
            'name' => 'Membership Staff',
            'email' => 'membership'.uniqid().'@staff.com',
            'current_organization_id' => $this->membershipOrganization->id,
        ]);

        $this->membershipStaff->organizations()->attach($this->membershipOrganization, [
            'roles' => json_encode(['membership_staff']),
        ]);
    }

    /**
     * Create subscription plans for testing
     */
    protected function createSubscriptionPlans(): void
    {
        $this->basicPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'name' => 'Basic Plan',
            'plan_type' => 'individual',
            'billing_frequency' => 'monthly',
            'amount' => 29.99,
            'family_members_included' => 0,
            'additional_family_member_fee' => 10.00,
            'is_active' => true,
        ]);

        $this->premiumPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'name' => 'Premium Plan',
            'plan_type' => 'individual',
            'billing_frequency' => 'annually',
            'amount' => 299.99,
            'family_members_included' => 2,
            'additional_family_member_fee' => 15.00,
            'is_active' => true,
        ]);

        $this->familyPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'name' => 'Family Plan',
            'plan_type' => 'family',
            'billing_frequency' => 'monthly',
            'amount' => 79.99,
            'family_members_included' => 4,
            'additional_family_member_fee' => 12.50,
            'is_active' => true,
        ]);
    }

    /**
     * Create test members with different statuses
     */
    protected function createTestMembers(): void
    {
        // Active member with family
        $this->testMember = Member::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'status' => 'active',
            'expiry_date' => now()->addYear(),
        ]);

        // Create family members for the test member
        FamilyMember::factory()->count(2)->create([
            'organization_id' => $this->membershipOrganization->id,
            'primary_member_id' => $this->testMember->id,
        ]);

        // Member with family plan
        $this->familyMember = Member::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@test.com',
            'status' => 'active',
            'expiry_date' => now()->addMonths(6),
        ]);

        FamilyMember::factory()->count(3)->create([
            'organization_id' => $this->membershipOrganization->id,
            'primary_member_id' => $this->familyMember->id,
        ]);

        // Expired member
        $this->expiredMember = Member::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'first_name' => 'Bob',
            'last_name' => 'Wilson',
            'email' => 'bob.wilson@test.com',
            'status' => 'expired',
            'expiry_date' => now()->subMonth(),
        ]);

        // Suspended member
        $this->suspendedMember = Member::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'email' => 'alice.brown@test.com',
            'status' => 'suspended',
            'expiry_date' => now()->addMonth(),
        ]);
    }

    /**
     * Create subscriptions for testing
     */
    protected function createSubscriptions(): void
    {
        // Active subscription
        $this->activeSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'member_id' => $this->testMember->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear()->subMonth(),
            'status' => 'active',
            'total_amount' => $this->basicPlan->amount,
            'paid_amount' => $this->basicPlan->amount,
            'auto_renew' => true,
        ]);

        // Expired subscription
        $this->expiredSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'member_id' => $this->expiredMember->id,
            'subscription_plan_id' => $this->premiumPlan->id,
            'start_date' => now()->subYears(2),
            'end_date' => now()->subMonth(),
            'status' => 'expired',
            'total_amount' => $this->premiumPlan->amount,
            'paid_amount' => $this->premiumPlan->amount,
            'auto_renew' => false,
        ]);
    }

    /**
     * Create member fees for testing
     */
    protected function createMemberFees(): void
    {
        // Pending fee
        $this->pendingFee = MemberFee::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'member_id' => $this->testMember->id,
            'fee_type' => 'subscription',
            'description' => 'Annual Membership Fee',
            'amount' => 50.00,
            'paid_amount' => 0,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
        ]);

        // Paid fee
        $this->paidFee = MemberFee::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'member_id' => $this->testMember->id,
            'fee_type' => 'additional_service',
            'description' => 'Registration Fee',
            'amount' => 25.00,
            'paid_amount' => 25.00,
            'due_date' => now()->subDays(60),
            'paid_date' => now()->subDays(45),
            'status' => 'paid',
            'payment_method' => 'credit_card',
            'payment_reference' => 'PAY-'.uniqid(),
        ]);

        // Overdue fee
        $this->overdueFee = MemberFee::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'member_id' => $this->suspendedMember->id,
            'fee_type' => 'late_fee',
            'description' => 'Late Payment Fee',
            'amount' => 15.00,
            'paid_amount' => 0,
            'due_date' => now()->subDays(15),
            'status' => 'overdue',
        ]);
    }

    /**
     * Create a member with subscription
     */
    protected function createMemberWithSubscription(array $memberOverrides = [], array $subscriptionOverrides = []): array
    {
        $member = Member::factory()->create(array_merge([
            'organization_id' => $this->membershipOrganization->id,
        ], $memberOverrides));

        $subscription = MemberSubscription::factory()->create(array_merge([
            'organization_id' => $this->membershipOrganization->id,
            'member_id' => $member->id,
            'subscription_plan_id' => $this->basicPlan->id,
        ], $subscriptionOverrides));

        return [$member, $subscription];
    }

    /**
     * Create a member with family members
     */
    protected function createMemberWithFamily(int $familyCount = 2, array $memberOverrides = [], array $familyOverrides = []): array
    {
        $member = Member::factory()->create(array_merge([
            'organization_id' => $this->membershipOrganization->id,
        ], $memberOverrides));

        $familyMembers = FamilyMember::factory()
            ->count($familyCount)
            ->create(array_merge([
                'organization_id' => $this->membershipOrganization->id,
                'primary_member_id' => $member->id,
            ], $familyOverrides));

        return [$member, $familyMembers];
    }

    /**
     * Create a member with fees
     */
    protected function createMemberWithFees(int $feeCount = 3, array $memberOverrides = [], array $feeOverrides = []): array
    {
        $member = Member::factory()->create(array_merge([
            'organization_id' => $this->membershipOrganization->id,
        ], $memberOverrides));

        $fees = MemberFee::factory()
            ->count($feeCount)
            ->create(array_merge([
                'organization_id' => $this->membershipOrganization->id,
                'member_id' => $member->id,
            ], $feeOverrides));

        return [$member, $fees];
    }

    /**
     * Get membership admin user for authentication
     */
    protected function getMembershipAdmin(): User
    {
        if (! $this->membershipAdmin) {
            $this->setupMembershipManagement();
        }

        return $this->membershipAdmin;
    }

    /**
     * Get membership staff user for authentication
     */
    protected function getMembershipStaff(): User
    {
        return $this->membershipStaff;
    }

    /**
     * Authenticate as membership admin
     */
    protected function actingAsMembershipAdmin(): self
    {
        $this->actingAs($this->getMembershipAdmin());

        return $this;
    }

    /**
     * Authenticate as membership staff
     */
    protected function actingAsMembershipStaff(): void
    {
        $this->actingAs($this->getMembershipStaff());
    }

    /**
     * Create test data for multi-tenant isolation testing
     */
    protected function createMultiTenantTestData(): array
    {
        // Create second organization
        $secondOrg = Organization::factory()->create([
            'name' => 'Second Organization',
        ]);

        // Create members in both organizations
        $org1Member = Member::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'email' => 'org1.member@test.com',
        ]);

        $org2Member = Member::factory()->create([
            'organization_id' => $secondOrg->id,
            'email' => 'org2.member@test.com',
        ]);

        // Create plans in both organizations
        $org1Plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'name' => 'Org1 Plan',
        ]);

        $org2Plan = SubscriptionPlan::factory()->create([
            'organization_id' => $secondOrg->id,
            'name' => 'Org2 Plan',
        ]);

        return [
            'org1' => $this->membershipOrganization,
            'org2' => $secondOrg,
            'org1_member' => $org1Member,
            'org2_member' => $org2Member,
            'org1_plan' => $org1Plan,
            'org2_plan' => $org2Plan,
        ];
    }
}
