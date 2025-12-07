<?php

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Membership\MemberSubscription;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create a member with factory', function () {
    $member = Member::factory()->create();

    expect($member)->toBeInstanceOf(Member::class);
    expect($member->first_name)->not->toBeEmpty();
    expect($member->membership_number)->not->toBeEmpty();
    expect($member->barcode_number)->not->toBeEmpty();
    expect($member->status)->toBe('active');
});

test('member belongs to organization', function () {
    $organization = Organization::factory()->create();
    $member = Member::factory()->create(['organization_id' => $organization->id]);

    expect($member->organization)->toBeInstanceOf(Organization::class);
    expect($member->organization->id)->toBe($organization->id);
});

test('member can have family members', function () {
    $member = Member::factory()->create();
    $familyMembers = FamilyMember::factory()->count(3)->create(['primary_member_id' => $member->id]);

    expect($member->familyMembers)->toHaveCount(3);
    expect($member->familyMembers->first())->toBeInstanceOf(FamilyMember::class);
});

test('member can have subscriptions', function () {
    $member = Member::factory()->create();
    $subscriptions = MemberSubscription::factory()->count(2)->create(['member_id' => $member->id]);

    expect($member->subscriptions)->toHaveCount(2);
    expect($member->subscriptions->first())->toBeInstanceOf(MemberSubscription::class);
});

test('member can have fees', function () {
    $member = Member::factory()->create();
    $fees = MemberFee::factory()->count(3)->create(['member_id' => $member->id]);

    expect($member->fees)->toHaveCount(3);
    expect($member->fees->first())->toBeInstanceOf(MemberFee::class);
});

test('member full name accessor works', function () {
    $member = Member::factory()->create([
        'title' => 'Mr',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($member->full_name)->toBe('Mr John Doe');
});

test('member age calculation works', function () {
    $birthDate = now()->subYears(30);
    $member = Member::factory()->create(['date_of_birth' => $birthDate]);

    expect($member->age)->toBe(30);
});

test('member status methods work', function () {
    $activeMember = Member::factory()->create([
        'status' => 'active',
        'expiry_date' => now()->addMonth(),
    ]);

    $expiredMember = Member::factory()->create([
        'status' => 'active',
        'expiry_date' => now()->subMonth(),
    ]);

    expect($activeMember->isActive())->toBeTrue();
    expect($activeMember->isExpired())->toBeFalse();
    expect($expiredMember->isActive())->toBeFalse();
    expect($expiredMember->isExpired())->toBeTrue();
});

test('member scopes work', function () {
    $activeMember = Member::factory()->active()->create();
    $expiredMember = Member::factory()->expired()->create();
    $inactiveMember = Member::factory()->inactive()->create();

    $activeMembers = Member::active()->get();
    $expiredMembers = Member::expired()->get();
    $inactiveMembers = Member::byStatus('inactive')->get();

    expect($activeMembers)->toHaveCount(1);
    expect($expiredMembers)->toHaveCount(1);
    expect($inactiveMembers)->toHaveCount(1);
    expect($activeMembers->first()->id)->toBe($activeMember->id);
    expect($expiredMembers->first()->id)->toBe($expiredMember->id);
    expect($inactiveMembers->first()->id)->toBe($inactiveMember->id);
});

test('member search scope works', function () {
    $member1 = Member::factory()->create(['first_name' => 'John', 'last_name' => 'Smith']);
    $member2 = Member::factory()->create(['email' => 'john.doe@example.com']);
    $member3 = Member::factory()->create(['phone' => '123-456-7890']);

    $searchResults = Member::search('john')->get();

    expect($searchResults)->toHaveCount(2);
    expect($searchResults->pluck('id'))->toContain($member1->id);
    expect($searchResults->pluck('id'))->toContain($member2->id);
    expect($searchResults->pluck('id'))->not->toContain($member3->id);
});
