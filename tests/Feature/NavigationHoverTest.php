<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create();
    $this->user->email_verified_at = now();
    $this->user->current_organization_id = $this->organization->id;
    $this->user->save();
    $this->organization->users()->attach($this->user->id, ['roles' => 'admin']);
});

it('dropdown opens on hover and closes on mouse leave', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Inventory')
        ->assertSee('Accounting')
        ->assertSee('Human Resources');
});

it('dropdown content renders correctly', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Items') // Inventory dropdown content
        ->assertSee('Stores')
        ->assertSee('Transactions')
        ->assertSee('Chart of Accounts') // Accounting dropdown content
        ->assertSee('Vouchers')
        ->assertSee('Cash Receipts')
        ->assertSee('Employees') // HR dropdown content
        ->assertSee('Shifts')
        ->assertSee('Attendance')
        ->assertSee('Payroll');
});

it('dropdown triggers have proper hover attributes', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('@mouseenter')
        ->assertSee('@mouseleave');
});

it('navigation icons are properly aligned', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('flex items-center justify-center')
        ->assertSee('flex-shrink-0');
});
