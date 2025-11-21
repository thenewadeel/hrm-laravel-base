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
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');

            $table->date('maintenance_date');
            $table->string('maintenance_type'); // routine, repair, upgrade
            $table->text('description');
            $table->decimal('cost', 15, 2)->default(0);

            $table->string('performed_by')->nullable();
            $table->string('vendor')->nullable();
            $table->text('notes')->nullable();

            $table->date('next_maintenance_date')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['organization_id', 'fixed_asset_id']);
            $table->index(['organization_id', 'maintenance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};
