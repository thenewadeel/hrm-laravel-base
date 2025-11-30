<?php

use App\Livewire\Demo\ModelSelectDemo;
use Illuminate\Support\Facades\Route;

// Demo Components
Route::get('/demo/inventory-components', function () {
    return view('demo.inventory-components');
})->name('demo.inventory-components');

Route::get('/demo/cash-management', function () {
    $organization = \App\Models\Organization::first();
    if (! $organization) {
        $organization = \App\Models\Organization::factory()->create();
    }

    return view('demo.cash-management', ['organization' => $organization]);
})->name('demo.cash-management');

Route::get('/demo/model-select', ModelSelectDemo::class)->name('demo.model-select');
