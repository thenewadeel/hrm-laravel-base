<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeIncrement extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'approved_by',
        'increment_type',
        'increment_value',
        'previous_salary',
        'new_salary',
        'effective_date',
        'reason',
        'status',
        'approval_notes',
        'approved_at',
    ];

    protected $casts = [
        'increment_value' => 'decimal:2',
        'previous_salary' => 'decimal:2',
        'new_salary' => 'decimal:2',
        'effective_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeImplemented($query)
    {
        return $query->where('status', 'implemented');
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('effective_date', '<=', $date);
    }

    /**
     * Calculate increment amount
     */
    public function getIncrementAmountAttribute()
    {
        return $this->new_salary - $this->previous_salary;
    }

    /**
     * Get increment percentage
     */
    public function getIncrementPercentageAttribute()
    {
        if ($this->previous_salary == 0) {
            return 0;
        }

        return round(($this->increment_amount / $this->previous_salary) * 100, 2);
    }

    /**
     * Approve the increment
     */
    public function approve(User $approver, ?string $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approval_notes' => $notes,
            'approved_at' => now(),
        ]);
    }

    /**
     * Implement the increment
     */
    public function implement()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved increments can be implemented');
        }

        $this->employee->update(['basic_salary' => $this->new_salary]);

        $this->update(['status' => 'implemented']);

        return true;
    }
}
