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
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained('organizations');
            $table->foreignId('financial_year_id')->nullable()->after('organization_id')->constrained('financial_years');
            $table->index(['organization_id', 'financial_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['financial_year_id']);
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'financial_year_id']);
            $table->dropColumn(['financial_year_id', 'organization_id']);
        });
    }
};
