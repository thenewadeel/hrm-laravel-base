<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEntry extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'period', // Format: YYYY-MM
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'overtime_pay',
        'bonus',
        'gross_pay',
        'tax_deduction',
        'insurance_deduction',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'status', //, ['draft', 'processed', 'paid', 'cancelled'])->default('draft');
        'paid_at'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'insurance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'paid_at' => 'datetime'
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
     * Calculate gross pay automatically
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->gross_pay = $model->basic_salary
                + $model->housing_allowance
                + $model->transport_allowance
                + $model->overtime_pay
                + $model->bonus;

            $model->total_deductions = $model->tax_deduction
                + $model->insurance_deduction
                + $model->other_deductions;

            $model->net_pay = $model->gross_pay - $model->total_deductions;
        });
    }

    /**
     * Scope for specific period
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope for paid entries
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Get payslip filename
     */
    public function getPayslipFilenameAttribute()
    {
        return "payslip-{$this->user->name}-{$this->period}.pdf";
    }
}
