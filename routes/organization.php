<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HrmDashboardController;
use App\Http\Controllers\OrganizationDashboardController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Portal\EmployeePortalController;
use App\Http\Controllers\Portal\ManagerPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('/organizations')->name('organiazations.')->group(function () {
    Route::get('/', [OrganizationController::class, 'index'])->name('index');
    Route::get('/dashboard', [OrganizationDashboardController::class, 'index'])
        ->name('dashboard');
});
// Route::livewire(['/organizations', OrganizationList::class])->name('organizations.index');
// Route::get('/companies/create', CompanyForm::class)->name('companies.create');
// Route::get('/companies/{company}/edit', CompanyForm::class)->name('companies.edit');
// Route::get('/companies/{company}', CompanyShow::class)->name('companies.show');

// // Organization unit routes
// Route::get('/units', UnitTree::class)->name('units.index');
// Route::get('/units/create', UnitForm::class)->name('units.create');
// Route::get('/units/{unit}/edit', UnitForm::class)->name('units.edit');
