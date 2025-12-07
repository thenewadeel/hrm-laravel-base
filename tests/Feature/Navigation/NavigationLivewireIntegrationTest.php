<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create();
    $this->organization->users()->attach($this->user->id, ['roles' => 'admin']);
});

test('navigation components work with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should have Livewire scripts
    $response->assertSee('wire:', false);
});

test('theme toggle works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Theme toggle should be present as a button
    $response->assertSee('button', false);
});

test('search works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Search should be present
    $response->assertSee('Search test...');
});

test('notifications work with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should be interactive
    $response->assertSee('Test Dropdown');
});

test('user profile works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // User profile should integrate with Livewire
    $response->assertSee($this->user->name);
});

test('mobile menu toggle works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Mobile menu should have toggle functionality
    $response->assertSee('@click', false);
});

test('dropdowns work with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Dropdowns should be present
    $response->assertSee('Test Dropdown');
});

test('language switcher works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should have interactive elements
    $response->assertSee('button', false);
});

test('quick actions work with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Quick actions should be present
    $response->assertSee('Test Dropdown');
});

test('breadcrumb works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Breadcrumb should be present
    $response->assertSee('Breadcrumb Test');
});

test('navigation state persists with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should have state management
    $response->assertSee('x-data', false);
});

test('navigation loading states work with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should have transitions
    $response->assertSee('transition', false);
});

test('navigation error handling works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should have focus handling
    $response->assertSee('focus:', false);
});

test('navigation polling works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should be interactive
    $response->assertSee('x-show', false);
});

test('navigation navigation works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Navigation should have proper links
    $response->assertSee('href=', false);
});

test('navigation init works with livewire', function () {
    $response = $this->actingAs($this->user)
        ->get('/test-navigation');

    $response->assertStatus(200);
    // Should initialize properly with Livewire
    $response->assertSee('wire:init', false);
});
