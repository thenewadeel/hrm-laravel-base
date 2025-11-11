<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use BelongsToOrganization;
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollEntries()
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function organizationMemberships()
    {
        return $this->hasMany(OrganizationUser::class);
    }
    /**
     * Get today's attendance record
     */
    public function todayAttendance()
    {
        return $this->hasOne(AttendanceRecord::class)
            ->whereDate('record_date', today());
    }

    /**
     * Get pending leave requests
     */
    public function pendingLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class)
            ->where('status', 'pending');
    }

    /**
     * Check if user is clocked in today
     */
    public function isClockedIn()
    {
        $attendance = $this->todayAttendance;
        return $attendance && $attendance->punch_in && !$attendance->punch_out;
    }
}
