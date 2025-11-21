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
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->morphs('calculable'); // Voucher, Invoice, PayrollSlip, etc.
            $table->foreignId('tax_rate_id')->constrained()->onDelete('cascade');
            $table->foreignId('tax_exemption_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('base_amount', 15, 2); // Amount before tax
            $table->decimal('taxable_amount', 15, 2); // Amount after exemptions
            $table->decimal('tax_rate', 8, 4); // Actual rate applied
            $table->decimal('tax_amount', 15, 2); // Calculated tax amount
            $table->date('calculation_date');
            $table->string('calculation_method'); // percentage, fixed, bracket
            $table->json('calculation_details')->nullable(); // Detailed breakdown
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'calculable_type', 'calculable_id']);
            $table->index(['organization_id', 'tax_rate_id']);
            $table->index(['organization_id', 'calculation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};
