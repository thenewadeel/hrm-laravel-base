<?php

use App\Models\Accounting\Voucher;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('voucher can be created with required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $voucher = Voucher::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'sales',
        'number' => 'SALES-2025-0001',
        'date' => now(),
        'amount' => 1000.00,
        'description' => 'Test sales voucher',
        'created_by' => $user->id,
    ]);

    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->organization_id)->toBe($organization->id);
    expect($voucher->type)->toBe('sales');
    expect($voucher->number)->toBe('SALES-2025-0001');
    expect($voucher->amount)->toBe(1000.0);
    expect($voucher->description)->toBe('Test sales voucher');
    expect($voucher->created_by)->toBe($user->id);
});

test('voucher belongs to organization', function () {
    $organization = Organization::factory()->create();
    $voucher = Voucher::factory()->create(['organization_id' => $organization->id]);

    expect($voucher->organization)->toBeInstanceOf(Organization::class);
    expect($voucher->organization->id)->toBe($organization->id);
});

test('voucher belongs to creator', function () {
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(['created_by' => $user->id]);

    expect($voucher->creator)->toBeInstanceOf(User::class);
    expect($voucher->creator->id)->toBe($user->id);
});

test('voucher belongs to updater', function () {
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(['updated_by' => $user->id]);

    expect($voucher->updater)->toBeInstanceOf(User::class);
    expect($voucher->updater->id)->toBe($user->id);
});

test('voucher uses soft deletes', function () {
    $voucher = Voucher::factory()->create();

    $voucher->delete();

    expect($voucher->trashed())->toBeTrue();
    expect(Voucher::find($voucher->id))->toBeNull();
    expect(Voucher::withTrashed()->find($voucher->id))->not->toBeNull();
});

test('voucher casts amount as decimal', function () {
    $voucher = Voucher::factory()->create(['amount' => 1234.56]);

    expect($voucher->amount)->toBeFloat();
    expect((float) $voucher->amount)->toBe(1234.56);
});

test('voucher casts date properly', function () {
    $date = now();
    $voucher = Voucher::factory()->create(['date' => $date]);

    expect($voucher->date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($voucher->date->format('Y-m-d'))->toBe($date->format('Y-m-d'));
});

test('voucher can have different types', function () {
    $types = ['sales', 'sales_return', 'purchase', 'purchase_return', 'salary', 'expense', 'fixed_asset', 'depreciation'];

    foreach ($types as $type) {
        $voucher = Voucher::factory()->create(['type' => $type]);
        expect($voucher->type)->toBe($type);
    }
});

test('voucher can be drafted', function () {
    $voucher = Voucher::factory()->create(['status' => 'draft']);

    expect($voucher->status)->toBe('draft');
    expect($voucher->isDraft())->toBeTrue();
    expect($voucher->isPosted())->toBeFalse();
});

test('voucher can be posted', function () {
    $voucher = Voucher::factory()->create(['status' => 'posted']);

    expect($voucher->status)->toBe('posted');
    expect($voucher->isPosted())->toBeTrue();
    expect($voucher->isDraft())->toBeFalse();
});

test('voucher generates sequential numbers', function () {
    $organization = Organization::factory()->create();

    // Test the generateNumber method format
    $number = Voucher::generateNumber('sales', $organization->id);

    expect($number)->toBeString();
    expect($number)->toMatch('/^SALES-\d{4}-\d{4}$/');
    expect($number)->toContain('SALES-2025-');
});
