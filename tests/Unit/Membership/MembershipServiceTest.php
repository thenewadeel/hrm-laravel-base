<?php

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Organization;
use App\Services\Membership\MembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('MembershipService', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
        $this->service = new MembershipService;
    });

    describe('createMember', function () {
        test('creates member with auto-generated numbers', function () {
            $memberData = [
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'status' => 'active',
                'join_date' => now()->format('Y-m-d'),
            ];

            $member = $this->service->createMember($memberData);

            expect($member)->toBeInstanceOf(Member::class);
            expect($member->first_name)->toBe('John');
            expect($member->last_name)->toBe('Doe');
            expect($member->email)->toBe('john@example.com');
            expect($member->membership_number)->not->toBeEmpty();
            expect($member->barcode_number)->not->toBeEmpty();
            expect($member->membership_number)->toStartWith('MEM-');
            expect($member->barcode_number)->toStartWith('MBR-');
        });

        test('generates unique membership numbers', function () {
            $memberData = [
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'join_date' => now()->format('Y-m-d'),
            ];

            $member1 = $this->service->createMember($memberData);
            $member2 = $this->service->createMember(array_merge($memberData, [
                'email' => 'jane@example.com',
                'first_name' => 'Jane',
            ]));

            expect($member1->membership_number)->not->toBe($member2->membership_number);
            expect($member1->barcode_number)->not->toBe($member2->barcode_number);
        });

        test('wraps creation in database transaction', function () {
            DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
                return $callback();
            });

            $memberData = [
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'join_date' => now()->format('Y-m-d'),
            ];

            $member = $this->service->createMember($memberData);

            expect($member)->toBeInstanceOf(Member::class);
        });
    });

    describe('updateMember', function () {
        test('updates existing member', function () {
            $member = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]);

            $updateData = [
                'first_name' => 'Jane',
                'email' => 'jane@example.com',
            ];

            $updatedMember = $this->service->updateMember($member, $updateData);

            expect($updatedMember->first_name)->toBe('Jane');
            expect($updatedMember->last_name)->toBe('Doe'); // Unchanged
            expect($updatedMember->email)->toBe('jane@example.com');
        });

        test('wraps update in database transaction', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);

            DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
                return $callback();
            });

            $updatedMember = $this->service->updateMember($member, ['first_name' => 'Jane']);

            expect($updatedMember)->toBeInstanceOf(Member::class);
        });
    });

    describe('addFamilyMember', function () {
        test('adds family member with auto-generated barcode', function () {
            $primaryMember = Member::factory()->create(['organization_id' => $this->organization->id]);

            $familyData = [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'relationship' => 'spouse',
            ];

            $familyMember = $this->service->addFamilyMember($primaryMember, $familyData);

            expect($familyMember)->toBeInstanceOf(FamilyMember::class);
            expect($familyMember->first_name)->toBe('Jane');
            expect($familyMember->last_name)->toBe('Doe');
            expect($familyMember->relationship)->toBe('spouse');
            expect($familyMember->primary_member_id)->toBe($primaryMember->id);
            expect($familyMember->organization_id)->toBe($this->organization->id);
            expect($familyMember->barcode_number)->not->toBeEmpty();
            expect($familyMember->barcode_number)->toStartWith('FAM-');
        });

        test('generates unique family barcode numbers', function () {
            $primaryMember = Member::factory()->create(['organization_id' => $this->organization->id]);

            $familyData = [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'relationship' => 'spouse',
            ];

            $familyMember1 = $this->service->addFamilyMember($primaryMember, $familyData);
            $familyMember2 = $this->service->addFamilyMember($primaryMember, array_merge($familyData, [
                'first_name' => 'Johnny',
                'relationship' => 'child',
            ]));

            expect($familyMember1->barcode_number)->not->toBe($familyMember2->barcode_number);
        });

        test('wraps family member creation in database transaction', function () {
            $primaryMember = Member::factory()->create(['organization_id' => $this->organization->id]);

            DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
                return $callback();
            });

            $familyMember = $this->service->addFamilyMember($primaryMember, [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'relationship' => 'spouse',
            ]);

            expect($familyMember)->toBeInstanceOf(FamilyMember::class);
        });
    });

    describe('deactivateMember', function () {
        test('deactivates member and all family members', function () {
            $member = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
            ]);

            $familyMembers = FamilyMember::factory()->count(2)->create([
                'organization_id' => $this->organization->id,
                'primary_member_id' => $member->id,
                'status' => 'active',
            ]);

            $result = $this->service->deactivateMember($member);

            expect($result)->toBeTrue();
            expect($member->fresh()->status)->toBe('inactive');

            foreach ($familyMembers as $familyMember) {
                expect($familyMember->fresh()->status)->toBe('inactive');
            }
        });

        test('wraps deactivation in database transaction', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);

            DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
                return $callback();
            });

            $result = $this->service->deactivateMember($member);

            expect($result)->toBeTrue();
        });
    });

    describe('suspendMember', function () {
        test('suspends member and family members with reason', function () {
            $member = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
                'notes' => 'Original notes',
            ]);

            $familyMembers = FamilyMember::factory()->count(2)->create([
                'organization_id' => $this->organization->id,
                'primary_member_id' => $member->id,
                'status' => 'active',
            ]);

            $reason = 'Non-payment of fees';
            $result = $this->service->suspendMember($member, $reason);

            expect($result)->toBeTrue();
            expect($member->fresh()->status)->toBe('suspended');
            expect($member->fresh()->notes)->toContain('Suspended: Non-payment of fees');

            foreach ($familyMembers as $familyMember) {
                expect($familyMember->fresh()->status)->toBe('suspended');
            }
        });

        test('suspends member without reason', function () {
            $member = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
            ]);

            $result = $this->service->suspendMember($member);

            expect($result)->toBeTrue();
            expect($member->fresh()->status)->toBe('suspended');
            expect($member->fresh()->notes)->toContain('Suspended: No reason provided');
        });

        test('wraps suspension in database transaction', function () {
            $member = Member::factory()->create(['organization_id' => $this->organization->id]);

            DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
                return $callback();
            });

            $result = $this->service->suspendMember($member);

            expect($result)->toBeTrue();
        });
    });

    describe('reactivateMember', function () {
        test('reactivates suspended member and family members', function () {
            $member = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'suspended',
                'expiry_date' => now()->addMonth(),
            ]);

            $familyMembers = FamilyMember::factory()->count(2)->create([
                'organization_id' => $this->organization->id,
                'primary_member_id' => $member->id,
                'status' => 'suspended',
            ]);

            $result = $this->service->reactivateMember($member);

            expect($result)->toBeTrue();
            expect($member->fresh()->status)->toBe('active');

            foreach ($familyMembers as $familyMember) {
                expect($familyMember->fresh()->status)->toBe('active');
            }
        });

        test('throws exception for expired member', function () {
            $member = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'suspended',
                'expiry_date' => now()->subMonth(),
            ]);

            expect(fn () => $this->service->reactivateMember($member))
                ->toThrow(\InvalidArgumentException::class, 'Cannot reactivate expired member');
        });

        test('wraps reactivation in database transaction', function () {
            $member = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'suspended',
                'expiry_date' => now()->addMonth(),
            ]);

            DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
                return $callback();
            });

            $result = $this->service->reactivateMember($member);

            expect($result)->toBeTrue();
        });
    });

    describe('generateMembershipNumber', function () {
        test('generates membership number with correct format', function () {
            $membershipNumber = $this->service->generateMembershipNumber($this->organization->id);

            expect($membershipNumber)->toStartWith('MEM-');
            expect($membershipNumber)->toMatch('/MEM-\d{4}-\d{5}/');
        });

        test('generates unique membership numbers', function () {
            $number1 = $this->service->generateMembershipNumber($this->organization->id);
            $number2 = $this->service->generateMembershipNumber($this->organization->id);

            expect($number1)->not->toBe($number2);
        });

        test('generates different numbers for different organizations', function () {
            $org2 = Organization::factory()->create();

            $number1 = $this->service->generateMembershipNumber($this->organization->id);
            $number2 = $this->service->generateMembershipNumber($org2->id);

            // Numbers should be different due to random generation
            expect($number1)->not->toBe($number2);
        });
    });

    describe('generateBarcodeNumber', function () {
        test('generates barcode number with correct format', function () {
            $barcodeNumber = $this->service->generateBarcodeNumber($this->organization->id);

            expect($barcodeNumber)->toStartWith('MBR-');
            expect($barcodeNumber)->toMatch('/MBR-\d+-\d{6}/');
        });

        test('generates unique barcode numbers', function () {
            $number1 = $this->service->generateBarcodeNumber($this->organization->id);
            $number2 = $this->service->generateBarcodeNumber($this->organization->id);

            expect($number1)->not->toBe($number2);
        });
    });

    describe('generateFamilyBarcodeNumber', function () {
        test('generates family barcode number with correct format', function () {
            $barcodeNumber = $this->service->generateFamilyBarcodeNumber($this->organization->id);

            expect($barcodeNumber)->toStartWith('FAM-');
            expect($barcodeNumber)->toMatch('/FAM-\d+-\d{6}/');
        });

        test('generates unique family barcode numbers', function () {
            $number1 = $this->service->generateFamilyBarcodeNumber($this->organization->id);
            $number2 = $this->service->generateFamilyBarcodeNumber($this->organization->id);

            expect($number1)->not->toBe($number2);
        });
    });

    describe('searchMembers', function () {
        beforeEach(function () {
            $this->member1 = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Smith',
            ]);

            $this->member2 = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'status' => 'inactive',
            ]);

            $this->member3 = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
            ]);
        });

        test('searches members by name', function () {
            $results = $this->service->searchMembers($this->organization->id, 'john');

            expect($results)->toHaveCount(2); // Both John Smith and John Doe
            expect($results->pluck('id'))->toContain($this->member1->id);
        });

        test('applies status filter', function () {
            $results = $this->service->searchMembers($this->organization->id, 'jane', [
                'status' => 'inactive',
            ]);

            expect($results)->toHaveCount(1);
            expect($results->first()->id)->toBe($this->member2->id);
        });

        test('applies family filter', function () {
            // Add family members to member1
            FamilyMember::factory()->create(['primary_member_id' => $this->member1->id]);

            $results = $this->service->searchMembers($this->organization->id, '', [
                'has_family' => true,
            ]);

            expect($results)->toHaveCount(1);
            expect($results->first()->id)->toBe($this->member1->id);
        });

        test('applies subscription status filter', function () {
            // This would require creating subscriptions, simplified for now
            $results = $this->service->searchMembers($this->organization->id, '', [
                'subscription_status' => 'active',
            ]);

            expect($results)->toHaveCount(0); // No active subscriptions in test data
        });
    });

    describe('getExpiringMembers', function () {
        test('gets members expiring within specified days', function () {
            $expiringMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->addDays(15),
                'status' => 'active',
            ]);

            $nonExpiringMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->addDays(45),
                'status' => 'active',
            ]);

            $expiredMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->subDays(15),
                'status' => 'active',
            ]);

            $expiringMembers = $this->service->getExpiringMembers($this->organization->id, 30);

            expect($expiringMembers)->toHaveCount(1);
            expect($expiringMembers->first()->id)->toBe($expiringMember->id);
        });

        test('uses default 30 days when not specified', function () {
            $expiringMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->addDays(25),
                'status' => 'active',
            ]);

            $expiringMembers = $this->service->getExpiringMembers($this->organization->id);

            expect($expiringMembers)->toHaveCount(1);
            expect($expiringMembers->first()->id)->toBe($expiringMember->id);
        });
    });

    describe('getExpiredMembers', function () {
        test('gets expired members', function () {
            $expiredMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->subDays(15),
                'status' => 'active',
            ]);

            $activeMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->addDays(15),
                'status' => 'active',
            ]);

            $expiredMembers = $this->service->getExpiredMembers($this->organization->id);

            expect($expiredMembers)->toHaveCount(1);
            expect($expiredMembers->first()->id)->toBe($expiredMember->id);
        });
    });

    describe('updateExpiredMemberStatus', function () {
        test('updates status of expired members', function () {
            $expiredMember1 = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->subDays(15),
                'status' => 'active',
            ]);

            $expiredMember2 = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->subDays(5),
                'status' => 'active',
            ]);

            $activeMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'expiry_date' => now()->addDays(15),
                'status' => 'active',
            ]);

            $updatedCount = $this->service->updateExpiredMemberStatus($this->organization->id);

            expect($updatedCount)->toBe(2);
            expect($expiredMember1->fresh()->status)->toBe('expired');
            expect($expiredMember2->fresh()->status)->toBe('expired');
            expect($activeMember->fresh()->status)->toBe('active');
        });
    });

    describe('getMemberStatistics', function () {
        test('returns correct member statistics', function () {
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

            $expiredMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
                'expiry_date' => now()->subDays(15),
            ]);

            $expiringMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
                'expiry_date' => now()->addDays(15),
            ]);

            $memberWithFamily = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
            ]);

            FamilyMember::factory()->create(['primary_member_id' => $memberWithFamily->id]);

            $stats = $this->service->getMemberStatistics($this->organization->id);

            expect($stats['total_members'])->toBe(6);
            expect($stats['active_members'])->toBe(3); // activeMember, expiringMember, memberWithFamily
            expect($stats['inactive_members'])->toBe(1);
            expect($stats['suspended_members'])->toBe(1);
            expect($stats['expired_members'])->toBe(1);
            expect($stats['expiring_next_30_days'])->toBe(1);
            expect($stats['members_with_family'])->toBe(1);
            expect($stats['activation_rate'])->toBe(50.0); // 3/6 * 100
        });

        test('handles zero members correctly', function () {
            $stats = $this->service->getMemberStatistics($this->organization->id);

            expect($stats['total_members'])->toBe(0);
            expect($stats['active_members'])->toBe(0);
            expect($stats['activation_rate'])->toBe(0);
        });
    });
});
