<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('inventory_transactions', 'organization_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            });
        }

        DB::statement(<<<'SQL'
            UPDATE inventory_transactions
            SET organization_id = (
                SELECT ou.organization_id
                FROM inventory_stores s
                JOIN organization_units ou ON ou.id = s.organization_unit_id
                WHERE s.id = inventory_transactions.store_id
            )
            WHERE organization_id IS NULL
SQL);

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->index('organization_id');
        });
    }

    public function down()
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
