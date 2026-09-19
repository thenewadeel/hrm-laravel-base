<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['reference_number']);
            $table->unique(['organization_id', 'reference_number']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->unique(['organization_id', 'sku']);
        });
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'reference_number']);
            $table->unique(['reference_number']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'sku']);
            $table->unique(['sku']);
        });
    }
};
