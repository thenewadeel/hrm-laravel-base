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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('supplier')->nullable()->after('notes');
            $table->string('recipient')->nullable()->after('supplier');
            $table->string('adjustment_reason')->nullable()->after('recipient');
            $table->foreignId('to_store_id')->nullable()->after('store_id')->constrained('inventory_stores')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['to_store_id']);
            $table->dropColumn(['supplier', 'recipient', 'adjustment_reason', 'to_store_id']);
        });
    }
};
