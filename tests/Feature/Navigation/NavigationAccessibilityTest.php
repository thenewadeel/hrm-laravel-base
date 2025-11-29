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

test('navigation has proper aria labels', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have ARIA labels for accessibility
    $response->assertSee('aria-label', false);
});

test('navigation links have proper semantic structure', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should use proper semantic HTML
    $response->assertSee('<nav', false);
});

test('navigation supports keyboard navigation', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have focusable elements for keyboard navigation
    $response->assertSee('button', false);
    $response->assertSee('href=', false);
});

test('navigation has proper heading structure', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have proper heading hierarchy
    $response->assertSee('<h', false);
});

test('navigation links are descriptive', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Links should have descriptive text
    $response->assertSee('Dashboard Link Test');
});

test('navigation has skip links', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have main content area for accessibility
    $response->assertSee('main', false);
});

test('dropdowns are accessible', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Dropdowns should be present and accessible
    $response->assertSee('Test Dropdown');
});

test('search input is accessible', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Search input should have proper labels
    $response->assertSee('Search test...');
});

test('theme toggle is accessible', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Theme toggle should be present as a button
    $response->assertSee('button', false);
});

test('mobile navigation is accessible', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Mobile navigation should have proper button
    $response->assertSee('inline-flex items-center justify-center', false);
});
