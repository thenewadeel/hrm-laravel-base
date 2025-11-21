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
        Schema::create('depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');

            $table->date('depreciation_date');
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation_before', 15, 2);
            $table->decimal('accumulated_depreciation_after', 15, 2);
            $table->decimal('book_value_before', 15, 2);
            $table->decimal('book_value_after', 15, 2);

            $table->string('depreciation_method');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['organization_id', 'fixed_asset_id']);
            $table->index(['organization_id', 'depreciation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciations');
    }
};
