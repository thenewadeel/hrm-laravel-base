<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'leave_type',
        //, ['sick', 'vacation', 'personal', 'emergency', 'maternity', 'paternity']);
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        //, ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'applied_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'total_days' => 'integer'
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function organization()
    // {
    //     return $this->belongsTo(Organization::class);
    // }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Calculate total days automatically
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->total_days = $model->start_date->diffInDays($model->end_date) + 1;
        });

        static::updating(function ($model) {
            if ($model->isDirty(['start_date', 'end_date'])) {
                $model->total_days = $model->start_date->diffInDays($model->end_date) + 1;
            }
        });
    }

    /**
     * Check if leave request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if leave request is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
