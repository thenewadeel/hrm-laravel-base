<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductionType extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'calculation_type',
        'default_value',
        'is_tax_exempt',
        'is_recurring',
        'is_active',
        'account_code',
    ];

    protected $casts = [
        'default_value' => 'decimal:2',
        'is_tax_exempt' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function employeeDeductions()
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTaxExempt($query)
    {
        return $query->where('is_tax_exempt', true);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Calculate deduction amount for an employee
     */
    public function calculateAmount(Employee $employee, $basicSalary = null)
    {
        $basicSalary = $basicSalary ?? $employee->basic_salary ?? 0;

        return match ($this->calculation_type) {
            'fixed_amount' => $this->default_value,
            'percentage_of_basic' => ($this->default_value / 100) * $basicSalary,
            'percentage_of_gross' => ($this->default_value / 100) * ($basicSalary * 1.3), // Rough gross estimate
            default => 0,
        };
    }
}
