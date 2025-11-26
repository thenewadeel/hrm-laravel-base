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
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');

            $table->date('transfer_date');
            $table->string('from_location');
            $table->string('to_location');
            $table->string('from_department')->nullable();
            $table->string('to_department')->nullable();
            $table->string('from_assigned_to')->nullable();
            $table->string('to_assigned_to')->nullable();

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['organization_id', 'transfer_date']);
            $table->index(['organization_id', 'fixed_asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};
