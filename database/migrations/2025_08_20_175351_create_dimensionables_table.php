<?php
// database/migrations/xxxx_create_dimensionables_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimensionables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained()->onDelete('cascade');
            $table->morphs('dimensionable'); // This will link to ledger_entries, budgets, etc.
            $table->timestamps();

            // Optional: Add unique constraint to prevent duplicate assignments
            $table->unique(['dimension_id', 'dimensionable_id', 'dimensionable_type'], 'dimensionable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimensionables');
    }
};
