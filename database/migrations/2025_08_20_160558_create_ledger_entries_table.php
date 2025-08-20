<?php
// database/migrations/xxxx_create_ledger_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->foreignId('chart_of_account_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2)->unsigned(); // <-- ADD UNSIGNED HERE
            $table->text('description');

            // Laravel 12 proper way - nullable polymorphic relationship
            $table->nullableMorphs('transactionable');

            $table->timestamps();
        });

        // For databases that support check constraints (not SQLite)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE ledger_entries ADD CONSTRAINT amount_positive CHECK (amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
