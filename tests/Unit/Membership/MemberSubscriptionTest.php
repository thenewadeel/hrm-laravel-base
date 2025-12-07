<?php

use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MemberSubscription Model', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
        $this->member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $this->plan = SubscriptionPlan::factory()->create(['organization_id' => $this->organization->id]);
    });

    test('can create a subscription with required fields', function () {
        $subscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'total_amount' => 299.99,
        ]);

        expect($subscription)->toBeInstanceOf(MemberSubscription::class);
        expect($subscription->member_id)->toBe($this->member->id);
        expect($subscription->subscription_plan_id)->toBe($this->plan->id);
        expect($subscription->total_amount)->toBeString();
        expect($subscription->total_amount)->toBe('299.99');
        expect($subscription->organization_id)->toBe($this->organization->id);
    });

    test('casts dates correctly', function () {
        $subscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'start_date' => '2023-01-01',
            'end_date' => '2024-01-01',
        ]);

        expect($subscription->start_date)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($subscription->end_date)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($subscription->start_date->format('Y-m-d'))->toBe('2023-01-01');
        expect($subscription->end_date->format('Y-m-d'))->toBe('2024-01-01');
    });

    test('casts decimal fields correctly', function () {
        $subscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'total_amount' => 299.99,
            'paid_amount' => 150.50,
        ]);

        expect($subscription->total_amount)->toBeString();
        expect($subscription->total_amount)->toBe('299.99');
        expect($subscription->paid_amount)->toBeString();
        expect($subscription->paid_amount)->toBe('150.50');
    });

    test('casts boolean fields correctly', function () {
        $subscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'auto_renew' => true,
        ]);

        expect($subscription->auto_renew)->toBeBool();
        expect($subscription->auto_renew)->toBeTrue();
    });

    test('active scope works correctly', function () {
        $activeSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $futureSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonths(2),
        ]);

        $expiredSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(),
        ]);

        $cancelledSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'cancelled',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $activeSubscriptions = MemberSubscription::active()->get();

        expect($activeSubscriptions)->toHaveCount(1);
        expect($activeSubscriptions->first()->id)->toBe($activeSubscription->id);
    });

    test('expired scope works correctly', function () {
        $expiredSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'end_date' => now()->subMonth(),
        ]);

        $activeSubscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'end_date' => now()->addMonth(),
        ]);

        $expiredSubscriptions = MemberSubscription::expired()->get();

        expect($expiredSubscriptions)->toHaveCount(1);
        expect($expiredSubscriptions->first()->id)->toBe($expiredSubscription->id);
    });

    test('expiring_soon scope works correctly', function () {
        $expiringSoon = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'end_date' => now()->addDays(15),
        ]);

        $notExpiringSoon = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'end_date' => now()->addDays(45),
        ]);

        $expired = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'end_date' => now()->subDays(15),
        ]);

        $expiringSoonSubscriptions = MemberSubscription::expiringSoon(30)->get();

        expect($expiringSoonSubscriptions)->toHaveCount(1);
        expect($expiringSoonSubscriptions->first()->id)->toBe($expiringSoon->id);
    });

    test('expiring_soon scope with custom days works correctly', function () {
        $expiringIn10Days = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'end_date' => now()->addDays(10),
        ]);

        $expiringIn20Days = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'end_date' => now()->addDays(20),
        ]);

        $expiringSoonSubscriptions = MemberSubscription::expiringSoon(15)->get();

        expect($expiringSoonSubscriptions)->toHaveCount(1);
        expect($expiringSoonSubscriptions->first()->id)->toBe($expiringIn10Days->id);
    });

    test('by_member scope works correctly', function () {
        $member1 = Member::factory()->create(['organization_id' => $this->organization->id]);
        $member2 = Member::factory()->create(['organization_id' => $this->organization->id]);

        $subscription1 = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member1->id,
            'subscription_plan_id' => $this->plan->id,
        ]);

        $subscription2 = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member2->id,
            'subscription_plan_id' => $this->plan->id,
        ]);

        $member1Subscriptions = MemberSubscription::byMember($member1->id)->get();
        $member2Subscriptions = MemberSubscription::byMember($member2->id)->get();

        expect($member1Subscriptions)->toHaveCount(1);
        expect($member2Subscriptions)->toHaveCount(1);
        expect($member1Subscriptions->first()->id)->toBe($subscription1->id);
        expect($member2Subscriptions->first()->id)->toBe($subscription2->id);
    });

    test('by_plan scope works correctly', function () {
        $plan1 = SubscriptionPlan::factory()->create(['organization_id' => $this->organization->id]);
        $plan2 = SubscriptionPlan::factory()->create(['organization_id' => $this->organization->id]);

        $subscription1 = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $plan1->id,
        ]);

        $subscription2 = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $plan2->id,
        ]);

        $plan1Subscriptions = MemberSubscription::byPlan($plan1->id)->get();
        $plan2Subscriptions = MemberSubscription::byPlan($plan2->id)->get();

        expect($plan1Subscriptions)->toHaveCount(1);
        expect($plan2Subscriptions)->toHaveCount(1);
        expect($plan1Subscriptions->first()->id)->toBe($subscription1->id);
        expect($plan2Subscriptions->first()->id)->toBe($subscription2->id);
    });

    test('soft deletes work correctly', function () {
        $subscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
        ]);

        $subscription->delete();

        expect($subscription->trashed())->toBeTrue();
        expect(MemberSubscription::find($subscription->id))->toBeNull();
        expect(MemberSubscription::withTrashed()->find($subscription->id))->not->toBeNull();
    });

    test('fillable attributes are correct', function () {
        $subscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'total_amount' => 299.99,
            'paid_amount' => 299.99,
            'auto_renew' => true,
            'notes' => 'Test notes',
        ]);

        expect($subscription->status)->toBe('active');
        expect($subscription->total_amount)->toBe('299.99');
        expect($subscription->paid_amount)->toBe('299.99');
        expect($subscription->auto_renew)->toBeTrue();
        expect($subscription->notes)->toBe('Test notes');
    });

    test('mass assignment protection works', function () {
        $subscription = new MemberSubscription;

        expect($subscription->getFillable())->toContain('member_id');
        expect($subscription->getFillable())->toContain('subscription_plan_id');
        expect($subscription->getFillable())->toContain('total_amount');
        expect($subscription->getFillable())->toContain('organization_id');
        expect($subscription->getFillable())->not->toContain('id');
        expect($subscription->getFillable())->not->toContain('created_at');
        expect($subscription->getFillable())->not->toContain('updated_at');
    });

    test('validates status values', function () {
        $subscription = MemberSubscription::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
        ]);

        expect($subscription->status)->toBe('active');

        // Test other valid statuses
        $validStatuses = ['active', 'expired', 'cancelled', 'suspended'];

        foreach ($validStatuses as $status) {
            $testSubscription = MemberSubscription::factory()->create([
                'organization_id' => $this->organization->id,
                'member_id' => $this->member->id,
                'subscription_plan_id' => $this->plan->id,
                'status' => $status,
            ]);

            expect($testSubscription->status)->toBe($status);
        }
    });
});
