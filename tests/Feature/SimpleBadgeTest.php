<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders basic badge component', function () {
    $view = $this->blade('<x-badge>Test Badge</x-badge>');

    $view->assertSee('Test Badge');
    $view->assertSee('inline-flex');
    $view->assertSee('font-medium');
});

it('renders status badge component', function () {
    $view = $this->blade('<x-badge status="active">Active</x-badge>');

    $view->assertSee('Active');
    $view->assertSee('bg-success/15');
    $view->assertSee('text-success');
});
