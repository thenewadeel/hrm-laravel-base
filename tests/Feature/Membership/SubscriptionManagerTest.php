<?php

use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use Livewire\Livewire;

test('subscription manager renders successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $this->actingAs($user)
        ->get('/subscriptions')
        ->assertStatus(200);
});

test('subscription manager displays subscriptions correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    $subscriptions = MemberSubscription::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SubscriptionManager::class)
        ->assertSee($subscriptions->first()->member->full_name)
        ->assertSee($plan->name)
        ->assertSee('Total Subscriptions');
});

test('subscription manager can create subscription', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SubscriptionManager::class)
        ->set('showCreateForm', true)
        ->set('member_id', $member->id)
        ->set('subscription_plan_id', $plan->id)
        ->set('start_date', now()->format('Y-m-d'))
        ->set('end_date', now()->addYear()->format('Y-m-d'))
        ->call('createSubscription');
    
    // Check for any errors
    $component->assertHasNoErrors();
    
    // Check if any notifications were dispatched (indicating errors)
    // Let's manually check if there are any validation errors by inspecting component
    $component->assertHasNoErrors();
    
    $this->assertDatabaseHas('member_subscriptions', [
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'organization_id' => $organization->id,
    ]);
});

test('subscription manager can renew subscription', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    $subscription = MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SubscriptionManager::class)
        ->call('renewSubscription', $subscription->id)
        ->assertDispatched('subscription-renewed')
        ->assertDispatched('show-notification');
});

test('subscription manager can cancel subscription', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $plan = SubscriptionPlan::factory()->create(['organization_id' => $organization->id]);

    $subscription = MemberSubscription::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SubscriptionManager::class)
        ->call('confirmCancelSubscription', $subscription->id, 'Test cancellation')
        ->assertDispatched('subscription-cancelled')
        ->assertDispatched('show-notification');

    $this->assertDatabaseHas('member_subscriptions', [
        'id' => $subscription->id,
        'status' => 'cancelled',
    ]);
});

test('subscription manager search functionality works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

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

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\SubscriptionManager::class)
        ->set('search', 'John')
        ->assertSee($member1->full_name)
        ->assertDontSee($member2->full_name);
});

test('subscription manager respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->organizations()->attach($organization1->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_SUBSCRIPTIONS, $organization1);
    $user->current_organization_id = $organization1->id;
    $user->save();

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
        ->test(\App\Livewire\Membership\SubscriptionManager::class)
        ->assertSee($member1->full_name)
        ->assertDontSee($member2->full_name);
});
