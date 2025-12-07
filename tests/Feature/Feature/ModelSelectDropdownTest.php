<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;

test('model select dropdown component renders correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);

    $this->actingAs($user);

    $response = $this->get('/demo/model-select');

    $response->assertStatus(200);
    $response->assertSee('Model Select Dropdown Demo');
    $response->assertSee('Select Employee');
    $response->assertSee('Select Multiple Employees');
    $response->assertSee('Select Account');
    $response->assertSee('Select Department');
});

test('model select dropdown handles employee data correctly', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);

    // Create test employees
    $employees = Employee::factory()->count(5)->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    $response = $this->get('/demo/model-select');

    $response->assertStatus(200);

    // Check that employee names appear in the rendered component
    foreach ($employees as $employee) {
        $response->assertSee($employee->first_name);
        $response->assertSee($employee->last_name);
    }
});

test('model select dropdown includes required JavaScript functionality', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);

    $this->actingAs($user);

    $response = $this->get('/demo/model-select');

    $response->assertStatus(200);
    $response->assertSee('modelSelectDropdown');
    $response->assertSee('toggleDropdown');
    $response->assertSee('filterOptions');
    $response->assertSee('selectOption');
});

test('model select dropdown handles chart of accounts data', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);

    // Create test chart of accounts
    $accounts = ChartOfAccount::factory()->count(3)->create(['organization_id' => $organization->id]);

    $this->actingAs($user);

    $response = $this->get('/demo/model-select');

    $response->assertStatus(200);

    // Check that account names appear in the rendered component
    foreach ($accounts as $account) {
        $response->assertSee($account->name);
    }
});
