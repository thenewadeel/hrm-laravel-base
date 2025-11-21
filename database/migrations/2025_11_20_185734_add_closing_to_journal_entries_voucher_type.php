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
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->enum('voucher_type', ['GENERAL', 'SALES', 'PURCHASE', 'SALARY', 'EXPENSE', 'CLOSING'])->default('GENERAL')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->enum('voucher_type', ['GENERAL', 'SALES', 'PURCHASE', 'SALARY', 'EXPENSE'])->default('GENERAL')->change();
        });
    }
};
