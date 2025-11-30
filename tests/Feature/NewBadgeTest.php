<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders new-badge component', function () {
    $view = $this->blade('<x-new-badge>Test Badge</x-new-badge>');

    $view->assertSee('Test Badge');
    $view->assertSee('inline-flex');
    $view->assertSee('font-medium');
});

it('renders new-status-badge component', function () {
    $view = $this->blade('<x-new-status-badge status="active" />');

    $view->assertSee('Active');
    $view->assertSee('bg-green-100');
    $view->assertSee('text-green-800');
});
