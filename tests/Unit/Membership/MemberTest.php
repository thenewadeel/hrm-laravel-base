<?php

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Membership\MemberSubscription;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Member Model', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
    });

    test('can create a member with required fields', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        expect($member)->toBeInstanceOf(Member::class);
        expect($member->first_name)->toBe('John');
        expect($member->last_name)->toBe('Doe');
        expect($member->email)->toBe('john@example.com');
        expect($member->organization_id)->toBe($this->organization->id);
    });

    test('generates unique membership and barcode numbers', function () {
        $members = Member::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $membershipNumbers = $members->pluck('membership_number');
        $barcodeNumbers = $members->pluck('barcode_number');

        expect($membershipNumbers)->toHaveCount(3);
        expect($barcodeNumbers)->toHaveCount(3);
        expect($membershipNumbers[0])->not->toBe($membershipNumbers[1]);
        expect($membershipNumbers[1])->not->toBe($membershipNumbers[2]);
        expect($membershipNumbers[0])->not->toBe($membershipNumbers[2]);
        expect($barcodeNumbers[0])->not->toBe($barcodeNumbers[1]);
        expect($barcodeNumbers[1])->not->toBe($barcodeNumbers[2]);
        expect($barcodeNumbers[0])->not->toBe($barcodeNumbers[2]);
    });

    test('casts dates correctly', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'date_of_birth' => '1990-01-01',
            'join_date' => '2023-01-01',
            'expiry_date' => '2024-01-01',
        ]);

        expect($member->date_of_birth)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($member->join_date)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($member->expiry_date)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($member->date_of_birth->format('Y-m-d'))->toBe('1990-01-01');
    });

    test('full name accessor concatenates correctly', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Dr',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        expect($member->full_name)->toBe('Dr John Doe');
    });

    test('full name accessor handles missing title', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => null,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        expect($member->full_name)->toBe('Jane Smith');
    });

    test('age calculation works correctly', function () {
        $birthDate = now()->subYears(30);
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'date_of_birth' => $birthDate,
        ]);

        expect($member->age)->toBe(30);
    });

    test('age returns null when date_of_birth is null', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'date_of_birth' => null,
        ]);

        expect($member->age)->toBeNull();
    });

    test('is_active works correctly', function () {
        $activeMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'expiry_date' => now()->addMonth(),
        ]);

        $expiredMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'expiry_date' => now()->subMonth(),
        ]);

        $inactiveMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'inactive',
            'expiry_date' => now()->addMonth(),
        ]);

        expect($activeMember->isActive())->toBeTrue();
        expect($expiredMember->isActive())->toBeFalse();
        expect($inactiveMember->isActive())->toBeFalse();
    });

    test('is_expired works correctly', function () {
        $expiredMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'expiry_date' => now()->subMonth(),
        ]);

        $futureMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'expiry_date' => now()->addMonth(),
        ]);

        $noExpiryMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'expiry_date' => null,
        ]);

        expect($expiredMember->isExpired())->toBeTrue();
        expect($futureMember->isExpired())->toBeFalse();
        expect($noExpiryMember->isExpired())->toBeFalse();
    });

    test('active scope works correctly', function () {
        $activeMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'expiry_date' => now()->addMonth(),
        ]);

        $expiredMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'expiry_date' => now()->subMonth(),
        ]);

        $inactiveMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'inactive',
            'expiry_date' => now()->addMonth(),
        ]);

        $activeMembers = Member::active()->get();

        expect($activeMembers)->toHaveCount(1);
        expect($activeMembers->first()->id)->toBe($activeMember->id);
    });

    test('expired scope works correctly', function () {
        $expiredMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'expiry_date' => now()->subMonth(),
        ]);

        $activeMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'expiry_date' => now()->addMonth(),
        ]);

        $expiredMembers = Member::expired()->get();

        expect($expiredMembers)->toHaveCount(1);
        expect($expiredMembers->first()->id)->toBe($expiredMember->id);
    });

    test('by_status scope works correctly', function () {
        $activeMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
        ]);

        $inactiveMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'inactive',
        ]);

        $suspendedMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'suspended',
        ]);

        $activeMembers = Member::byStatus('active')->get();
        $inactiveMembers = Member::byStatus('inactive')->get();
        $suspendedMembers = Member::byStatus('suspended')->get();

        expect($activeMembers)->toHaveCount(1);
        expect($inactiveMembers)->toHaveCount(1);
        expect($suspendedMembers)->toHaveCount(1);
        expect($activeMembers->first()->id)->toBe($activeMember->id);
        expect($inactiveMembers->first()->id)->toBe($inactiveMember->id);
        expect($suspendedMembers->first()->id)->toBe($suspendedMember->id);
    });

    test('search scope works across multiple fields', function () {
        $member1 = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
        ]);

        $member2 = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'email' => 'john.doe@example.com',
        ]);

        $member3 = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'phone' => '123-456-7890',
        ]);

        $member4 = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'membership_number' => 'MEM-123456',
        ]);

        $searchResults = Member::search('john')->get();

        expect($searchResults)->toHaveCount(2);
        expect($searchResults->pluck('id'))->toContain($member1->id);
        expect($searchResults->pluck('id'))->toContain($member2->id);
        expect($searchResults->pluck('id'))->not->toContain($member3->id);
        expect($searchResults->pluck('id'))->not->toContain($member4->id);
    });

    test('relationships work correctly', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        // Test family members relationship
        $familyMembers = FamilyMember::factory()->count(2)->create([
            'primary_member_id' => $member->id,
        ]);

        expect($member->familyMembers)->toHaveCount(2);
        expect($member->familyMembers->first())->toBeInstanceOf(FamilyMember::class);

        // Test subscriptions relationship
        $subscriptions = MemberSubscription::factory()->count(2)->create([
            'member_id' => $member->id,
            'status' => 'expired',
        ]);

        expect($member->subscriptions)->toHaveCount(2);
        expect($member->subscriptions->first())->toBeInstanceOf(MemberSubscription::class);

        // Test active subscription relationship
        $activeSubscription = MemberSubscription::factory()->create([
            'member_id' => $member->id,
            'status' => 'active',
        ]);

        expect($member->activeSubscription)->toHaveCount(1);
        expect($member->activeSubscription->first()->id)->toBe($activeSubscription->id);

        // Test fees relationship
        $fees = MemberFee::factory()->count(3)->create([
            'member_id' => $member->id,
            'status' => 'paid',
        ]);

        expect($member->fees)->toHaveCount(3);
        expect($member->fees->first())->toBeInstanceOf(MemberFee::class);

        // Test unpaid fees relationship
        $unpaidFee = MemberFee::factory()->create([
            'member_id' => $member->id,
            'status' => 'pending',
        ]);

        expect($member->unpaidFees)->toHaveCount(1);
        expect($member->unpaidFees->first()->id)->toBe($unpaidFee->id);
    });

    test('organization relationship works correctly', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        expect($member->organization)->toBeInstanceOf(Organization::class);
        expect($member->organization->id)->toBe($this->organization->id);
    });

    test('soft deletes work correctly', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $member->delete();

        expect($member->trashed())->toBeTrue();
        expect(Member::find($member->id))->toBeNull();
        expect(Member::withTrashed()->find($member->id))->not->toBeNull();
    });

    test('fillable attributes are correct', function () {
        $member = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Test St',
            'notes' => 'Test notes',
        ]);

        expect($member->first_name)->toBe('Test');
        expect($member->last_name)->toBe('User');
        expect($member->email)->toBe('test@example.com');
        expect($member->phone)->toBe('123-456-7890');
        expect($member->address)->toBe('123 Test St');
        expect($member->notes)->toBe('Test notes');
    });

    test('mass assignment protection works', function () {
        $member = new Member;

        expect($member->getFillable())->toContain('first_name');
        expect($member->getFillable())->toContain('last_name');
        expect($member->getFillable())->toContain('email');
        expect($member->getFillable())->toContain('organization_id');
        expect($member->getFillable())->not->toContain('id');
        expect($member->getFillable())->not->toContain('created_at');
        expect($member->getFillable())->not->toContain('updated_at');
    });
});
