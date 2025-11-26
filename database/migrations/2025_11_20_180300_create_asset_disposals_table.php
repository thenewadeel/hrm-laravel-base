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
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');

            $table->date('disposal_date');
            $table->string('disposal_type'); // sale, scrap, write_off, donation
            $table->decimal('disposal_value', 15, 2)->default(0);
            $table->decimal('proceeds', 15, 2)->default(0);
            $table->decimal('gain_loss', 15, 2)->default(0);

            $table->decimal('book_value_at_disposal', 15, 2);
            $table->decimal('accumulated_depreciation_at_disposal', 15, 2);

            $table->string('disposed_to')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['organization_id', 'disposal_date']);
            $table->index(['organization_id', 'disposal_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
