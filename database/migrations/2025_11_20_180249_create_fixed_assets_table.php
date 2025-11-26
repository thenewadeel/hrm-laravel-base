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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixed_asset_category_id')->constrained()->onDelete('restrict');
            $table->foreignId('chart_of_account_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('accumulated_depreciation_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            $table->string('asset_tag')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->string('department')->nullable();
            $table->string('assigned_to')->nullable();

            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->integer('useful_life_years');
            $table->string('depreciation_method')->default('straight_line');

            $table->decimal('current_book_value', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->date('last_depreciation_date')->nullable();

            $table->string('status')->default('active'); // active, inactive, disposed, under_maintenance
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'fixed_asset_category_id']);
            $table->index(['organization_id', 'location']);
            $table->unique(['organization_id', 'asset_tag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
