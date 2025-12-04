<?php

use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use Livewire\Livewire;

test('membership member list renders successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);

    $this->actingAs($user)
        ->get('/membership')
        ->assertStatus(200);
});

test('member list displays members correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);

    $members = Member::factory()->count(3)->create([
        'organization_id' => $organization->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->assertSee($members->first()->first_name)
        ->assertSee($members->first()->membership_number)
        ->assertSee($members->count().' results');
});

test('member list search functionality works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);

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

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->set('search', 'John')
        ->assertSee($member1->first_name)
        ->assertDontSee($member2->first_name);
});

test('member list status filter works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);

    $activeMember = Member::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'active',
    ]);

    $inactiveMember = Member::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'inactive',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->set('status', 'active')
        ->assertSee($activeMember->first_name)
        ->assertDontSee($inactiveMember->first_name);
});

test('member list can delete member', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->call('deleteMember', $member->id)
        ->assertDispatched('member-deleted')
        ->assertDispatched('show-notification', ['message' => 'Member deleted successfully', 'type' => 'success']);

    $this->assertSoftDeleted('members', ['id' => $member->id]);
});

test('member list respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->organizations()->attach($organization1->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization1);

    $member1 = Member::factory()->create([
        'organization_id' => $organization1->id,
    ]);

    $member2 = Member::factory()->create([
        'organization_id' => $organization2->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->assertSee($member1->first_name)
        ->assertDontSee($member2->first_name);
});

test('member list pagination works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);

    Member::factory()->count(25)->create([
        'organization_id' => $organization->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->set('perPage', 10)
        ->assertSee('Showing 1 to 10 of 25 results');
});
