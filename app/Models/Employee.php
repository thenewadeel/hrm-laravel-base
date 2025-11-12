<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, BelongsToOrganization, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'organization_id',
        'organization_unit_id',
        'first_name',
        'last_name',
        'biometric_id',
        'middle_name',
        'date_of_birth',
        'gender',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'photo',
        'is_active',
        'is_admin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
    ];

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
        // Assuming OrganizationUser is the pivot model for a many-to-many relationship
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
    /**
     * Scope for employees with user accounts
     */
    public function scopeWithUserAccount($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope for employees without user accounts
     */
    public function scopeWithoutUserAccount($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope for active employees in organization
     */
    public function scopeActiveInOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId)
            ->where('is_active', true);
    }

    /**
     * Get associated OrganizationUser for login access
     */
    public function organizationUser()
    {
        return $this->hasOne(OrganizationUser::class, 'user_id', 'user_id')
            ->where('organization_id', $this->organization_id);
    }
    /**
     * Get the user associated with the employee (for login access)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Check if employee has login access
     */
    public function hasLoginAccess(): bool
    {
        return !is_null($this->user_id) && $this->organizationUser()->exists();
    }

    /**
     * Get login roles (if any)
     */
    public function getLoginRoles(): array
    {
        return $this->organizationUser->roles ?? [];
    }
}
