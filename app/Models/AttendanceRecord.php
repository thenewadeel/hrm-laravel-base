<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'record_date',
        'punch_in',
        'punch_out',
        'total_hours',
        'status',
        //['present', 'absent', 'late', 'leave', 'missed_punch', 'pending_regularization'])->default('present');
        'biometric_id',
        'device_serial_no',
        'late_minutes',
        'overtime_minutes',
        'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'punch_in' => 'datetime',
        'punch_out' => 'datetime',
        'total_hours' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('record_date', $date);
    }
    /**
     * Scope for current organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    /**
     * Check if record is for today
     */
    public function isToday()
    {
        return $this->record_date->isToday();
    }

    /**
     * Calculate work hours
     */
    public function calculateWorkHours()
    {
        if ($this->punch_in && $this->punch_out) {
            return $this->punch_out->diffInHours($this->punch_in);
        }
        return 0;
    }
}
