<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'approved_by',
        'loan_reference',
        'loan_type',
        'principal_amount',
        'interest_rate',
        'repayment_period_months',
        'monthly_installment',
        'total_interest',
        'total_repayment',
        'balance_amount',
        'installments_paid',
        'disbursement_date',
        'first_payment_date',
        'maturity_date',
        'purpose',
        'status',
        'approval_notes',
        'approved_at',
        'disbursed_at',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_repayment' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'installments_paid' => 'integer',
        'disbursement_date' => 'date',
        'first_payment_date' => 'date',
        'maturity_date' => 'date',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
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
     * Calculate monthly installment
     */
    public static function calculateMonthlyInstallment($principal, $annualRate, $months)
    {
        $monthlyRate = $annualRate / 12 / 100;

        if ($monthlyRate == 0) {
            return $principal / $months;
        }

        return $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) /
               (pow(1 + $monthlyRate, $months) - 1);
    }

    /**
     * Calculate total interest
     */
    public static function calculateTotalInterest($principal, $annualRate, $months)
    {
        $monthlyInstallment = self::calculateMonthlyInstallment($principal, $annualRate, $months);

        return ($monthlyInstallment * $months) - $principal;
    }

    /**
     * Approve the loan
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
     * Disburse the loan
     */
    public function disburse()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved loans can be disbursed');
        }

        $this->update([
            'status' => 'active',
            'disbursed_at' => now(),
            'balance_amount' => $this->total_repayment,
        ]);

        return true;
    }

    /**
     * Process monthly payment
     */
    public function processPayment()
    {
        if ($this->status !== 'active') {
            throw new \Exception('Only active loans can accept payments');
        }

        if ($this->balance_amount <= 0) {
            $this->update(['status' => 'completed']);

            return 0;
        }

        $paymentAmount = min($this->monthly_installment, $this->balance_amount);

        $this->increment('installments_paid');
        $this->decrement('balance_amount', $paymentAmount);

        if ($this->balance_amount <= 0) {
            $this->update(['status' => 'completed']);
        }

        return $paymentAmount;
    }

    /**
     * Get remaining installments
     */
    public function getRemainingInstallmentsAttribute()
    {
        return $this->repayment_period_months - $this->installments_paid;
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'active' &&
               $this->maturity_date < now() &&
               $this->balance_amount > 0;
    }
}
