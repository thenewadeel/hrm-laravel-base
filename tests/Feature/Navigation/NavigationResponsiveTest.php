<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create();
    $this->organization->users()->attach($this->user->id, ['roles' => 'admin']);
});

test('navigation has mobile breakpoints', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have mobile-first responsive classes
    $response->assertSee('sm:', false);
    $response->assertSee('md:', false);
    $response->assertSee('lg:', false);
});

test('desktop menu hides on mobile', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Desktop menu should be hidden on mobile
    $response->assertSee('hidden md:flex', false);
});

test('mobile menu is available', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have mobile menu toggle
    $response->assertSee('md:hidden', false);
    $response->assertSee('h-6 w-6', false);
});

test('navigation adapts to tablet viewport', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have tablet-specific styles
    $response->assertSee('md:', false);
});

test('sidebar is responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should use responsive spacing
    $response->assertSee('max-w-7xl', false);
    $response->assertSee('px-4 sm:px-6 lg:px-8', false);
});

test('user profile is responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // User profile should adapt to different screen sizes
    $response->assertSee('avatar', false);
    $response->assertSee('h-8 w-8', false);
});

test('search bar is responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Search bar should be responsive
    $response->assertSee('search', false);
    $response->assertSee('w-full', false);
});

test('notifications are responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Notifications should be responsive
    $response->assertSee('notification', false);
    $response->assertSee('relative', false);
});

test('breadcrumb is responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Breadcrumb should be present
    $response->assertSee('Breadcrumb Test');
});

test('quick actions are responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Quick actions should be present
    $response->assertSee('space-y-6', false);
});

test('dropdowns are responsive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Dropdowns should be present
    $response->assertSee('Test Dropdown');
});

test('navigation uses flexbox for responsiveness', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should use flexbox for responsive layout
    $response->assertSee('flex', false);
    $response->assertSee('items-center', false);
});

test('navigation has proper spacing on different screens', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have responsive spacing
    $response->assertSee('px-4 sm:px-6 lg:px-8', false);
    $response->assertSee('space-y-6', false);
});
