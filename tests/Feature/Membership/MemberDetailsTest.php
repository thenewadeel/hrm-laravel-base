<?php

use App\Livewire\Membership\MemberDetails;
use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

it('renders member details component correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->assertStatus(200)
        ->assertSee($member->full_name)
        ->assertSee($member->membership_number)
        ->assertSee('Member Profile');
});

it('displays member contact information correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
        'email' => 'john.doe@example.com',
        'phone' => '+1234567890',
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'postal_code' => '10001',
    ]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->assertSee('john.doe@example.com')
        ->assertSee('+1234567890')
        ->assertSee('123 Main St')
        ->assertSee('New York')
        ->assertSee('NY')
        ->assertSee('10001');
});

it('displays family members correctly', function () {
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

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->assertSee('Family Members')
        ->assertSee('Jane Doe')
        ->assertSee('Spouse')
        ->assertSee('Jimmy Doe')
        ->assertSee('Child');
});

it('displays membership history correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
        'join_date' => now()->subYears(2),
        'expiry_date' => now()->addMonths(6),
    ]);

    // Create subscription history
    \App\Models\Membership\MemberSubscription::factory()->count(3)->create([
        'member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->assertSee('Membership History')
        ->assertSee('Join Date')
        ->assertSee($member->join_date->format('M j, Y'))
        ->assertSee('Expiry Date')
        ->assertSee($member->expiry_date->format('M j, Y'));
});

it('displays fee and payment history correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    // Create fee history
    \App\Models\Membership\MemberFee::factory()->count(5)->create([
        'member_id' => $member->id,
        'organization_id' => $organization->id,
        'amount' => 100.00,
        'paid_amount' => 100.00,
        'status' => 'paid',
    ]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->assertSee('Fee & Payment History')
        ->assertSee('$100.00');
});

it('generates QR code correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->call('generateQRCode')
        ->assertDispatched('qr-code-generated');
});

it('generates barcode correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->call('generateBarcode')
        ->assertDispatched('barcode-generated');
});

it('adds family member correctly', function () {
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
    ];

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->set('showAddFamilyMember', true)
        ->set('familyMemberForm', $familyData)
        ->call('addFamilyMember')
        ->assertDispatched('family-member-added');

    // Verify family member was added
    $this->assertDatabaseHas('family_members', [
        'primary_member_id' => $member->id,
        'first_name' => 'Test',
        'last_name' => 'Child',
        'relationship' => 'Child',
    ]);
});

it('updates family member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $familyMember = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    $updateData = [
        'relationship' => 'Spouse',
        'title' => 'Mrs',
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'date_of_birth' => '1990-01-01',
        'gender' => 'female',
    ];

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->set('editingFamilyMember', $familyMember->id)
        ->set('familyMemberForm', $updateData)
        ->call('updateFamilyMember')
        ->assertDispatched('family-member-updated');

    // Verify family member was updated
    $familyMember->refresh();
    expect($familyMember->first_name)->toBe('Updated');
    expect($familyMember->last_name)->toBe('Name');
    expect($familyMember->relationship)->toBe('Spouse');
});

it('removes family member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $familyMember = FamilyMember::factory()->create([
        'primary_member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->call('removeFamilyMember', $familyMember->id)
        ->assertDispatched('family-member-removed');

    // Verify family member was removed
    $this->assertSoftDeleted('family_members', [
        'id' => $familyMember->id,
    ]);
});

it('sends email to member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
        'email' => 'test@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->set('emailSubject', 'Test Subject')
        ->set('emailMessage', 'Test message')
        ->call('sendEmail')
        ->assertDispatched('email-sent');
});

it('sends SMS to member correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create([
        'organization_id' => $organization->id,
        'phone' => '+1234567890',
    ]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->set('smsMessage', 'Test SMS message')
        ->call('sendSMS')
        ->assertDispatched('sms-sent');
});

it('uploads member photo correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    $file = \Illuminate\Http\UploadedFile::fake()->image('photo.jpg');

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->set('photo', $file)
        ->call('uploadPhoto')
        ->assertDispatched('photo-uploaded');

    // Verify photo was uploaded
    $member->refresh();
    expect($member->photo_path)->not->toBeNull();
});

it('validates family member form correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'admin']);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    // Test with empty required fields
    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->set('showAddFamilyMember', true)
        ->set('familyMemberForm', [
            'relationship' => '',
            'first_name' => '',
            'last_name' => '',
        ])
        ->call('addFamilyMember')
        ->assertHasErrors(['familyMemberForm.relationship', 'familyMemberForm.first_name', 'familyMemberForm.last_name']);
});

it('validates user permissions for member details', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['role' => 'member']); // Limited role

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    Livewire::test(MemberDetails::class, ['memberId' => $member->id])
        ->assertForbidden();
});
