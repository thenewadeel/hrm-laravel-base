<?php

use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\JobPositionController;
use App\Http\Controllers\HR\ShiftController;
use App\Http\Controllers\HrmDashboardController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Portal\EmployeePortalController;
use App\Http\Controllers\Portal\ManagerPortalController;
use Illuminate\Support\Facades\Route;

// -------------------
// HRM routes
// -------------------
// Employee Portal Routes
Route::prefix('portal/employee')->name('portal.employee.')->group(function () {
    Route::get('/dashboard', [EmployeePortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [EmployeePortalController::class, 'attendance'])->name('attendance');
    Route::post('/clock-in', [EmployeePortalController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [EmployeePortalController::class, 'clockOut'])->name('clock-out');

    // Setup Routes
    Route::get('/setup', [EmployeePortalController::class, 'setup'])->name('setup');
    Route::post('/setup', [EmployeePortalController::class, 'completeSetup'])->name('complete-setup');

    // Leave Routes
    Route::get('/leave', [EmployeePortalController::class, 'leave'])->name('leave');
    Route::get('/leave/create', [EmployeePortalController::class, 'createLeave'])->name('leave.create');
    Route::post('/leave', [EmployeePortalController::class, 'storeLeave'])->name('leave.store');

    // Payslip Routes
    Route::get('/payslips', [EmployeePortalController::class, 'payslips'])->name('payslips');
    Route::get('/payslips/{payslip}', [EmployeePortalController::class, 'showPayslip'])->name('payslips.show');
    Route::get('/payslips/{payslip}/download', [EmployeePortalController::class, 'downloadPayslip'])->name('payslips.download');
});

// Employee Portal Routes
Route::prefix('portal/employee')->name('portal.employee.')->group(function () {
    Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out');
});

// Payroll Routes (if separate)
Route::get('/payroll/processing', [PayrollController::class, 'processing'])->name('payroll.processing');

// Manager Portal Routes
Route::prefix('portal/manager')->name('portal.manager.')->group(function () {
    Route::get('/dashboard', [ManagerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/team-attendance', [ManagerPortalController::class, 'teamAttendance'])->name('team-attendance');
    Route::get('/reports', [ManagerPortalController::class, 'reports'])->name('reports');

    // Leave Approval Routes
    Route::post('/leave/{leaveRequest}/approve', [ManagerPortalController::class, 'approveLeave'])->name('leave.approve');
    Route::post('/leave/{leaveRequest}/reject', [ManagerPortalController::class, 'rejectLeave'])->name('leave.reject');
});

// Attendance Routes
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');
    Route::post('/sync-biometric', [AttendanceController::class, 'syncBiometricData'])->name('biometric-sync');
    Route::post('/regularize/{id}', [AttendanceController::class, 'regularizeTime'])->name('regularize');
    Route::post('/apply-leave/{id}', [AttendanceController::class, 'applyLeave'])->name('apply-leave');
    Route::get('/export-payroll', [AttendanceController::class, 'exportForPayroll'])->name('export-payroll');
});
Route::get('/hrm/dashboard', [HrmDashboardController::class, 'index'])
    ->name('hrm.dashboard');
// // Employee routes
Route::prefix('hr')->group(function () {
    // Route::resource('hr/employees', EmployeeController::class)
    // HR Employee Routes
    Route::resource('employees', EmployeeController::class)->names([
        'index' => 'hr.employees.index',
        'create' => 'hr.employees.create',
        'update' => 'hr.employees.update',
        'store' => 'hr.employees.store',
        'show' => 'hr.employees.show',
        'edit' => 'hr.employees.edit',
        'destroy' => 'hr.employees.destroy',
    ]);

    // Additional routes for employee management
    Route::put('employees/{employee}/biometric', [\App\Http\Controllers\HR\EmployeeController::class, 'updateBiometric'])
        ->name('hr.employees.update-biometric');

    Route::post('employees/{employee}/grant-access', [\App\Http\Controllers\HR\EmployeeController::class, 'grantSystemAccess'])
        ->name('hr.employees.grant-access');

    Route::post('employees/without-user', [\App\Http\Controllers\HR\EmployeeController::class, 'storeWithoutUser'])
        ->name('hr.employees.store-without-user');

    // Job Positions Routes
    Route::resource('positions', JobPositionController::class)->names([
        'index' => 'hr.positions.index',
        'create' => 'hr.positions.create',
        'update' => 'hr.positions.update',
        'store' => 'hr.positions.store',
        'show' => 'hr.positions.show',
        'edit' => 'hr.positions.edit',
        'destroy' => 'hr.positions.destroy',
    ]);

    // Shifts Routes
    Route::resource('shifts', ShiftController::class)->names([
        'index' => 'hr.shifts.index',
        'create' => 'hr.shifts.create',
        'update' => 'hr.shifts.update',
        'store' => 'hr.shifts.store',
        'show' => 'hr.shifts.show',
        'edit' => 'hr.shifts.edit',
        'destroy' => 'hr.shifts.destroy',
    ]);
});

// Route::get('/attendance/dashboard', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');
// Route::get('/payroll/processing', [PayrollController::class, 'processing'])->name('payroll.processing');
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
