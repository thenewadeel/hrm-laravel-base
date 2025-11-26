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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_statement_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('transaction_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description');
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->enum('status', ['pending', 'cleared', 'reconciled'])->default('pending');
            $table->enum('reconciliation_status', ['unmatched', 'matched', 'partially_matched'])->default('unmatched');
            $table->foreignId('matched_ledger_entry_id')->nullable()->constrained('ledger_entries')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'bank_account_id', 'transaction_date']);
            $table->index(['organization_id', 'bank_account_id', 'status']);
            $table->index(['organization_id', 'bank_account_id', 'reconciliation_status']);
            $table->index(['bank_statement_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
