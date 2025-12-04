<?php

use App\Models\Membership\Member;
use App\Models\Organization;
use App\Services\Membership\MembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Membership Service Integration', function () {
    beforeEach(function () {
        $this->organization = Organization::factory()->create();
        $this->service = new MembershipService;
    });

    describe('Member Management', function () {
        test('creates member with auto-generated numbers', function () {
            $memberData = [
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'status' => 'active',
                'join_date' => now(),
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
                'join_date' => now(),
            ];

            $member1 = $this->service->createMember($memberData);
            $member2 = $this->service->createMember(array_merge($memberData, [
                'email' => 'jane@example.com',
                'first_name' => 'Jane',
            ]));

            expect($member1->membership_number)->not->toBe($member2->membership_number);
            expect($member1->barcode_number)->not->toBe($member2->barcode_number);
        });

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
    });

    describe('Member Statistics', function () {
        test('returns correct member statistics', function () {
            $activeMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
            ]);

            $inactiveMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'inactive',
            ]);

            $expiredMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => 'active',
                'expiry_date' => now()->subDays(15),
            ]);

            $stats = $this->service->getMemberStatistics($this->organization->id);

            expect($stats['total_members'])->toBe(3);
            expect($stats['active_members'])->toBe(1); // Only activeMember
            expect($stats['inactive_members'])->toBe(1);
            expect($stats['expired_members'])->toBe(1);
            expect($stats['activation_rate'])->toBe(33.33); // 1/3 * 100
        });

        test('handles zero members correctly', function () {
            $stats = $this->service->getMemberStatistics($this->organization->id);

            expect($stats['total_members'])->toBe(0);
            expect($stats['active_members'])->toBe(0);
            expect($stats['activation_rate'])->toBe(0);
        });
    });

    describe('Member Search', function () {
        test('searches members by name', function () {
            $member1 = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Smith',
            ]);

            $member2 = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ]);

            $results = $this->service->searchMembers($this->organization->id, 'john');

            expect($results)->toHaveCount(1);
            expect($results->first()->id)->toBe($member1->id);
        });

        test('applies status filter', function () {
            $activeMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'John',
                'last_name' => 'Smith',
                'status' => 'active',
            ]);

            $inactiveMember = Member::factory()->create([
                'organization_id' => $this->organization->id,
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'status' => 'inactive',
            ]);

            $results = $this->service->searchMembers($this->organization->id, 'jane', [
                'status' => 'inactive',
            ]);

            expect($results)->toHaveCount(1);
            expect($results->first()->id)->toBe($inactiveMember->id);
        });
    });

    describe('Expiring Members', function () {
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
});
