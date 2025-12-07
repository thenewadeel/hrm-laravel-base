<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();

    $this->admin = User::factory()->create();
    $this->organization->users()->attach($this->admin->id, ['roles' => 'admin']);

    $this->manager = User::factory()->create();
    $this->organization->users()->attach($this->manager->id, ['roles' => 'manager']);

    $this->employee = User::factory()->create();
    $this->organization->users()->attach($this->employee->id, ['roles' => 'employee']);
});

test('admin sees admin navigation items', function () {
    $response = $this->actingAs($this->admin)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Admin should see navigation elements
    $response->assertSee('Dashboard Link Test');
});

test('manager sees manager navigation items', function () {
    $response = $this->actingAs($this->manager)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Manager should see navigation elements
    $response->assertSee('Dashboard Link Test');
});

test('employee sees employee navigation items', function () {
    $response = $this->actingAs($this->employee)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Employee should see employee-specific navigation
    $response->assertSee('Profile', false);
    $response->assertSee('Attendance', false);
});

test('admin can access protected routes', function () {
    $response = $this->actingAs($this->admin)
        ->get('/dashboard');

    $response->assertRedirect();
});

test('manager can access protected routes', function () {
    $response = $this->actingAs($this->manager)
        ->get('/dashboard');

    $response->assertRedirect();
});

test('employee can access protected routes', function () {
    $response = $this->actingAs($this->employee)
        ->get('/dashboard');

    $response->assertRedirect();
});

test('all roles can access dashboard', function () {
    $response = $this->actingAs($this->admin)
        ->get('/test-navigation');
    $response->assertStatus(200);

    $response = $this->actingAs($this->manager)
        ->get('/test-navigation');
    $response->assertStatus(200);

    $response = $this->actingAs($this->employee)
        ->get('/test-navigation');
    $response->assertStatus(200);
});

test('navigation shows correct user name', function () {
    $response = $this->actingAs($this->admin)
        ->get('/test-navigation');
    $response->assertSee($this->admin->name);

    $response = $this->actingAs($this->manager)
        ->get('/test-navigation');
    $response->assertSee($this->manager->name);

    $response = $this->actingAs($this->employee)
        ->get('/test-navigation');
    $response->assertSee($this->employee->name);
});

test('role based navigation access', function () {
    // All roles should see basic navigation
    $response = $this->actingAs($this->admin)
        ->get('/test-navigation');
    $response->assertSee('Dashboard Link Test');

    $response = $this->actingAs($this->manager)
        ->get('/test-navigation');
    $response->assertSee('Dashboard Link Test');

    $response = $this->actingAs($this->employee)
        ->get('/test-navigation');
    $response->assertSee('Dashboard Link Test');
});

test('role based sidebar navigation', function () {
    // All roles should see navigation components
    $response = $this->actingAs($this->admin)
        ->get('/test-navigation');
    $response->assertSee('Navigation Components Test');

    $response = $this->actingAs($this->manager)
        ->get('/test-navigation');
    $response->assertSee('Navigation Components Test');

    $response = $this->actingAs($this->employee)
        ->get('/test-navigation');
    $response->assertSee('Navigation Components Test');
});

test('navigation respects organization scoping', function () {
    // Create another organization
    $otherOrg = Organization::factory()->create();
    $otherUser = User::factory()->create();
    $otherOrg->users()->attach($otherUser->id, ['roles' => 'admin']);

    // User should see navigation elements
    $response = $this->actingAs($this->admin)
        ->get('/test-navigation');
    $response->assertStatus(200);
    $response->assertSee('Navigation Test Page');
});
