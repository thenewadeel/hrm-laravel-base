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
    $this->organization->users()->attach($this->user->id, ['roles' => ['admin']]);
});

it('renders all navigation sections on the dashboard', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Inventory')
        ->assertSee('Accounting')
        ->assertSee('Human Resources')
        ->assertSee('Membership')
        ->assertSee('Organization')
        ->assertSee('Reports & Analytics')
        ->assertSee('Admin Portal')
        ->assertSee('System Setup');
});

it('dropdown content renders correctly', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Catalogue') // Inventory dropdown content
        ->assertSee('Items')
        ->assertSee('Stores')
        ->assertSee('Stock Operations')
        ->assertSee('Transactions')
        ->assertSee('Insights')
        ->assertSee('Chart of Accounts') // Accounting dropdown content
        ->assertSee('Vouchers')
        ->assertSee('Bank & Cash')
        ->assertSee('Cash Receipts')
        ->assertSee('Receivables & Payables')
        ->assertSee('Assets & Tax')
        ->assertSee('Employees') // HR dropdown content
        ->assertSee('Shifts')
        ->assertSee('Attendance')
        ->assertSee('Payroll Management')
        ->assertSee('Quick actions');
});

it('navigation sections toggle on click', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('@click')
        ->assertSee('x-show')
        ->assertSee('toggleSection')
        ->assertSee('aria-expanded');
});

it('navigation icons are properly aligned', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('flex items-center justify-center')
        ->assertSee('flex-shrink-0');
});

it('sidebar supports collapse-to-rail and command search', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('toggle-rail')
        ->assertSee('rail-expand')
        ->assertSee('Rail navigation')
        ->assertSee('data-sidebar-toggle')
        ->assertSee('nav-rail')
        ->assertSee('Search navigation');
});

it('assigns real routes instead of dead links', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertDontSee('href="#"')
        ->assertSee('http://localhost/accounts');
});
