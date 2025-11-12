<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display the attendance dashboard.
     */
    public function dashboard()
    {
        return view('attendance.dashboard');
    }

    /**
     * Sync biometric data.
     */
    public function syncBiometricData()
    {
        // Mock sync process
        return back()->with('success', 'Biometric data synchronized successfully');
    }

    /**
     * Regularize attendance time.
     */
    public function regularizeTime(Request $request)
    {
        // Mock regularization process
        return response()->json(['message' => 'Time regularized successfully']);
    }

    /**
     * Apply leave for absent employee.
     */
    public function applyLeave(Request $request)
    {
        // Mock leave application process
        return response()->json(['message' => 'Leave applied successfully']);
    }

    /**
     * Export attendance data for payroll.
     */
    public function exportForPayroll()
    {
        // Mock export process
        return response()->download(storage_path('app/attendance-export.csv'));
    }
}
