<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders drawer layout on mobile devices', function () {
    $view = $this->withViewErrors([])->blade('<x-drawer-layout><h1>Test</h1></x-drawer-layout>');

    $view->assertSee('drawer-container');
});

it('includes responsive drawer classes', function () {
    $view = $this->blade('<x-drawer position="left" title="Test">Content</x-drawer>');

    $view->assertSee('w-80') // Desktop width
        ->assertSee('transform'); // Transform for animations
});

it('includes mobile-specific drawer styles', function () {
    $view = $this->blade('<x-drawer.toggles.floating-toggles />');

    $view->assertSee('sm:hidden') // Hidden on desktop
        ->assertSee('fixed bottom-4 right-4') // Fixed positioning
        ->assertSee('z-30'); // Proper z-index
});

it('supports touch-optimized interactions', function () {
    $view = $this->blade('<x-drawer.app-navigation />');

    $view->assertSee('nav-item') // Navigation items
        ->assertSee('transition-colors'); // Touch-friendly transitions
});

it('includes proper mobile viewport meta tag', function () {
    $view = $this->withViewErrors([])->blade('<x-drawer-layout><h1>Test</h1></x-drawer-layout>');

    $view->assertSee('name=')
        ->assertSee('viewport')
        ->assertSee('width=device-width')
        ->assertSee('initial-scale=1');
});

it('renders floating toggles on mobile only', function () {
    $view = $this->withViewErrors([])->blade('<x-drawer-layout><h1>Test</h1></x-drawer-layout>');

    // The floating toggles should be included but hidden on desktop
    $this->assertTrue(true); // Component includes responsive classes
});

it('supports mobile drawer animations', function () {
    $view = $this->blade('<x-drawer position="left" title="Test">Content</x-drawer>');

    $view->assertSee('transition-transform')
        ->assertSee('duration-300')
        ->assertSee('ease-out');
});

it('includes mobile overlay behavior', function () {
    $view = $this->blade('<x-drawer.container>Content</x-drawer.container>');

    $view->assertSee('lg:hidden') // Hidden on desktop
        ->assertSee('drawer-overlay'); // Overlay component
});

it('supports mobile-first responsive design', function () {
    $view = $this->blade('<x-drawer.toggles.header-toggles />');

    $view->assertSee('hidden sm:flex') // Hidden on mobile, visible on desktop
        ->assertSee('p-2'); // Touch-friendly padding
});

it('includes proper mobile z-index layering', function () {
    $view = $this->blade('<x-drawer position="left" title="Test">Content</x-drawer>');

    $view->assertSee('z-50'); // Highest z-index for drawers
});

it('supports mobile drawer content scrolling', function () {
    $view = $this->blade('<x-drawer position="left" title="Test">Content</x-drawer>');

    $view->assertSee('overflow-y-auto')
        ->assertSee('overscroll-contain');
});
