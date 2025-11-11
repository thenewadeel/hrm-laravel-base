<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'user_id',
        'organization_id',
        'record_date',
        'punch_in',
        'punch_out',
        'total_hours',
        'status',
        //['present', 'absent', 'late', 'leave', 'missed_punch', 'pending_regularization'])->default('present');
        'biometric_id',
        'device_serial_no',
        'notes'
    ];
    protected $casts = [
        'record_date' => 'date',
        'punch_in' => 'datetime',
        'punch_out' => 'datetime',
        'total_hours' => 'decimal:2'
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
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
