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
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_statement_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('reconciliation_date');
            $table->decimal('statement_balance', 15, 2);
            $table->decimal('book_balance', 15, 2);
            $table->decimal('difference', 15, 2);
            $table->decimal('outstanding_deposits', 15, 2)->default(0);
            $table->decimal('outstanding_withdrawals', 15, 2)->default(0);
            $table->integer('transactions_reconciled')->default(0);
            $table->integer('total_transactions')->default(0);
            $table->enum('status', ['in_progress', 'completed', 'failed'])->default('in_progress');
            $table->text('notes')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'bank_account_id', 'reconciliation_date']);
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};
