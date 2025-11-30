<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders simple badge test page', function () {
    $response = $this->get('/simple-badge-test');

    $response->assertStatus(200);
    $response->assertSee('Badge System Test');
    $response->assertSee('Default Badge');
    $response->assertSee('Green Badge');
    $response->assertSee('Red Badge');
});
