<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'min_income',
        'max_income',
        'rate',
        'base_tax',
        'exemption_amount',
        'is_active',
        'effective_date',
        'end_date',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'rate' => 'decimal:2',
        'base_tax' => 'decimal:2',
        'exemption_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

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
     * Calculate tax for given taxable income
     */
    public function calculateTax($taxableIncome)
    {
        if ($taxableIncome <= $this->exemption_amount) {
            return 0;
        }

        $taxableAmount = $taxableIncome - $this->exemption_amount;

        if ($taxableAmount <= $this->min_income) {
            return 0;
        }

        $excessIncome = $taxableAmount - $this->min_income;

        return $this->base_tax + ($excessIncome * ($this->rate / 100));
    }

    /**
     * Check if income falls within this bracket
     */
    public function isIncomeInRange($income)
    {
        return $income >= $this->min_income
               && ($this->max_income === null || $income <= $this->max_income);
    }
}
