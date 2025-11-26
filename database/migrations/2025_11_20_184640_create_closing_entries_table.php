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
        Schema::create('closing_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('financial_year_id')->constrained('financial_years');
            $table->foreignId('journal_entry_id')->constrained('journal_entries');
            $table->enum('type', ['revenue_closure', 'expense_closure', 'profit_transfer']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['organization_id', 'financial_year_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closing_entries');
    }
};
