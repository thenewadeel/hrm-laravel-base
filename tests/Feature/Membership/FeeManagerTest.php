<?php

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use Livewire\Livewire;

test('fee manager renders successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization);

    $this->actingAs($user)
        ->get('/fees')
        ->assertStatus(200);
});

test('fee manager displays fees correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $fees = MemberFee::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\FeeManager::class)
        ->assertSee($fees->first()->description)
        ->assertSee($fees->first()->member->full_name);
});

test('fee manager can create fee', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\FeeManager::class, ['member' => $member])
        ->set('showCreateForm', true)
        ->set('fee_type', 'subscription')
        ->set('description', 'Test Fee')
        ->set('amount', 100.00)
        ->set('due_date', now()->addDays(30)->format('Y-m-d'));

    // Check for validation errors
    $component->assertHasNoErrors();

    $component->call('createFee');

    // Check that fee was actually created
    $this->assertDatabaseHas('member_fees', [
        'description' => 'Test Fee',
        'amount' => 100.00,
        'member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);

    $this->assertDatabaseHas('member_fees', [
        'description' => 'Test Fee',
        'amount' => 100.00,
        'member_id' => $member->id,
        'organization_id' => $organization->id,
    ]);
});

test('fee manager can process payment', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'amount' => 100.00,
        'paid_amount' => 0.00,
        'status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\FeeManager::class)
        ->call('showPaymentForm', $fee->id)
        ->assertSet('selectedFeeId', $fee->id)
        ->assertSet('payment_amount', 100.00)
        ->set('payment_method', 'cash')
        ->set('payment_reference', 'PAY-001')
        ->call('processPayment');

    $this->assertDatabaseHas('member_fees', [
        'id' => $fee->id,
        'paid_amount' => 100.00,
        'status' => 'paid',
    ]);

    $this->assertDatabaseHas('member_fees', [
        'id' => $fee->id,
        'paid_amount' => 100.00,
        'status' => 'paid',
    ]);
});

test('fee manager can waive fee', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\FeeManager::class)
        ->call('waiveFee', $fee->id, 'Test waiver');

    $this->assertDatabaseHas('member_fees', [
        'id' => $fee->id,
        'status' => 'waived',
    ]);

    $this->assertDatabaseHas('member_fees', [
        'id' => $fee->id,
        'status' => 'waived',
    ]);
});

test('fee manager search functionality works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->current_organization_id = $organization->id;

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

    MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member1->id,
        'description' => 'John\'s Fee',
    ]);

    MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member2->id,
        'description' => 'Jane\'s Fee',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\FeeManager::class)
        ->set('search', 'John')
        ->assertSee('John\'s Fee')
        ->assertDontSee('Jane\'s Fee');
});

test('fee manager status filter works', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->current_organization_id = $organization->id;

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $pendingFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'pending',
    ]);

    $paidFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'paid',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\FeeManager::class)
        ->set('status', 'pending')
        ->assertSee($pendingFee->description)
        ->assertDontSee($paidFee->description);
});

test('fee manager respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $user->organizations()->attach($organization1->id, ['roles' => 'admin']);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization1);
    $user->current_organization_id = $organization1->id;

    $member1 = Member::factory()->create(['organization_id' => $organization1->id]);
    $member2 = Member::factory()->create(['organization_id' => $organization2->id]);

    MemberFee::factory()->create([
        'organization_id' => $organization1->id,
        'member_id' => $member1->id,
        'description' => 'Organization 1 Fee',
    ]);

    MemberFee::factory()->create([
        'organization_id' => $organization2->id,
        'member_id' => $member2->id,
        'description' => 'Organization 2 Fee',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\FeeManager::class)
        ->assertSee('Organization 1 Fee')
        ->assertDontSee('Organization 2 Fee');
});
