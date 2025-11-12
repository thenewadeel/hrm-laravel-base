<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adds the foreign key for the active organization/tenant
            $table->foreignId('current_organization_id')
                ->nullable()
                ->constrained('organizations') // Constrain to the organizations table
                ->after('password');
            $table->index('current_organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_organization_id']);
            $table->dropIndex(['current_organization_id']); // Drop the index first
            $table->dropColumn('current_organization_id');
        });
    }
};
