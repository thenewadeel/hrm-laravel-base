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
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('advance_reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_amount', 12, 2);
            $table->integer('repayment_months');
            $table->decimal('monthly_deduction', 12, 2);
            $table->integer('months_repaid')->default(0);
            $table->date('request_date');
            $table->date('approval_date')->nullable();
            $table->date('first_deduction_month');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'active', 'completed', 'rejected'
            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index('advance_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};
