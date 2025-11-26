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
        Schema::table('tax_rates', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tax_jurisdictions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tax_exemptions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_rates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tax_jurisdictions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tax_exemptions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
