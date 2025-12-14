<?php

use function Pest\Laravel\get;

it('renders drawer test page with all components', function () {
    get('/test-drawers')
        ->assertStatus(200)
        ->assertSee('Simple Drawer Test')
        ->assertSee('Toggle Left Drawer')
        ->assertSee('Toggle Right Drawer');
});

it('includes all drawer components in layout', function () {
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

it('includes drawer content components', function () {
    get('/test-drawers')
        ->assertStatus(200)
        ->assertSee('Navigation')
        ->assertSee('Module Settings')
        ->assertSee('App Information')
        ->assertSee('User Preferences');
});
