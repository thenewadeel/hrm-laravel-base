<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders badge standalone page', function () {
    $response = $this->get('/badge-standalone');

    $response->assertStatus(200);
    $response->assertSee('Badge System Test');
    $response->assertSee('Default Badge');
    $response->assertSee('Green Badge');
    $response->assertSee('Red Badge');
    $response->assertSee('Active');
    $response->assertSee('Sales');
    $response->assertSee('Low');
});
