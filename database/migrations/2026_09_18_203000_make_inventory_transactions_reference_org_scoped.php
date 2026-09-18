<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->unique(['organization_id', 'reference']);
        });
    }

    public function down()
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'reference']);
            $table->unique(['reference']);
        });
    }
};