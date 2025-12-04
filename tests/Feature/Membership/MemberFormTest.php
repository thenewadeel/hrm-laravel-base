<?php

use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use Livewire\Livewire;

test('membership member form renders successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_MEMBERS, $organization);

    $this->actingAs($user)
        ->get('/members/create')
        ->assertStatus(200);
});

test('member form can create new member', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_MEMBERS, $organization);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberForm::class)
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('email', 'john@example.com')
        ->set('join_date', now()->format('Y-m-d'))
        ->call('save')
        ->assertDispatched('member-created')
        ->assertDispatched('show-notification', ['message' => 'Member created successfully', 'type' => 'success']);

    $this->assertDatabaseHas('members', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'organization_id' => $organization->id,
    ]);
});

test('member form validates required fields', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_MEMBERS, $organization);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberForm::class)
        ->call('save')
        ->assertHasErrors(['first_name', 'last_name', 'join_date']);
});

test('member form validates email format', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_MEMBERS, $organization);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberForm::class)
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('email', 'invalid-email')
        ->set('join_date', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors(['email']);
});

test('member form can add family members', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_MEMBERS, $organization);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberForm::class)
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('join_date', now()->format('Y-m-d'))
        ->call('addFamilyMember')
        ->assertSet('family_members.0.relationship', '')
        ->assertSet('family_members.0.first_name', '')
        ->assertSet('family_members.0.last_name', '');
});

test('member form can update existing member', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_MEMBERS, $organization);

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberForm::class, ['member' => $member])
        ->assertSet('first_name', 'John')
        ->assertSet('last_name', 'Doe')
        ->set('first_name', 'Jane')
        ->set('last_name', 'Smith')
        ->call('save')
        ->assertDispatched('member-updated')
        ->assertDispatched('show-notification', ['message' => 'Member updated successfully', 'type' => 'success']);

    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);
});

test('member form handles photo upload', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_MEMBERS, $organization);

    $photo = \Illuminate\Http\UploadedFile::fake()->image('member-photo.jpg', 100, 100);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\MemberForm::class)
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('join_date', now()->format('Y-m-d'))
        ->set('photo', $photo)
        ->call('save')
        ->assertDispatched('member-created');

    $this->assertDatabaseHas('members', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'organization_id' => $organization->id,
    ]);
});
