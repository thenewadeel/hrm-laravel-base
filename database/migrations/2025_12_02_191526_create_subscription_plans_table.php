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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->enum('plan_type', ['individual', 'family', 'corporate', 'sports', 'social']);
            $table->enum('billing_frequency', ['monthly', 'quarterly', 'semi_annually', 'annually']);
            $table->decimal('amount', 10, 2);
            $table->integer('family_members_included')->default(0);
            $table->decimal('additional_family_member_fee', 10, 2)->default(0.00);
            $table->json('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'plan_type']);
            $table->index(['organization_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
