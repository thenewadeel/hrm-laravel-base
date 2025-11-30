<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders badge showcase page', function () {
    $response = $this->get('/simple-badge-test');

    $response->assertStatus(200);
    $response->assertSee('Badge System Test');
});

it('renders basic badge in showcase', function () {
    $response = $this->get('/simple-badge-test');

    $response->assertSee('Default Badge');
    $response->assertSee('Green Badge');
    $response->assertSee('Red Badge');
});

it('renders status badges in showcase', function () {
    $response = $this->get('/simple-badge-test');

    $response->assertSee('Badge System Test');
});

it('renders category badges in showcase', function () {
    $response = $this->get('/simple-badge-test');

    $response->assertSee('Badge System Test');
});
