<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('drawer layout components exist', function () {
    // Test that component views exist
    expect(view()->exists('components.drawer.container'))->toBeTrue();
    expect(view()->exists('components.drawer.drawer'))->toBeTrue();
    expect(view()->exists('components.drawer.toggle'))->toBeTrue();
    expect(view()->exists('components.drawer.overlay'))->toBeTrue();
    expect(view()->exists('components.drawer-layout'))->toBeTrue();
});

it('drawer content components exist', function () {
    // Test that content component views exist
    expect(view()->exists('components.drawer.app-navigation'))->toBeTrue();
    expect(view()->exists('components.drawer.module-settings'))->toBeTrue();
    expect(view()->exists('components.drawer.app-info'))->toBeTrue();
    expect(view()->exists('components.drawer.user-preferences'))->toBeTrue();
});

it('drawer settings components exist', function () {
    // Test that settings component views exist
    expect(view()->exists('components.drawer.inventory-settings'))->toBeTrue();
    expect(view()->exists('components.drawer.accounting-settings'))->toBeTrue();
    expect(view()->exists('components.drawer.hr-settings'))->toBeTrue();
    expect(view()->exists('components.drawer.organization-settings'))->toBeTrue();
    expect(view()->exists('components.drawer.general-settings'))->toBeTrue();
});

it('drawer toggle components exist', function () {
    // Test that toggle component views exist
    expect(view()->exists('components.drawer.toggles.header-toggles'))->toBeTrue();
    expect(view()->exists('components.drawer.toggles.floating-toggles'))->toBeTrue();
});
