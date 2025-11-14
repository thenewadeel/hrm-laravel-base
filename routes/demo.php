<?php

use App\Services\AccountingReportService;
use Illuminate\Support\Facades\Route;
// Demo Components
Route::get('/demo/inventory-components', function () {
    return view('demo.inventory-components');
})->name('demo.inventory-components');
