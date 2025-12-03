<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Membership\MemberSubscription;
use App\Permissions\MembershipPermissions;
use Livewire\Livewire;

test('membership dashboard renders successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    $this->actingAs($user)
        ->get('/membership')
        ->assertStatus(200);
});

test('membership dashboard displays statistics correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    Member::factory()->count(10)->create(['organization_id' => $organization->id]);
    
    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->assertSee('Total Members')
        ->assertSee('10');
});

test('membership dashboard shows recent members', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    $recentMember = Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe'
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->assertSee($recentMember->full_name)
        ->assertSee($recentMember->membership_number);
});

test('membership dashboard shows expiring members', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    $expiringMember = Member::factory()->create([
        'organization_id' => $organization->id,
        'expiry_date' => now()->addDays(15),
        'first_name' => 'Jane',
        'last_name' => 'Smith'
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->assertSee($expiringMember->full_name)
        ->assertSee('Expiring Soon');
});

test('membership dashboard shows alerts for overdue fees', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    $member = Member::factory()->create(['organization_id' => $organization->id]);
    
    MemberFee::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'overdue'
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->assertSee('Overdue Fees')
        ->assertSee('3 fees are overdue');
});

test('membership dashboard period selector works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->set('period', 'week')
        ->assertSet('period', 'week')
        ->set('period', 'month')
        ->assertSet('period', 'month')
        ->set('period', 'quarter')
        ->assertSet('period', 'quarter')
        ->set('period', 'year')
        ->assertSet('period', 'year');
});

test('membership dashboard shows quick actions', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->assertSee('Add New Member')
        ->assertSee('Create Subscription')
        ->assertSee('Generate Cards')
        ->assertSee('Process Fees');
});

test('membership dashboard respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->organizations()->attach($organization1->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization1);
    
    Member::factory()->count(5)->create(['organization_id' => $organization1->id]);
    Member::factory()->count(10)->create(['organization_id' => $organization2->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->assertSee('5') // Should only see organization 1 members
        ->assertDontSee('10'); // Should not see organization 2 members
});

test('membership dashboard shows growth metrics', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    
    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MembershipDashboard::class)
        ->assertSee('Revenue (Last 30 Days)')
        ->assertSee('New Members')
        ->assertSee('Growth');
});
