<?php

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('FamilyMember Model', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
        $this->primaryMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'expiry_date' => now()->addYear(),
        ]);
    });

    test('can create a family member with required fields', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'relationship' => 'spouse',
        ]);

        expect($familyMember)->toBeInstanceOf(FamilyMember::class);
        expect($familyMember->first_name)->toBe('Jane');
        expect($familyMember->last_name)->toBe('Doe');
        expect($familyMember->relationship)->toBe('spouse');
        expect($familyMember->primary_member_id)->toBe($this->primaryMember->id);
        expect($familyMember->organization_id)->toBe($this->organization->id);
    });

    test('casts dates correctly', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'date_of_birth' => '1995-05-15',
        ]);

        expect($familyMember->date_of_birth)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($familyMember->date_of_birth->format('Y-m-d'))->toBe('1995-05-15');
    });

    test('full name accessor concatenates correctly', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'title' => 'Mrs',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        expect($familyMember->full_name)->toBe('Mrs Jane Doe');
    });

    test('full name accessor handles missing title', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'title' => null,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        expect($familyMember->full_name)->toBe('Jane Doe');
    });

    test('age calculation works correctly', function () {
        $birthDate = now()->subYears(25);
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'date_of_birth' => $birthDate,
        ]);

        expect($familyMember->age)->toBe(25);
    });

    test('age returns null when date_of_birth is null', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'date_of_birth' => null,
        ]);

        expect($familyMember->age)->toBeNull();
    });

    test('is_active works correctly with active primary member', function () {
        $activeFamilyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'status' => 'active',
        ]);

        expect($activeFamilyMember->isActive())->toBeTrue();
    });

    test('is_active works correctly with inactive family member', function () {
        $inactiveFamilyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'status' => 'inactive',
        ]);

        expect($inactiveFamilyMember->isActive())->toBeFalse();
    });

    test('is_active works correctly with inactive primary member', function () {
        $inactivePrimaryMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'inactive',
        ]);

        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $inactivePrimaryMember->id,
            'status' => 'active',
        ]);

        expect($familyMember->isActive())->toBeFalse();
    });

    test('is_active works correctly with expired primary member', function () {
        $expiredPrimaryMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'expiry_date' => now()->subMonth(),
        ]);

        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $expiredPrimaryMember->id,
            'status' => 'active',
        ]);

        expect($familyMember->isActive())->toBeFalse();
    });

    test('active scope works correctly', function () {
        $activeFamilyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'status' => 'active',
        ]);

        $inactiveFamilyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'status' => 'inactive',
        ]);

        $activeFamilyMembers = FamilyMember::active()->get();

        expect($activeFamilyMembers)->toHaveCount(1);
        expect($activeFamilyMembers->first()->id)->toBe($activeFamilyMember->id);
    });

    test('active scope excludes family members of inactive primary members', function () {
        $inactivePrimaryMember = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'inactive',
        ]);

        $familyMemberOfInactive = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $inactivePrimaryMember->id,
            'status' => 'active',
        ]);

        $familyMemberOfActive = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'status' => 'active',
        ]);

        $activeFamilyMembers = FamilyMember::active()->get();

        expect($activeFamilyMembers)->toHaveCount(1);
        expect($activeFamilyMembers->first()->id)->toBe($familyMemberOfActive->id);
    });

    test('by_relationship scope works correctly', function () {
        $spouse = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'relationship' => 'spouse',
        ]);

        $child = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'relationship' => 'child',
        ]);

        $spouses = FamilyMember::byRelationship('spouse')->get();
        $children = FamilyMember::byRelationship('child')->get();

        expect($spouses)->toHaveCount(1);
        expect($children)->toHaveCount(1);
        expect($spouses->first()->id)->toBe($spouse->id);
        expect($children->first()->id)->toBe($child->id);
    });

    test('search scope works across multiple fields', function () {
        $familyMember1 = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $familyMember2 = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'relationship' => 'spouse',
        ]);

        $familyMember3 = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'barcode_number' => 'FAM-123456',
        ]);

        $searchResults = FamilyMember::search('jane')->get();

        expect($searchResults)->toHaveCount(1);
        expect($searchResults->pluck('id'))->toContain($familyMember1->id);
        expect($searchResults->pluck('id'))->not->toContain($familyMember2->id);
        expect($searchResults->pluck('id'))->not->toContain($familyMember3->id);
    });

    test('primary_member relationship works correctly', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
        ]);

        expect($familyMember->primaryMember)->toBeInstanceOf(Member::class);
        expect($familyMember->primaryMember->id)->toBe($this->primaryMember->id);
    });

    test('organization relationship works correctly', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
        ]);

        expect($familyMember->organization)->toBeInstanceOf(Organization::class);
        expect($familyMember->organization->id)->toBe($this->organization->id);
    });

    test('soft deletes work correctly', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
        ]);

        $familyMember->delete();

        expect($familyMember->trashed())->toBeTrue();
        expect(FamilyMember::find($familyMember->id))->toBeNull();
        expect(FamilyMember::withTrashed()->find($familyMember->id))->not->toBeNull();
    });

    test('fillable attributes are correct', function () {
        $familyMember = FamilyMember::factory()->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
            'relationship' => 'child',
            'title' => 'Master',
            'first_name' => 'Johnny',
            'last_name' => 'Doe',
            'gender' => 'male',
            'status' => 'active',
            'notes' => 'Test notes',
        ]);

        expect($familyMember->relationship)->toBe('child');
        expect($familyMember->title)->toBe('Master');
        expect($familyMember->first_name)->toBe('Johnny');
        expect($familyMember->last_name)->toBe('Doe');
        expect($familyMember->gender)->toBe('male');
        expect($familyMember->status)->toBe('active');
        expect($familyMember->notes)->toBe('Test notes');
    });

    test('mass assignment protection works', function () {
        $familyMember = new FamilyMember;

        expect($familyMember->getFillable())->toContain('primary_member_id');
        expect($familyMember->getFillable())->toContain('first_name');
        expect($familyMember->getFillable())->toContain('last_name');
        expect($familyMember->getFillable())->toContain('organization_id');
        expect($familyMember->getFillable())->not->toContain('id');
        expect($familyMember->getFillable())->not->toContain('created_at');
        expect($familyMember->getFillable())->not->toContain('updated_at');
    });

    test('generates unique barcode numbers', function () {
        $familyMembers = FamilyMember::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'primary_member_id' => $this->primaryMember->id,
        ]);

        $barcodeNumbers = $familyMembers->pluck('barcode_number');

        expect($barcodeNumbers)->toHaveCount(3);
        expect($barcodeNumbers)->toHaveCount(3);
        expect($barcodeNumbers[0])->not->toBe($barcodeNumbers[1]);
        expect($barcodeNumbers[1])->not->toBe($barcodeNumbers[2]);
        expect($barcodeNumbers[0])->not->toBe($barcodeNumbers[2]);
    });

    test('validates relationship values', function () {
        $validRelationships = ['spouse', 'child', 'parent', 'sibling', 'other'];

        foreach ($validRelationships as $relationship) {
            $familyMember = FamilyMember::factory()->create([
                'organization_id' => $this->organization->id,
                'primary_member_id' => $this->primaryMember->id,
                'relationship' => $relationship,
            ]);

            expect($familyMember->relationship)->toBe($relationship);
        }
    });

    test('validates gender values', function () {
        $validGenders = ['male', 'female', 'other'];

        foreach ($validGenders as $gender) {
            $familyMember = FamilyMember::factory()->create([
                'organization_id' => $this->organization->id,
                'primary_member_id' => $this->primaryMember->id,
                'gender' => $gender,
            ]);

            expect($familyMember->gender)->toBe($gender);
        }
    });

    test('validates status values', function () {
        $validStatuses = ['active', 'inactive', 'suspended'];

        foreach ($validStatuses as $status) {
            $familyMember = FamilyMember::factory()->create([
                'organization_id' => $this->organization->id,
                'primary_member_id' => $this->primaryMember->id,
                'status' => $status,
            ]);

            expect($familyMember->status)->toBe($status);
        }
    });
});
