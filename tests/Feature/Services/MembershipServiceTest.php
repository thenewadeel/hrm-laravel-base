<?php

use App\Services\Membership\MembershipService;
use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create a member with auto-generated numbers', function () {
    $organization = Organization::factory()->create();
    $service = new MembershipService();
    
    $memberData = [
        'organization_id' => $organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '123-456-7890',
        'join_date' => now(),
        'status' => 'active',
    ];
    
    $member = $service->createMember($memberData);
    
    expect($member)->toBeInstanceOf(Member::class);
    expect($member->membership_number)->not->toBeEmpty();
    expect($member->barcode_number)->not->toBeEmpty();
    expect($member->membership_number)->toStartWith('MEM-');
    expect($member->barcode_number)->toStartWith('MBR-');
    expect($member->first_name)->toBe('John');
    expect($member->last_name)->toBe('Doe');
});

test('can update an existing member', function () {
    $member = Member::factory()->create();
    $service = new MembershipService();
    
    $updateData = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
    ];
    
    $updatedMember = $service->updateMember($member, $updateData);
    
    expect($updatedMember->first_name)->toBe('Jane');
    expect($updatedMember->last_name)->toBe('Smith');
    expect($updatedMember->email)->toBe('jane@example.com');
});

test('can add family member to existing member', function () {
    $member = Member::factory()->create();
    $service = new MembershipService();
    
    $familyData = [
        'relationship' => 'Spouse',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-01-01',
    ];
    
    $familyMember = $service->addFamilyMember($member, $familyData);
    
    expect($familyMember)->toBeInstanceOf(FamilyMember::class);
    expect($familyMember->primary_member_id)->toBe($member->id);
    expect($familyMember->relationship)->toBe('Spouse');
    expect($familyMember->first_name)->toBe('Jane');
    expect($familyMember->barcode_number)->toStartWith('FAM-');
});

test('can deactivate a member and family members', function () {
    $member = Member::factory()->active()->create();
    $familyMembers = FamilyMember::factory()->count(2)->create(['primary_member_id' => $member->id]);
    
    $service = new MembershipService();
    $result = $service->deactivateMember($member);
    
    expect($result)->toBeTrue();
    expect($member->fresh()->status)->toBe('inactive');
    expect($member->fresh()->familyMembers->every('status', 'inactive'))->toBeTrue();
});

test('can suspend a member with reason', function () {
    $member = Member::factory()->active()->create();
    $service = new MembershipService();
    
    $result = $service->suspendMember($member, 'Non-payment of dues');
    
    expect($result)->toBeTrue();
    expect($member->fresh()->status)->toBe('suspended');
    expect($member->fresh()->notes)->toContain('Non-payment of dues');
});

test('can reactivate a suspended member', function () {
    $member = Member::factory()->create([
        'status' => 'suspended',
        'expiry_date' => now()->addMonth(),
    ]);
    
    $service = new MembershipService();
    $result = $service->reactivateMember($member);
    
    expect($result)->toBeTrue();
    expect($member->fresh()->status)->toBe('active');
});

test('cannot reactivate expired member', function () {
    $member = Member::factory()->create([
        'status' => 'suspended',
        'expiry_date' => now()->subMonth(),
    ]);
    
    $service = new MembershipService();
    
    expect(fn() => $service->reactivateMember($member))
        ->toThrow(\InvalidArgumentException::class, 'Cannot reactivate expired member');
});

test('generates unique membership numbers', function () {
    $organization = Organization::factory()->create();
    $service = new MembershipService();
    
    $number1 = $service->generateMembershipNumber($organization->id);
    $number2 = $service->generateMembershipNumber($organization->id);
    
    expect($number1)->not->toBe($number2);
    expect($number1)->toStartWith('MEM-');
    expect($number2)->toStartWith('MEM-');
});

test('generates unique barcode numbers', function () {
    $organization = Organization::factory()->create();
    $service = new MembershipService();
    
    $barcode1 = $service->generateBarcodeNumber($organization->id);
    $barcode2 = $service->generateBarcodeNumber($organization->id);
    
    expect($barcode1)->not->toBe($barcode2);
    expect($barcode1)->toStartWith('MBR-');
    expect($barcode2)->toStartWith('MBR-');
});

test('can search members across multiple fields', function () {
    $organization = Organization::factory()->create();
    $member1 = Member::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'John',
        'last_name' => 'Smith',
    ]);
    $member2 = Member::factory()->create([
        'organization_id' => $organization->id,
        'email' => 'john.doe@example.com',
    ]);
    $member3 = Member::factory()->create([
        'organization_id' => $organization->id,
        'phone' => '123-456-7890',
    ]);
    
    $service = new MembershipService();
    
    // Search by name
    $results = $service->searchMembers($organization->id, 'john');
    expect($results)->toHaveCount(2);
    expect($results->pluck('id'))->toContain($member1->id, $member2->id);
    
    // Search by email
    $results = $service->searchMembers($organization->id, 'john.doe');
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($member2->id);
});

test('can get expiring members', function () {
    $organization = Organization::factory()->create();
    $expiringMember = Member::factory()->create([
        'organization_id' => $organization->id,
        'expiry_date' => now()->addDays(15),
        'status' => 'active',
    ]);
    $activeMember = Member::factory()->create([
        'organization_id' => $organization->id,
        'expiry_date' => now()->addDays(60),
        'status' => 'active',
    ]);
    
    $service = new MembershipService();
    $expiringMembers = $service->getExpiringMembers($organization->id, 30);
    
    expect($expiringMembers)->toHaveCount(1);
    expect($expiringMembers->first()->id)->toBe($expiringMember->id);
});

test('can get member statistics', function () {
    $organization = Organization::factory()->create();
    Member::factory()->count(10)->active()->create(['organization_id' => $organization->id]);
    Member::factory()->count(3)->inactive()->create(['organization_id' => $organization->id]);
    Member::factory()->count(2)->suspended()->create(['organization_id' => $organization->id]);
    Member::factory()->count(1)->expired()->create(['organization_id' => $organization->id]);
    
    $service = new MembershipService();
    $stats = $service->getMemberStatistics($organization->id);
    
    expect($stats['total_members'])->toBe(16);
    expect($stats['active_members'])->toBe(10);
    expect($stats['inactive_members'])->toBe(3);
    expect($stats['suspended_members'])->toBe(2);
    expect($stats['expired_members'])->toBe(1);
    expect($stats['activation_rate'])->toBe(62.5);
});
