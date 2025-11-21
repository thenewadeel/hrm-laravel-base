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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('tax_jurisdiction_id')->nullable()->constrained('tax_jurisdictions')->onDelete('set null');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['sales', 'purchase', 'withholding', 'income', 'vat', 'service', 'other']);
            $table->decimal('rate', 8, 4); // Tax rate with 4 decimal places for precision
            $table->boolean('is_compound')->default(false); // Compound tax (applied on amount after other taxes)
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->json('applicable_accounts')->nullable(); // Chart of account IDs this tax applies to
            $table->string('gl_account_code')->nullable(); // GL account for tax liability
            $table->timestamps();

            $table->index(['organization_id', 'type', 'is_active']);
            $table->index(['organization_id', 'effective_date']);
            $table->unique(['organization_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
