<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\OrganizationDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('/organizations')->name('organization.')->group(function () {
    Route::get('/', [OrganizationController::class, 'index'])->name('index');
    Route::get('/dashboard', [OrganizationDashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/structure', [OrganizationDashboardController::class, 'structure'])
        ->name('structure');
    Route::get('/analytics', [OrganizationDashboardController::class, 'analytics'])
        ->name('analytics');
});
// Route::livewire(['/organizations', OrganizationList::class])->name('organizations.index');
// Route::get('/companies/create', CompanyForm::class)->name('companies.create');
// Route::get('/companies/{company}/edit', CompanyForm::class)->name('companies.edit');
// Route::get('/companies/{company}', CompanyShow::class)->name('companies.show');

// // Organization unit routes
// Route::get('/units', UnitTree::class)->name('units.index');
// Route::get('/units/create', UnitForm::class)->name('units.create');
// Route::get('/units/{unit}/edit', UnitForm::class)->name('units.edit');
