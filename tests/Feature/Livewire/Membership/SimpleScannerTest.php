<?php

use App\Livewire\Membership\SimpleScanner;
use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
});

it('renders simple scanner component successfully', function () {
    Livewire::test(SimpleScanner::class)
        ->assertStatus(200)
        ->assertSee('Member Access Scanner')
        ->assertSee('Test Valid Member')
        ->assertSee('Test Invalid Entry');
});

it('displays recent scans on mount', function () {
    Livewire::test(SimpleScanner::class)
        ->assertViewHas('recentScans')
        ->assertSee('John Doe')
        ->assertSee('Jane Smith')
        ->assertSee('Unknown');
});

it('can scan a valid member successfully', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'barcode_number' => 'BAR001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->assertDispatched('scan-success', member: $member->full_name)
        ->assertSet('showProfile', true)
        ->assertSet('currentMember.name', $member->full_name)
        ->assertSet('currentMember.member_id', $member->membership_number)
        ->assertSet('currentMember.is_active', true);
});

it('can scan by barcode number', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'barcode_number' => 'BAR001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'BAR001')
        ->call('scan')
        ->assertDispatched('scan-success')
        ->assertSet('showProfile', true)
        ->assertSet('currentMember.name', $member->full_name);
});

it('handles invalid scan attempts', function () {
    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'INVALID123')
        ->call('scan')
        ->assertDispatched('scan-error')
        ->assertSet('showProfile', false)
        ->assertSet('currentMember', null);
});

it('includes family members in scan results', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    $familyMember = FamilyMember::factory()->create([
        'organization_id' => $this->organization->id,
        'primary_member_id' => $member->id,
        'relationship' => 'Spouse',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->assertSet('currentMember.family_members.0.name', $familyMember->full_name)
        ->assertSet('currentMember.family_members.0.relationship', 'Spouse');
});

it('can test valid member with demo button', function () {
    // Create a member with the demo ID
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->call('scanDemo', 'valid')
        ->assertDispatched('scan-success', member: $member->full_name);
});

it('can test invalid entry with demo button', function () {
    Livewire::test(SimpleScanner::class)
        ->call('scanDemo', 'invalid')
        ->assertDispatched('scan-error');
});

it('can close member profile', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->assertSet('showProfile', true)
        ->call('closeProfile')
        ->assertSet('showProfile', false)
        ->assertSet('currentMember', null);
});

it('shows correct relationship colors for family members', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    $spouse = FamilyMember::factory()->create([
        'organization_id' => $this->organization->id,
        'primary_member_id' => $member->id,
        'relationship' => 'Spouse',
        'status' => 'active',
    ]);

    $son = FamilyMember::factory()->create([
        'organization_id' => $this->organization->id,
        'primary_member_id' => $member->id,
        'relationship' => 'Son',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->assertSet('currentMember.family_members.0.relationship_color', 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200')
        ->assertSet('currentMember.family_members.1.relationship_color', 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200');
});

it('includes enhanced member data in scan results', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
        'expiry_date' => now()->addDays(15), // Expiring soon
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->assertSet('currentMember.is_active', true)
        ->assertSet('currentMember.is_expired', false)
        ->assertSet('currentMember.is_expiring_soon', true)
        ->assertSet('currentMember.membership_type', fn ($value) => in_array($value, ['Gold', 'Platinum', 'Premium', 'Standard', 'Corporate']))
        ->assertSet('currentMember.access_level', fn ($value) => in_array($value, ['Full Access', 'Premium Access', 'Standard Access', 'Limited Access']))
        ->assertSet('currentMember.check_in_count', fn ($value) => is_int($value) && $value >= 15 && $value <= 45)
        ->assertSet('currentMember.recent_activity', fn ($value) => is_array($value) && count($value) >= 1);
});

it('can check in member from profile', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->call('checkInMember')
        ->assertDispatched('member-checked-in', member: $member->full_name);
});

it('can add note from profile', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->call('addNote')
        ->assertDispatched('add-note-modal', memberId: $member->id);
});

it('can view full profile from scanner', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->call('viewFullProfile')
        ->assertDispatched('view-full-profile', memberId: $member->id);
});

it('limits recent scans to last 5 entries', function () {
    Livewire::test(SimpleScanner::class)
        ->assertSet('recentScans', fn ($value) => count($value) <= 5);
});

it('clears scan input after scanning', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->assertSet('scanInput', '');
});

it('handles scanning state correctly', function () {
    Livewire::test(SimpleScanner::class)
        ->set('isScanning', true)
        ->assertSet('isScanning', true);
});

it('displays enhanced visual elements in profile modal', function () {
    $member = Member::factory()->create([
        'organization_id' => $this->organization->id,
        'membership_number' => 'MEM001',
        'status' => 'active',
    ]);

    Livewire::test(SimpleScanner::class)
        ->set('scanInput', 'MEM001')
        ->call('scan')
        ->assertSee('Check In Member')
        ->assertSee('Add Note')
        ->assertSee('Full Profile')
        ->assertSee('Contact Information')
        ->assertSee('Family Members')
        ->assertSee('Recent Activity')
        ->assertSee('Access Status')
        ->assertSee('Quick Stats')
        ->assertSee('Benefits');
});
