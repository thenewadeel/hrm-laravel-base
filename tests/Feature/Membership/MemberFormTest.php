<?php

use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SetupMembership;

uses(RefreshDatabase::class, SetupMembership::class);

beforeEach(function () {
    $this->setupMembershipManagement();
});

test('membership member form renders successfully', function () {
    // Use the existing demo organization and user that we know works
    $this->actingAsMembershipAdmin()->get('/members/create')->assertStatus(200);
});

test('member form can create new member', function () {
    // Test through HTTP request to ensure proper context
    $this->actingAs($this->getMembershipAdmin())
        ->post('/members', [
            'title' => 'Mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'gender' => 'male',
            'join_date' => now()->format('Y-m-d'),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('members', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'organization_id' => $this->membershipOrganization->id,
    ]);
});

test('member form validates required fields via HTTP', function () {
    $this->actingAs($this->getMembershipAdmin())
        ->post('/members', [])
        ->assertSessionHasErrors(['first_name', 'last_name', 'join_date']);
});

test('member form validates email format via HTTP', function () {
    $this->actingAs($this->getMembershipAdmin())
        ->post('/members', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'join_date' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors(['email']);
});

test('member form can update existing member via HTTP', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->membershipOrganization->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane.smith@example.com',
        'join_date' => now()->subYear()->format('Y-m-d'),
    ]);

    $this->actingAs($this->getMembershipAdmin())
        ->put("/members/{$member->id}", [
            'first_name' => 'Jane Updated',
            'last_name' => 'Smith',
            'email' => 'jane.updated@example.com',
            'gender' => 'female',
            'join_date' => $member->join_date->format('Y-m-d'),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('members', [
        'first_name' => 'Jane Updated',
        'last_name' => 'Smith',
        'email' => 'jane.updated@example.com',
    ]);
});

test('member form component renders on page', function () {
    $this->actingAs($this->getMembershipAdmin())
        ->get('/members/create')
        ->assertSeeLivewire(\App\Livewire\Membership\MemberForm::class)
        ->assertSee('New Member')
        ->assertSuccessful();
});
