<?php

use App\Livewire\Membership\FamilyMemberManager;
use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

it('renders family member manager component correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->assertStatus(200)
        ->assertSee('Family Member Management')
        ->assertSee('Add Family Member')
        ->assertSee('Family Members');
});

it('displays existing family members correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $familyMember1 = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
        'relationship' => 'Spouse',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);

    $familyMember2 = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
        'relationship' => 'Child',
        'first_name' => 'Jimmy',
        'last_name' => 'Doe',
    ]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->assertSee('Jane Doe')
        ->assertSee('Spouse')
        ->assertSee('Jimmy Doe')
        ->assertSee('Child');
});

it('adds new family member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    $familyData = [
        'relationship' => 'Child',
        'title' => 'Mr',
        'first_name' => 'Test',
        'last_name' => 'Child',
        'date_of_birth' => '2015-01-01',
        'gender' => 'male',
        'notes' => 'Test notes',
    ];

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('showForm', true)
        ->set('form', $familyData)
        ->call('save')
        ->assertDispatched('family-member-saved')
        ->assertSet('showForm', false);

    // Verify family member was added
    $this->assertDatabaseHas('family_members', [
        'primary_member_id' => $member->id,
        'first_name' => 'Test',
        'last_name' => 'Child',
        'relationship' => 'Child',
        'gender' => 'male',
    ]);
});

it('updates existing family member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $familyMember = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
        'relationship' => 'Child',
        'first_name' => 'Original',
        'last_name' => 'Name',
    ]);

    $this->actingAs($user);

    $updateData = [
        'relationship' => 'Spouse',
        'title' => 'Mrs',
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'date_of_birth' => '1990-01-01',
        'gender' => 'female',
        'notes' => 'Updated notes',
    ];

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('editingFamilyMember', $familyMember->id)
        ->set('form', $updateData)
        ->call('save')
        ->assertDispatched('family-member-saved')
        ->assertSet('editingFamilyMember', null);

    // Verify family member was updated
    $familyMember->refresh();
    expect($familyMember->first_name)->toBe('Updated');
    expect($familyMember->relationship)->toBe('Spouse');
    expect($familyMember->gender)->toBe('female');
});

it('deletes family member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $familyMember = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->call('deleteFamilyMember', $familyMember->id)
        ->assertDispatched('family-member-deleted');

    // Verify family member was deleted
    $this->assertSoftDeleted('family_members', [
        'id' => $familyMember->id,
    ]);
});

it('validates family member form correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    // Test with empty required fields
    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('showForm', true)
        ->set('form', [
            'relationship' => '',
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'gender' => '',
        ])
        ->call('save')
        ->assertHasErrors([
            'form.relationship',
            'form.first_name',
            'form.last_name',
            'form.date_of_birth',
            'form.gender',
        ]);
});

it('validates date of birth format correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('showForm', true)
        ->set('form', [
            'relationship' => 'Child',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'date_of_birth' => 'invalid-date',
            'gender' => 'male',
        ])
        ->call('save')
        ->assertHasErrors(['form.date_of_birth']);
});

it('validates gender field correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('showForm', true)
        ->set('form', [
            'relationship' => 'Child',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'date_of_birth' => '2015-01-01',
            'gender' => 'invalid',
        ])
        ->call('save')
        ->assertHasErrors(['form.gender']);
});

it('cancels form correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('showForm', true)
        ->set('form', ['relationship' => 'Child'])
        ->call('cancel')
        ->assertSet('showForm', false)
        ->assertSet('editingFamilyMember', null)
        ->assertSet('form', [
            'relationship' => '',
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'gender' => '',
            'notes' => '',
        ]);
});

it('searches family members correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $familyMember1 = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $familyMember2 = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

it('filters family members by relationship correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $familyMember1 = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
        'relationship' => 'Spouse',
    ]);

    $familyMember2 = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
        'relationship' => 'Child',
    ]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('relationshipFilter', 'Spouse')
        ->assertSee($familyMember1->full_name)
        ->assertDontSee($familyMember2->full_name);
});

it('generates barcode for family member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $familyMember = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->call('generateBarcode', $familyMember->id)
        ->assertDispatched('barcode-generated');
});

it('uploads photo for family member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $familyMember = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    $file = \Illuminate\Http\UploadedFile::fake()->image('photo.jpg');

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->set('photo', $file)
        ->call('uploadPhoto', $familyMember->id)
        ->assertDispatched('photo-uploaded');

    // Verify photo was uploaded
    $familyMember->refresh();
    expect($familyMember->photo_path)->not->toBeNull();
});

it('validates user permissions for family member management', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'member']); // Limited role

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(FamilyMemberManager::class, ['memberId' => $member->id])
        ->assertForbidden();
});
