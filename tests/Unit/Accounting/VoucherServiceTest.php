<?php

use App\Models\Accounting\Voucher;
use App\Models\Organization;
use App\Models\User;
use App\Services\GeneralVoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('voucher service creates voucher with required data', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $data = [
        'type' => 'sales',
        'date' => now()->format('Y-m-d'),
        'amount' => 1000.00,
        'description' => 'Test sales voucher',
        'notes' => 'Test notes',
    ];

    $voucher = app(GeneralVoucherService::class)->createVoucher($data, $organization->id, $user->id);

    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->organization_id)->toBe($organization->id);
    expect($voucher->type)->toBe('sales');
    expect($voucher->amount)->toBe(1000.0);
    expect($voucher->description)->toBe('Test sales voucher');
    expect($voucher->notes)->toBe('Test notes');
    expect($voucher->status)->toBe('draft');
    expect($voucher->created_by)->toBe($user->id);
});

test('voucher service generates sequential voucher numbers', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $data = [
        'type' => 'sales',
        'date' => now()->format('Y-m-d'),
        'amount' => 1000.00,
        'description' => 'Test sales voucher',
    ];

    $voucher1 = app(GeneralVoucherService::class)->createVoucher($data, $organization->id, $user->id);
    $voucher2 = app(GeneralVoucherService::class)->createVoucher($data, $organization->id, $user->id);

    expect($voucher1->number)->toBe('SALES-2025-0001');
    expect($voucher2->number)->toBe('SALES-2025-0002');
});

test('voucher service validates amount is positive', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $data = [
        'type' => 'sales',
        'date' => now()->format('Y-m-d'),
        'amount' => -100,
        'description' => 'Invalid voucher',
    ];

    expect(fn () => app(GeneralVoucherService::class)->createVoucher($data, $organization->id, $user->id))
        ->toThrow('Amount must be positive');
});

test('voucher service validates voucher type', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $data = [
        'type' => 'invalid_type',
        'date' => now()->format('Y-m-d'),
        'amount' => 1000.00,
        'description' => 'Invalid voucher',
    ];

    expect(fn () => app(GeneralVoucherService::class)->createVoucher($data, $organization->id, $user->id))
        ->toThrow('Invalid voucher type');
});

test('voucher service can post voucher', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $data = [
        'type' => 'sales',
        'date' => now()->format('Y-m-d'),
        'amount' => 1000.00,
        'description' => 'Test sales voucher',
    ];

    $voucher = app(GeneralVoucherService::class)->createVoucher($data, $organization->id, $user->id);

    $postedVoucher = app(GeneralVoucherService::class)->postVoucher($voucher->id, $user->id);

    expect($postedVoucher->status)->toBe('posted');
    expect($postedVoucher->updated_by)->toBe($user->id);
});

test('voucher service updates voucher', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $voucher = Voucher::factory()->create([
        'organization_id' => $organization->id,
        'created_by' => $user->id,
    ]);

    $updateData = [
        'description' => 'Updated description',
        'notes' => 'Updated notes',
        'amount' => 2000.00,
        'date' => now()->format('Y-m-d'),
    ];

    $updatedVoucher = app(GeneralVoucherService::class)->updateVoucher($voucher->id, $updateData, $user->id);

    expect($updatedVoucher->description)->toBe('Updated description');
    expect($updatedVoucher->notes)->toBe('Updated notes');
    expect($updatedVoucher->amount)->toBe(2000.0);
    expect($updatedVoucher->updated_by)->toBe($user->id);
});

test('voucher service handles posting posted voucher', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $voucher = Voucher::factory()->posted()->create([
        'organization_id' => $organization->id,
    ]);

    expect(fn () => app(GeneralVoucherService::class)->postVoucher($voucher->id, $user->id))
        ->toThrow('Voucher is already posted');
});
