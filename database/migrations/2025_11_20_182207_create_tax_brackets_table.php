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
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('min_income', 12, 2);
            $table->decimal('max_income', 12, 2)->nullable(); // null for highest bracket
            $table->decimal('rate', 5, 2); // Tax rate as percentage
            $table->decimal('base_tax', 12, 2)->default(0); // Base tax amount for this bracket
            $table->decimal('exemption_amount', 12, 2)->default(0); // Tax exemption amount
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_brackets');
    }
};
