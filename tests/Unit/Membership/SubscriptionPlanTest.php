<?php

use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('SubscriptionPlan Model', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
    });

    test('can create a subscription plan with required fields', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Basic Plan',
            'amount' => 29.99,
        ]);

        expect($plan)->toBeInstanceOf(SubscriptionPlan::class);
        expect($plan->name)->toBe('Basic Plan');
        expect($plan->amount)->toBeString();
        expect($plan->amount)->toBe('29.99');
        expect($plan->organization_id)->toBe($this->organization->id);
    });

    test('casts decimal fields correctly', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'amount' => 29.99,
            'additional_family_member_fee' => 10.50,
        ]);

        expect($plan->amount)->toBeString();
        expect($plan->amount)->toBe('29.99');
        expect($plan->additional_family_member_fee)->toBeString();
        expect($plan->additional_family_member_fee)->toBe('10.50');
    });

    test('casts boolean fields correctly', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        expect($plan->is_active)->toBeBool();
        expect($plan->is_active)->toBeTrue();
    });

    test('casts benefits as array', function () {
        $benefits = ['Access to gym', 'Free parking', 'Personal trainer'];
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'benefits' => $benefits,
        ]);

        expect($plan->benefits)->toBeArray();
        expect($plan->benefits)->toEqual($benefits);
    });

    test('formatted_amount accessor returns correctly formatted string', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'amount' => 29.99,
        ]);

        expect($plan->formatted_amount)->toBe('29.99');
    });

    test('formatted_additional_fee accessor returns correctly formatted string', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'additional_family_member_fee' => 10.50,
        ]);

        expect($plan->formatted_additional_fee)->toBe('10.50');
    });

    test('calculate_total_cost works correctly for no additional members', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'amount' => 50.00,
            'family_members_included' => 2,
            'additional_family_member_fee' => 15.00,
        ]);

        $totalCost = $plan->calculateTotalCost(1); // 1 family member, 2 included

        expect($totalCost)->toBe(50.00);
    });

    test('calculate_total_cost works correctly with additional members', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'amount' => 50.00,
            'family_members_included' => 2,
            'additional_family_member_fee' => 15.00,
        ]);

        $totalCost = $plan->calculateTotalCost(4); // 4 family members, 2 included, 2 additional

        expect($totalCost)->toBe(80.00); // 50 + (2 * 15)
    });

    test('calculate_total_cost handles zero family members', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'amount' => 30.00,
            'family_members_included' => 0,
            'additional_family_member_fee' => 10.00,
        ]);

        $totalCost = $plan->calculateTotalCost(3); // 3 family members, 0 included, 3 additional

        expect($totalCost)->toBe(60.00); // 30 + (3 * 10)
    });

    test('active scope works correctly', function () {
        $activePlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        $inactivePlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => false,
        ]);

        $activePlans = SubscriptionPlan::active()->get();

        expect($activePlans)->toHaveCount(1);
        expect($activePlans->first()->id)->toBe($activePlan->id);
    });

    test('by_type scope works correctly', function () {
        $individualPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'plan_type' => 'individual',
        ]);

        $familyPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'plan_type' => 'family',
        ]);

        $individualPlans = SubscriptionPlan::byType('individual')->get();
        $familyPlans = SubscriptionPlan::byType('family')->get();

        expect($individualPlans)->toHaveCount(1);
        expect($familyPlans)->toHaveCount(1);
        expect($individualPlans->first()->id)->toBe($individualPlan->id);
        expect($familyPlans->first()->id)->toBe($familyPlan->id);
    });

    test('by_frequency scope works correctly', function () {
        $monthlyPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_frequency' => 'monthly',
        ]);

        $yearlyPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_frequency' => 'annually',
        ]);

        $monthlyPlans = SubscriptionPlan::byFrequency('monthly')->get();
        $yearlyPlans = SubscriptionPlan::byFrequency('annually')->get();

        expect($monthlyPlans)->toHaveCount(1);
        expect($yearlyPlans)->toHaveCount(1);
        expect($monthlyPlans->first()->id)->toBe($monthlyPlan->id);
        expect($yearlyPlans->first()->id)->toBe($yearlyPlan->id);
    });

    test('search scope works across name and description', function () {
        $plan1 = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Premium Gym Membership',
        ]);

        $plan2 = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'description' => 'Access to premium gym facilities',
        ]);

        $plan3 = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Basic Plan',
            'description' => 'Basic access only',
        ]);

        $searchResults = SubscriptionPlan::search('premium')->get();

        expect($searchResults)->toHaveCount(2);
        expect($searchResults->pluck('id'))->toContain($plan1->id);
        expect($searchResults->pluck('id'))->toContain($plan2->id);
        expect($searchResults->pluck('id'))->not->toContain($plan3->id);
    });

    test('subscriptions relationship works correctly', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $subscriptions = MemberSubscription::factory()->count(3)->create([
            'subscription_plan_id' => $plan->id,
        ]);

        expect($plan->subscriptions)->toHaveCount(3);
        expect($plan->subscriptions->first())->toBeInstanceOf(MemberSubscription::class);
    });

    test('active_subscriptions relationship works correctly', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $activeSubscription = MemberSubscription::factory()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $expiredSubscription = MemberSubscription::factory()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'expired',
        ]);

        expect($plan->activeSubscriptions)->toHaveCount(1);
        expect($plan->activeSubscriptions->first()->id)->toBe($activeSubscription->id);
    });

    test('organization relationship works correctly', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        expect($plan->organization)->toBeInstanceOf(Organization::class);
        expect($plan->organization->id)->toBe($this->organization->id);
    });

    test('soft deletes work correctly', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $plan->delete();

        expect($plan->trashed())->toBeTrue();
        expect(SubscriptionPlan::find($plan->id))->toBeNull();
        expect(SubscriptionPlan::withTrashed()->find($plan->id))->not->toBeNull();
    });

    test('fillable attributes are correct', function () {
        $plan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Plan',
            'description' => 'Test description',
            'plan_type' => 'individual',
            'billing_frequency' => 'monthly',
            'amount' => 29.99,
            'is_active' => true,
        ]);

        expect($plan->name)->toBe('Test Plan');
        expect($plan->description)->toBe('Test description');
        expect($plan->plan_type)->toBe('individual');
        expect($plan->billing_frequency)->toBe('monthly');
        expect((float) $plan->amount)->toBe(29.99);
        expect($plan->is_active)->toBeTrue();
    });

    test('mass assignment protection works', function () {
        $plan = new SubscriptionPlan;

        expect($plan->getFillable())->toContain('name');
        expect($plan->getFillable())->toContain('amount');
        expect($plan->getFillable())->toContain('organization_id');
        expect($plan->getFillable())->not->toContain('id');
        expect($plan->getFillable())->not->toContain('created_at');
        expect($plan->getFillable())->not->toContain('updated_at');
    });
});
