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
        Schema::create('tax_filings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('tax_jurisdiction_id')->constrained()->onDelete('cascade');
            $table->foreignId('tax_rate_id')->constrained()->onDelete('cascade');
            $table->string('filing_number')->unique();
            $table->string('filing_type'); // monthly, quarterly, annual, special
            $table->date('period_start');
            $table->date('period_end');
            $table->date('filing_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'filed', 'accepted', 'rejected', 'paid', 'overdue']);
            $table->decimal('total_tax_collected', 15, 2)->default(0);
            $table->decimal('total_tax_paid', 15, 2)->default(0);
            $table->decimal('tax_due', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->string('confirmation_number')->nullable();
            $table->text('filing_notes')->nullable();
            $table->json('filing_data')->nullable(); // Detailed filing information
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'tax_jurisdiction_id', 'status']);
            $table->index(['organization_id', 'filing_type', 'period_end']);
            $table->index(['organization_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_filings');
    }
};
