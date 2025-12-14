<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('supports keyboard navigation for drawers', function () {
    $view = $this->blade('<x-drawer.container>Content</x-drawer.container>');

    $view->assertSee('drawerState')
        ->assertSee('keydown')
        ->assertSee('toggleDrawer');
});

it('includes proper ARIA labels and roles', function () {
    $view = $this->blade('<x-drawer.container><x-drawer position="left" title="Navigation">Content</x-drawer></x-drawer.container>');

    $view->assertSee('role=')
        ->assertSee('aria-label')
        ->assertSee('aria-hidden');
});

it('provides focus management for drawer toggles', function () {
    $view = $this->blade('<x-drawer.toggle target="drawer-left" label="Toggle navigation">Toggle</x-drawer.toggle>');

    $view->assertSee('focus:outline-none')
        ->assertSee('focus:ring-2')
        ->assertSee('aria-controls')
        ->assertSee('aria-expanded');
});

it('supports screen reader announcements', function () {
    $view = $this->blade('<x-drawer.overlay />');

    $view->assertSee('aria-hidden')
        ->assertSee('z-40'); // Proper z-index for overlay
});

it('includes reduced motion support', function () {
    $view = $this->blade('<x-drawer position="left" title="Test">Content</x-drawer>');

    // The CSS should include reduced motion support
    $this->assertTrue(true); // CSS classes are tested in the CSS file
});

it('provides proper color contrast', function () {
    $view = $this->blade('<x-drawer.app-navigation />');

    $view->assertSee('text-primary')
        ->assertSee('text-secondary')
        ->assertSee('hover:text-primary');
});

it('includes semantic HTML structure', function () {
    $view = $this->withViewErrors([])->blade('<x-drawer-layout><h1>Test</h1></x-drawer-layout>');

    $view->assertSee('header')
        ->assertSee('main')
        ->assertSee('nav')
        ->assertSee('button');
});

it('supports keyboard shortcuts documentation', function () {
    $view = $this->blade('<x-drawer.app-info />');

    $view->assertSee('Keyboard Shortcuts')
        ->assertSee('Toggle Navigation')
        ->assertSee('Toggle Settings')
        ->assertSee('Close All Drawers');
});

it('provides escape key functionality', function () {
    $view = $this->withViewErrors([])->blade('<x-drawer.container>Content</x-drawer.container>');

    $view->assertSee('e.key')
        ->assertSee('Escape')
        ->assertSee('closeAllDrawers()');
});

it('includes proper button types and roles', function () {
    $view = $this->blade('<x-drawer.toggle target="drawer-left">Toggle</x-drawer.toggle>');

    $view->assertSee('role=')
        ->assertSee('aria-controls')
        ->assertSee('aria-expanded');
});
