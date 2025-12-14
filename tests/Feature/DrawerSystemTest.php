<?php

use function Pest\Laravel\get;

it('renders the drawer test page successfully', function () {
    get('/test-drawers')
        ->assertStatus(200)
        ->assertSee('Simple Drawer Test')
        ->assertSee('Toggle Left');
});

it('includes all drawer components in the layout', function () {
    get('/test-drawers')
        ->assertStatus(200)
        ->assertSee('drawer-left')
        ->assertSee('drawer-right')
        ->assertSee('drawer-top')
        ->assertSee('drawer-bottom')
        ->assertSee('drawer-overlay');
});

it('includes Alpine.js drawer system initialization', function () {
    get('/test-drawers')
        ->assertStatus(200)
        ->assertSee('x-data')
        ->assertSee('drawers')
        ->assertSee('toggleDrawer')
        ->assertSee('closeAllDrawers');
});

it('includes proper drawer transition classes', function () {
    get('/test-drawers')
        ->assertStatus(200)
        ->assertSee('x-transition:enter')
        ->assertSee('x-transition:leave')
        ->assertSee('duration-300');
});

it('includes accessibility attributes', function () {
    get('/test-drawers')
        ->assertStatus(200)
        ->assertSee('aria-expanded')
        ->assertSee('aria-controls')
        ->assertSee('aria-label')
        ->assertSee('role=');
});
