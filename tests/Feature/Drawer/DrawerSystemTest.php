<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders drawer container component', function () {
    $view = $this->blade('
        <x-drawer.container>
            <div>Test Content</div>
        </x-drawer.container>
    ');

    $view->assertSee('Test Content')
        ->assertSee('drawer-container')
        ->assertSee('drawerState');
});

it('renders drawer component with correct position', function () {
    $view = $this->blade('<x-drawer position="left" title="Test Drawer">Content</x-drawer>');

    $view->assertSee('Test Drawer')
        ->assertSee('drawer-left')
        ->assertSee('Content');
});

it('renders drawer toggle component', function () {
    $view = $this->blade('<x-drawer.toggle target="drawer-left">Toggle</x-drawer.toggle>');

    $view->assertSee('Toggle')
        ->assertSee('drawer-toggle')
        ->assertSee('toggleDrawer');
});

it('renders drawer overlay component', function () {
    $view = $this->blade('<x-drawer.overlay />');

    $view->assertSee('drawer-overlay')
        ->assertSee('closeAllDrawers');
});

it('renders complete drawer layout', function () {
    $view = $this->withViewErrors([])->blade('<x-drawer-layout><h1>Test Page</h1></x-drawer-layout>');

    $view->assertSee('Test Page')
        ->assertSee('drawer-container')
        ->assertSee('Navigation')
        ->assertSee('Module Settings')
        ->assertSee('App Information')
        ->assertSee('User Preferences');
});

it('renders app navigation drawer content', function () {
    $view = $this->blade('<x-drawer.app-navigation />');

    $view->assertSee('Dashboard')
        ->assertSee('Organization')
        ->assertSee('Human Resources')
        ->assertSee('Accounting')
        ->assertSee('Inventory')
        ->assertSee('Reports')
        ->assertSee('Analytics')
        ->assertSee('Membership')
        ->assertSee('Quick Actions');
});

it('renders module settings drawer content', function () {
    $view = $this->blade('<x-drawer.module-settings />');

    $view->assertSee('Current Module')
        ->assertSee('Common Settings')
        ->assertSee('Display Density')
        ->assertSee('Auto-refresh');
});

it('renders app info drawer content', function () {
    $view = $this->blade('<x-drawer.app-info />');

    $view->assertSee('System Status')
        ->assertSee('Application')
        ->assertSee('Technical Details')
        ->assertSee('Quick Help')
        ->assertSee('Keyboard Shortcuts');
});

it('renders user preferences drawer content', function () {
    $view = $this->blade('<x-drawer.user-preferences />');

    $view->assertSee('Appearance')
        ->assertSee('Language')
        ->assertSee('Region')
        ->assertSee('Notifications')
        ->assertSee('Privacy')
        ->assertSee('Security');
});

it('renders inventory settings content', function () {
    $view = $this->blade('<x-drawer.inventory-settings />');

    $view->assertSee('Inventory Settings')
        ->assertSee('Stock Management')
        ->assertSee('Costing Method')
        ->assertSee('Display Options');
});

it('renders accounting settings content', function () {
    $view = $this->blade('<x-drawer.accounting-settings />');

    $view->assertSee('Accounting Settings')
        ->assertSee('Financial Year')
        ->assertSee('Voucher Settings')
        ->assertSee('Currency Settings')
        ->assertSee('Tax Settings');
});

it('renders HR settings content', function () {
    $view = $this->blade('<x-drawer.hr-settings />');

    $view->assertSee('HR Settings')
        ->assertSee('Attendance Settings')
        ->assertSee('Leave Settings')
        ->assertSee('Payroll Settings');
});

it('renders organization settings content', function () {
    $view = $this->blade('<x-drawer.organization-settings />');

    $view->assertSee('Organization Settings')
        ->assertSee('General')
        ->assertSee('Member Management')
        ->assertSee('Security');
});

it('renders general settings content', function () {
    $view = $this->blade('<x-drawer.general-settings />');

    $view->assertSee('General Settings')
        ->assertSee('Application')
        ->assertSee('Display')
        ->assertSee('Notifications');
});

it('includes proper accessibility attributes', function () {
    $view = $this->blade('<x-drawer position="left" title="Test Drawer">Content</x-drawer>');

    $view->assertSee('role=')
        ->assertSee('aria-label')
        ->assertSee('aria-hidden');
});

it('includes keyboard shortcuts in app info', function () {
    $view = $this->blade('<x-drawer.app-info />');

    $view->assertSee('Ctrl+L')
        ->assertSee('Ctrl+R')
        ->assertSee('Ctrl+T')
        ->assertSee('Ctrl+B')
        ->assertSee('Esc');
});
