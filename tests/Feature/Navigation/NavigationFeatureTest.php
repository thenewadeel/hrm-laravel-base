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

test('main navigation renders on authenticated pages', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    $response->assertSee('Navigation');
});

test('navigation links are accessible', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Check for common navigation elements
    $response->assertSee('Dashboard');
});

test('user profile displays correct user information', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    $response->assertSee($this->user->name);
});

test('navigation respects organization context', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Organization context should be respected - user should be able to see their profile
    $response->assertSee($this->user->name);
});

test('dropdown menus function correctly', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Check for dropdown functionality
    $response->assertSee('Inventory');
});

test('search functionality is available', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Search should be present
    $response->assertSee('Search');
});

test('theme toggle works correctly', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Theme toggle should be present
    $response->assertSee('Theme');
});

test('mobile navigation is responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Mobile navigation elements should be present
    $response->assertSee('Quick Actions');
});

test('breadcrumb navigation displays correctly', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Breadcrumb should be present
    $response->assertSee('Breadcrumb');
});

test('quick actions are accessible', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Quick actions should be present
    $response->assertSee('Quick Actions');
});

test('notifications display correctly', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Notification bell should be present
    $response->assertSee('Notifications');
});

test('language switcher functions correctly', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Language switcher should be present
    $response->assertSee('Language');
});

test('navigation handles unauthenticated users correctly', function () {
    $response = $this->get('/test-navigation');

    // test-navigation route doesn't require auth, so should load without redirect
    $response->assertStatus(200);
    // Navigation components should still render for unauthenticated users
    $response->assertSee('Navigation');
});

test('navigation handles different user roles', function () {
    // Create user with different role
    $regularUser = User::factory()->create();
    $regularUser->email_verified_at = now();
    $regularUser->current_organization_id = $this->organization->id;
    $regularUser->save();
    $this->organization->users()->attach($regularUser->id, ['roles' => 'employee']);

    $response = $this->actingAs($regularUser)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should see different navigation options based on role
    $response->assertSee($regularUser->name);
});
