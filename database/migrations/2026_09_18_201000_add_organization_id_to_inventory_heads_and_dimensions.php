<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'inventory_heads' => 'inventory_heads',
        'dimensions' => 'dimensions',
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'organization_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('organization_id')->nullable();
                });

                $fallback = DB::table('organizations')->value('id');

                if ($fallback) {
                    DB::table($table)->update(['organization_id' => $fallback]);
                }

                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('organization_id')->nullable(false)->change();
                    $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
                    $table->index('organization_id');
                });
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};