<?php

use App\Http\Controllers\Portal\EmployeePortalController;
use App\Http\Controllers\Portal\ManagerPortalController;
use Illuminate\Support\Facades\Route;


// Employee Portal Routes
Route::prefix('portal/employee')->name('portal.employee.')->group(function () {
    Route::get('/dashboard', [EmployeePortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [EmployeePortalController::class, 'attendance'])->name('attendance');
    Route::post('/clock-in', [EmployeePortalController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [EmployeePortalController::class, 'clockOut'])->name('clock-out');

    // Leave Routes
    Route::get('/leave', [EmployeePortalController::class, 'leave'])->name('leave');
    Route::get('/leave/create', [EmployeePortalController::class, 'createLeave'])->name('leave.create');
    Route::post('/leave', [EmployeePortalController::class, 'storeLeave'])->name('leave.store');

    // Payslip Routes
    Route::get('/payslips', [EmployeePortalController::class, 'payslips'])->name('payslips');
    Route::get('/payslips/{payslip}', [EmployeePortalController::class, 'showPayslip'])->name('payslips.show');
    Route::get('/payslips/{payslip}/download', [EmployeePortalController::class, 'downloadPayslip'])->name('payslips.download');
});

// Manager Portal Routes
Route::prefix('portal/manager')->name('portal.manager.')->group(function () {
    Route::get('/dashboard', [ManagerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/team-attendance', [ManagerPortalController::class, 'teamAttendance'])->name('team-attendance');
    Route::get('/reports', [ManagerPortalController::class, 'reports'])->name('reports');

    // Leave Approval Routes
    Route::post('/leave/{leaveRequest}/approve', [ManagerPortalController::class, 'approveLeave'])->name('leave.approve');
    Route::post('/leave/{leaveRequest}/reject', [ManagerPortalController::class, 'rejectLeave'])->name('leave.reject');
});
