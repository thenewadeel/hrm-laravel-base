<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\OrganizationController;
use App\Http\Livewire\Organization\OrganizationList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    // Company routes
    // Route::livewire(['/organizations', OrganizationList::class])->name('organizations.index');
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    // Route::get('/companies/create', CompanyForm::class)->name('companies.create');
    // Route::get('/companies/{company}/edit', CompanyForm::class)->name('companies.edit');
    // Route::get('/companies/{company}', CompanyShow::class)->name('companies.show');

    // // Organization unit routes
    // Route::get('/units', UnitTree::class)->name('units.index');
    // Route::get('/units/create', UnitForm::class)->name('units.create');
    // Route::get('/units/{unit}/edit', UnitForm::class)->name('units.edit');

    // // Employee routes
    // Route::get('/employees', EmployeeList::class)->name('employees.index');
    // Route::get('/employees/create', EmployeeForm::class)->name('employees.create');
    // Route::get('/employees/{employee}/edit', EmployeeForm::class)->name('employees.edit');
    // Route::get('/employees/{employee}', EmployeeShow::class)->name('employees.show');

    // // User management routes
    // Route::get('/users', UserList::class)->name('users.index');
    // Route::get('/users/create', UserForm::class)->name('users.create');
    // Route::get('/users/{user}/edit', UserForm::class)->name('users.edit');
    // Route::get('/roles', RoleManager::class)->name('roles.index');
    // });

    Route::get('/accounts', [AccountsController::class, 'index'])->name('accounting.index');
});

// Temporary debug route
Route::get('/debug/api-config', function () {
    return response()->json([
        'app_url' => config('app.url'),
        'api_url' => config('app.api_url'),
        'env_api_url' => env('API_URL'),
        'full_api_endpoint' => config('app.api_url') . '/journal-entries',
        'is_local' => app()->isLocal(),
        'environment' => app()->environment(),
        'cors_config' => config('cors'),
        'timezone' => config('app.timezone')
    ]);
});



// In routes/web.php
Route::get('/debug/journal-entries', function () {
    try {
        $entries = \App\Models\Accounting\JournalEntry::with('ledgerEntries.account')->get();
        return response()->json($entries);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// In routes/web.php
Route::get('/debug/test-sequence', function () {
    try {
        $sequenceService = app(App\Services\SequenceService::class);

        // Test 1: Check current value
        $current = $sequenceService->peek('journal_entry_ref');
        echo "Current value: " . $current . "<br>";

        // Test 2: Generate a new value
        // $ref1 = $sequenceService->generate('journal_entry_ref');
        // echo "Generated 1: " . $ref1 . "<br>";

        // Test 3: Reserve a value
        $ref2 = $sequenceService->reserve('journal_entry_ref');
        echo "Reserved: " . json_encode($ref2) . "<br>";

        // Test 4: Check value after generation
        $currentAfter = $sequenceService->peek('journal_entry_ref');
        echo "Value after generation: " . $currentAfter . "<br>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
