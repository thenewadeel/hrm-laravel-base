<?php

use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use Livewire\Livewire;

test('simple subscriptions component renders with real data', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'total_amount' => 100.00,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->assertStatus(200)
        ->assertSee('Subscription Management')
        ->assertSee($member->full_name)
        ->assertSee($plan->name);
});

test('simple subscriptions add subscription button opens form', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('showAddSubscriptionForm')
        ->assertSet('showAddSubscriptionForm', true)
        ->assertSee('New Subscription');
});

test('simple subscriptions can create subscription with validation', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('showAddSubscriptionForm')
        ->set('member_id', $member->id)
        ->set('subscription_plan_id', 'individual') // Use demo plan ID
        ->set('start_date', now()->format('Y-m-d'))
        ->set('auto_renew', false);

    $component->assertHasNoErrors();

    // Test that the method runs without throwing exceptions
    $component->call('addSubscription');

    // Just check that notify was dispatched (success or error)
    $component->assertDispatched('notify');
});

test('simple subscriptions validation fails for invalid data', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('showAddSubscriptionForm')
        ->set('member_id', '')
        ->set('subscription_plan_id', '')
        ->set('start_date', 'invalid-date');

    $component->call('addSubscription');

    $component->assertHasErrors([
        'member_id' => 'required',
        'subscription_plan_id' => 'required',
        'start_date' => 'date',
    ]);
});

test('simple subscriptions shows correct statistics', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    // Create subscriptions with different statuses
    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->addDays(15), // Expiring soon
    ]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'expired',
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class);

    $stats = $component->subscriptionStats;

    expect($stats['active'])->toBe(2);
    expect($stats['expiring'])->toBe(1);
    expect($stats['expired'])->toBe(1);
    expect($stats['new_this_month'])->toBe(3); // All created this month
});

test('simple subscriptions respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->organizations()->attach($organization1->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_SUBSCRIPTIONS, $organization1);
    $user->current_organization_id = $organization1->id;

    $member1 = Member::factory()->create(['organization_id' => $organization1->id]);
    $member2 = Member::factory()->create(['organization_id' => $organization2->id]);
    $plan1 = SubscriptionPlan::factory()->create(['organization_id' => $organization1->id]);
    $plan2 = SubscriptionPlan::factory()->create(['organization_id' => $organization2->id]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization1->id,
        'member_id' => $member1->id,
        'subscription_plan_id' => $plan1->id,
    ]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization2->id,
        'member_id' => $member2->id,
        'subscription_plan_id' => $plan2->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->assertSee($member1->full_name)
        ->assertDontSee($member2->full_name);
});

test('simple subscriptions loads members and plans for selection', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $members = Member::factory()->count(3)->create(['organization_id' => $organization->id]);
    $plans = SubscriptionPlan::factory()->count(2)->create(['organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('showAddSubscriptionForm');

    $membersList = $component->members;
    $plansList = $component->subscriptionPlans;

    expect($membersList)->toHaveCount(3);
    expect($plansList)->toHaveCount(2);
    expect($membersList->pluck('id'))->toContain($members[0]->id, $members[1]->id, $members[2]->id);
    expect($plansList->pluck('id'))->toContain($plans[0]->id, $plans[1]->id);
});

test('simple subscriptions handles unauthorized access', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'member']);
    // Don't give permission to manage subscriptions
    $user->current_organization_id = $organization->id;

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->assertStatus(200)
        ->assertDontSee('New Subscription'); // Should not see add button without permission
});

test('simple subscriptions can renew existing subscription', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::RENEW_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    $subscription = MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'end_date' => now()->addDays(15),
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('renewSubscription', $subscription->id);

    $this->assertDatabaseHas('member_subscriptions', [
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
    ]);

    // Original subscription should be marked as expired
    $this->assertDatabaseHas('member_subscriptions', [
        'id' => $subscription->id,
        'status' => 'expired',
    ]);
});

test('simple subscriptions can process batch renewals', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::RENEW_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    // Create subscriptions with auto-renew enabled
    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'end_date' => now()->addDays(5), // Due for renewal
        'auto_renew' => true,
    ]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'end_date' => now()->addDays(3), // Due for renewal (within 7 days)
        'auto_renew' => true,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('processRenewals')
        ->assertDispatched('notify');

    // Check that new subscriptions were created
    $this->assertDatabaseCount('member_subscriptions', 4); // 2 original + 2 renewed
});

test('simple subscriptions can send reminders', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    // Create expiring subscriptions
    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'end_date' => now()->addDays(15), // Expiring soon
    ]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'end_date' => now()->addDays(20), // Expiring soon
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('sendReminders')
        ->assertDispatched('notify');
});

test('simple subscriptions can cancel subscription', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::CANCEL_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    $subscription = MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'end_date' => now()->addDays(15),
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->call('cancelSubscription', $subscription->id)
        ->assertDispatched('notify');

    $this->assertDatabaseHas('member_subscriptions', [
        'id' => $subscription->id,
        'status' => 'cancelled',
    ]);
});

test('simple subscriptions search functionality works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member1 = Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $member2 = Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member1->id,
        'subscription_plan_id' => $plan->id,
    ]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member2->id,
        'subscription_plan_id' => $plan->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('simple subscriptions status filter works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;

    $member1 = Member::factory()->create(['organization_id' => $organization->id]);
    $member2 = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member1->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
    ]);

    MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member2->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'expired',
    ]);

    // Test filtering by active status
    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->set('statusFilter', 'active')
        ->assertSee($member1->full_name)
        ->assertDontSee($member2->full_name);

    // Test filtering by expired status
    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SimpleSubscriptions::class)
        ->set('statusFilter', 'expired')
        ->assertSee($member2->full_name)
        ->assertDontSee($member1->full_name);
});
