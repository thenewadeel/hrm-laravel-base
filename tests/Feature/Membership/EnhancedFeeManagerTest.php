<?php

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use Livewire\Livewire;

it('renders enhanced fee manager successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => ['admin']]);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization);
    $user->update(['current_organization_id' => $organization->id]);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\EnhancedFeeManager::class)
        ->assertStatus(200)
        ->assertSee('Total Revenue')
        ->assertSee('Collection Rate');
});

it('displays fee statistics correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => ['admin']]);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization);
    $user->update(['current_organization_id' => $organization->id]);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    // Create fees with different statuses
    MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'paid',
        'amount' => 100,
        'paid_amount' => 100,
    ]);

    MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'pending',
        'amount' => 50,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\EnhancedFeeManager::class)
        ->assertSee('100.00') // Paid amount
        ->assertSee('50.00')  // Pending amount
        ->assertSee('66.7%'); // Collection rate (100/150 * 100)
});

it('can create enhanced fee with recurring option', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => ['admin']]);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization);
    $user->update(['current_organization_id' => $organization->id]);

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\EnhancedFeeManager::class)
        ->set('member_id', $member->id)
        ->set('fee_type', 'subscription')
        ->set('description', 'Monthly Membership')
        ->set('amount', 75)
        ->set('due_date', now()->addDays(30)->format('Y-m-d'))
        ->set('recurring', true)
        ->set('recurring_frequency', 'monthly')
        ->set('recurring_end_date', now()->addMonths(3)->format('Y-m-d'))
        ->call('createFee')
        ->assertDispatched('fee-created')
        ->assertDispatched('show-notification', message: 'Fee created successfully', type: 'success');

    $this->assertDatabaseHas('member_fees', [
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'description' => 'Monthly Membership',
        'amount' => 75,
        'fee_type' => 'subscription',
    ]);
});

it('can process payment with receipt generation', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => ['admin']]);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::PROCESS_PAYMENTS, $organization);
    $user->update(['current_organization_id' => $organization->id]);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'pending',
        'amount' => 100,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\EnhancedFeeManager::class)
        ->set('selectedFeeId', $fee->id)
        ->set('payment_amount', 100)
        ->set('payment_method', 'credit_card')
        ->set('payment_reference', 'TEST-123')
        ->set('send_receipt', true)
        ->call('processPayment')
        ->assertDispatched('payment-processed')
        ->assertDispatched('show-notification', message: 'Payment processed successfully', type: 'success');

    $fee->refresh();
    expect($fee->status)->toBe('paid');
    expect((float) $fee->paid_amount)->toBe(100.0);
    expect($fee->payment_method)->toBe('credit_card');
});

it('can generate invoice for fee', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => ['admin']]);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization);
    $user->update(['current_organization_id' => $organization->id]);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $fee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\EnhancedFeeManager::class)
        ->call('generateInvoice', $fee)
        ->assertSet('showInvoiceModal', true)
        ->assertSet('selectedFee.id', $fee->id)
        ->assertSee('Invoice #INV-');
});

it('can toggle analytics dashboard', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => ['admin']]);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization);
    $user->update(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\EnhancedFeeManager::class)
        ->assertSet('showAnalytics', false)
        ->call('toggleAnalytics')
        ->assertSet('showAnalytics', true)
        ->assertSee('Revenue Analytics')
        ->assertSee('Monthly Revenue Trend');
});

it('respects organization isolation', function () {
    $user = User::factory()->create();
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();

    $user->organizations()->attach($organization1->id, ['roles' => ['admin']]);
    $user->givePermissionTo(MembershipPermissions::MANAGE_FEES, $organization1);
    $user->givePermissionTo(MembershipPermissions::VIEW_FEES, $organization1);
    $user->update(['current_organization_id' => $organization1->id]);

    $member1 = Member::factory()->create(['organization_id' => $organization1->id]);
    $member2 = Member::factory()->create(['organization_id' => $organization2->id]);

    $fee1 = MemberFee::factory()->create([
        'organization_id' => $organization1->id,
        'member_id' => $member1->id,
        'description' => 'Organization 1 Fee',
    ]);

    $fee2 = MemberFee::factory()->create([
        'organization_id' => $organization2->id,
        'member_id' => $member2->id,
        'description' => 'Organization 2 Fee',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\EnhancedFeeManager::class)
        ->assertSee('Organization 1 Fee')
        ->assertDontSee('Organization 2 Fee');
});
