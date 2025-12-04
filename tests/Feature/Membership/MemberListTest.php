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
    $user->givePermissionTo(MembershipPermissions::VIEW_DASHBOARD, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $this->actingAs($user)
        ->get('/membership')
        ->assertStatus(200);
});

test('member list displays members correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $members = Member::factory()->count(3)->create([
        'organization_id' => $organization->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->assertSee($members->first()->first_name)
        ->assertSee($members->first()->membership_number)
        ->assertSeeHtml('<span class="font-medium">'.$members->count().'</span>')
        ->assertSee('results');
});

test('member list search functionality works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);
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
    $user->current_organization_id = $organization->id;
    $user->save();

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
    $user->givePermissionTo(MembershipPermissions::DELETE_MEMBERS, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization);
    $user->current_organization_id = $organization->id;
    $user->save();

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->call('deleteMember', $member->id);

    // Check if member was actually deleted
    $this->assertSoftDeleted('members', ['id' => $member->id]);

    // Check events without strict parameter matching first
    $component->assertDispatched('member-deleted');
    $component->assertDispatched('show-notification');

    $this->assertSoftDeleted('members', ['id' => $member->id]);
});

test('member list respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->organizations()->attach($organization1->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::VIEW_MEMBERS, $organization1);
    $user->current_organization_id = $organization1->id;
    $user->save();

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
    $user->current_organization_id = $organization->id;
    $user->save();

    Member::factory()->count(25)->create([
        'organization_id' => $organization->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberList::class)
        ->set('perPage', 10)
        ->assertSee('25')
        ->assertSee('results');
});
