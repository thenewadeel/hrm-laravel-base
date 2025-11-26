<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('loan_reference')->unique();
            $table->string('loan_type');
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_rate', 5, 2); // Annual interest rate
            $table->integer('repayment_period_months'); // Total repayment period in months
            $table->decimal('monthly_installment', 12, 2);
            $table->decimal('total_interest', 12, 2);
            $table->decimal('total_repayment', 12, 2);
            $table->decimal('balance_amount', 12, 2);
            $table->integer('installments_paid')->default(0);
            $table->date('disbursement_date');
            $table->date('first_payment_date');
            $table->date('maturity_date');
            $table->text('purpose')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'disbursed', 'active', 'completed', 'defaulted'
            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index('loan_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};
