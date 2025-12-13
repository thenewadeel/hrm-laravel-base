<?php

use App\Livewire\Membership\AdvancedMemberList;
use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

it('renders the advanced member list component', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $this->actingAs($user);

    Livewire::test(AdvancedMemberList::class)
        ->assertStatus(200)
        ->assertSee('Advanced Member Management')
        ->assertSee('Export')
        ->assertSee('Total Members');
});

it('displays member statistics correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    // Create members with different statuses
    Member::factory()->count(5)->create(['organization_id' => $organization->id, 'status' => 'active']);
    Member::factory()->count(2)->create(['organization_id' => $organization->id, 'status' => 'inactive']);
    Member::factory()->count(1)->create(['organization_id' => $organization->id, 'status' => 'expired']);

    $this->actingAs($user);

    Livewire::test(AdvancedMemberList::class)
        ->assertSee('Total Members')
        ->assertSee('8') // Total members
        ->assertSee('5') // Active members
        ->assertSee('2') // Inactive members
        ->assertSee('1'); // Expired members
});

it('filters members by status correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    // Create members with different statuses
    $activeMembers = Member::factory()->count(3)->create(['organization_id' => $organization->id, 'status' => 'active']);
    Member::factory()->count(2)->create(['organization_id' => $organization->id, 'status' => 'inactive']);

    $this->actingAs($user);

    $component = Livewire::test(AdvancedMemberList::class)
        ->set('status', 'active');

    // Should only show active members
    foreach ($activeMembers as $member) {
        $component->assertSee($member->full_name);
    }

    $component->assertSet('status', 'active');
});

it('searches members by name correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

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

    $this->actingAs($user);

    Livewire::test(AdvancedMemberList::class)
        ->set('search', 'John')
        ->assertSee($member1->full_name)
        ->assertDontSee($member2->full_name);
});

it('searches members by membership number correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
        'membership_number' => 'MEM-2025-12345',
    ]);

    $this->actingAs($user);

    Livewire::test(AdvancedMemberList::class)
        ->set('search', 'MEM-2025-12345')
        ->assertSee($member->full_name);
});

it('sorts members by different columns', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $member1 = Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'Alice',
        'last_name' => 'Johnson',
    ]);

    $member2 = Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'Bob',
        'last_name' => 'Smith',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(AdvancedMemberList::class)
        ->set('sortBy', 'first_name')
        ->set('sortDirection', 'asc');

    // Should show Alice first (alphabetical order)
    $html = $component->html();
    $alicePosition = strpos($html, $member1->full_name);
    $bobPosition = strpos($html, $member2->full_name);

    expect($alicePosition)->toBeLessThan($bobPosition);
});

it('filters by date range correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $member1 = Member::factory()->create([
        'organization_id' => $organization->id,
        'join_date' => now()->subDays(10),
    ]);

    $member2 = Member::factory()->create([
        'organization_id' => $organization->id,
        'join_date' => now()->subDays(40),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(AdvancedMemberList::class)
        ->set('dateRange', 'last_30_days');

    $component->assertSee($member1->full_name)
        ->assertDontSee($member2->full_name);
});

it('shows family members count correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    FamilyMember::factory()->count(3)->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    Livewire::test(AdvancedMemberList::class)
        ->assertSee('3 family member(s)');
});

it('exports members to CSV correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    Member::factory()->count(3)->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(AdvancedMemberList::class)
        ->call('exportMembers', 'csv')
        ->assertDispatched('export-completed');
});

it('performs bulk status changes correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $members = Member::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $memberIds = $members->pluck('id')->toArray();

    Livewire::test(AdvancedMemberList::class)
        ->set('selectedMembers', $memberIds)
        ->set('bulkAction', 'suspend')
        ->call('performBulkAction')
        ->assertDispatched('bulk-action-completed');

    // Verify members were suspended
    foreach ($members as $member) {
        $member->refresh();
        expect($member->status)->toBe('suspended');
    }
});

it('filters by subscription status correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $memberWithSubscription = Member::factory()->create(['organization_id' => $organization->id]);
    $memberWithoutSubscription = Member::factory()->create(['organization_id' => $organization->id]);

    // Create subscription for first member
    \App\Models\Membership\MemberSubscription::factory()->create([
        'member_id' => $memberWithSubscription->id,
        'organization_id' => $organization->id,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(AdvancedMemberList::class)
        ->set('subscriptionStatus', 'active');

    $component->assertSee($memberWithSubscription->full_name)
        ->assertDontSee($memberWithoutSubscription->full_name);
});

it('handles pagination correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    Member::factory()->count(25)->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    $component = Livewire::test(AdvancedMemberList::class)
        ->set('perPage', 10);

    // Should show pagination controls
    $component->assertSee('Next');
    
    // Should show pagination info (check for any of the possible pagination texts)
    $component->assertSee('Showing')
              ->assertSee('25');
});

it('validates user permissions correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode(['member'])]); // Limited role

    $this->actingAs($user);

    Livewire::test(AdvancedMemberList::class)
        ->assertForbidden();
});

it('saves and loads filter presets correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode([App\Roles\MembershipRoles::MEMBERSHIP_ADMIN])]);

    $this->actingAs($user);

    $component = Livewire::test(AdvancedMemberList::class)
        ->set('status', 'active')
        ->set('sortBy', 'first_name')
        ->set('perPage', 25)
        ->call('saveFilterPreset', 'Active Members Sorted');

    $component->assertDispatched('filter-preset-saved');

    // Test loading preset
    $component->call('loadFilterPreset', 'Active Members Sorted')
        ->assertSet('status', 'active')
        ->assertSet('sortBy', 'first_name')
        ->assertSet('perPage', 25);
});
