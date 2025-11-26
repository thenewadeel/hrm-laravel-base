<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDeduction extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'deduction_type_id',
        'amount',
        'percentage',
        'effective_date',
        'end_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'effective_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('effective_date', '<=', $date);
    }

    /**
     * Check if deduction is currently active
     */
    public function isCurrentlyActive()
    {
        return $this->is_active
               && $this->effective_date <= now()
               && ($this->end_date === null || $this->end_date >= now());
    }

    /**
     * Get calculated amount for a specific period
     */
    public function getCalculatedAmount($basicSalary = null)
    {
        if ($this->amount) {
            return $this->amount;
        }

        if ($this->percentage && $basicSalary) {
            return ($this->percentage / 100) * $basicSalary;
        }

        return 0;
    }
}
