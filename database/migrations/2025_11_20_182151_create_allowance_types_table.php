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
        Schema::create('allowance_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('calculation_type', ['fixed_amount', 'percentage_of_basic', 'percentage_of_gross']);
            $table->decimal('default_value', 12, 2)->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_recurring')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('account_code')->nullable(); // For accounting integration
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->unique(['organization_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowance_types');
    }
};
