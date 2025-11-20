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
        Schema::create('cash_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('voucher_number')->unique();
            $table->date('date');
            $table->string('paid_to');
            $table->decimal('amount', 15, 2);
            $table->foreignId('cash_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('debit_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_id', 'voucher_number']);
            $table->index(['organization_id', 'date']);
            $table->index('voucher_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_payments');
    }
};
