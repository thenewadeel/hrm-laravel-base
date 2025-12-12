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
        Schema::create('fee_distribution_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('fee_type'); // subscription, late_fee, penalty, etc.
            $table->enum('rule_type', ['percentage', 'fixed', 'priority'])->default('percentage');
            $table->json('conditions')->nullable(); // Conditional rules based on amount, member category, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // For rule execution order
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'fee_type', 'is_active']);
            $table->index(['organization_id', 'priority']);
        });

        Schema::create('fee_distribution_rule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_distribution_rule_id')->constrained()->onDelete('cascade');
            $table->foreignId('chart_of_account_id')->constrained()->onDelete('cascade');
            $table->enum('distribution_type', ['percentage', 'fixed']);
            $table->decimal('percentage', 5, 2)->nullable(); // For percentage-based distribution
            $table->decimal('fixed_amount', 15, 2)->nullable(); // For fixed amount distribution
            $table->integer('priority')->default(0); // For item execution order within rule
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['fee_distribution_rule_id', 'priority']);
        });

        Schema::create('fee_distribution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_fee_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('fee_distribution_rule_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('total_amount', 15, 2);
            $table->json('distribution_breakdown'); // Detailed breakdown of how amount was distributed
            $table->string('status'); // success, failed, partial
            $table->text('error_message')->nullable();
            $table->timestamp('distributed_at');
            $table->timestamps();

            $table->index(['organization_id', 'distributed_at']);
            $table->index(['member_fee_id']);
            $table->index(['journal_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_distribution_logs');
        Schema::dropIfExists('fee_distribution_rule_items');
        Schema::dropIfExists('fee_distribution_rules');
    }
};
