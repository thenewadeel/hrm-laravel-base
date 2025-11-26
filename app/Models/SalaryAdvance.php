<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'approved_by',
        'advance_reference',
        'amount',
        'balance_amount',
        'repayment_months',
        'monthly_deduction',
        'months_repaid',
        'request_date',
        'approval_date',
        'first_deduction_month',
        'reason',
        'status',
        'approval_notes',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'months_repaid' => 'integer',
        'request_date' => 'date',
        'approval_date' => 'date',
        'first_deduction_month' => 'date',
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
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Approve the advance
     */
    public function approve(User $approver, ?string $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approval_notes' => $notes,
            'approved_at' => now(),
            'approval_date' => now(),
        ]);
    }

    /**
     * Activate the advance for repayment
     */
    public function activate()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved advances can be activated');
        }

        $this->update([
            'status' => 'active',
            'balance_amount' => $this->amount,
            'monthly_deduction' => $this->amount / $this->repayment_months,
        ]);

        return true;
    }

    /**
     * Process monthly deduction
     */
    public function processMonthlyDeduction()
    {
        if ($this->status !== 'active') {
            throw new \Exception('Only active advances can process deductions');
        }

        if ($this->balance_amount <= 0) {
            $this->update(['status' => 'completed']);

            return 0;
        }

        $deductionAmount = min($this->monthly_deduction, $this->balance_amount);

        $this->increment('months_repaid');
        $this->decrement('balance_amount', $deductionAmount);

        if ($this->balance_amount <= 0) {
            $this->update(['status' => 'completed']);
        }

        return $deductionAmount;
    }

    /**
     * Get remaining months
     */
    public function getRemainingMonthsAttribute()
    {
        return $this->repayment_months - $this->months_repaid;
    }

    /**
     * Check if advance should be deducted this month
     */
    public function shouldDeductThisMonth($payrollPeriod)
    {
        if ($this->status !== 'active' || $this->balance_amount <= 0) {
            return false;
        }

        $firstDeduction = \Carbon\Carbon::parse($this->first_deduction_month);
        $currentPeriod = \Carbon\Carbon::parse($payrollPeriod);

        return $currentPeriod >= $firstDeduction &&
               $this->months_repaid < $this->repayment_months;
    }
}
