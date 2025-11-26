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
        Schema::create('tax_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->morphs('exemptible'); // Customer, Vendor, Employee, or other models
            $table->foreignId('tax_rate_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('exemption_type'); // resale, charitable, government, manufacturing, etc.
            $table->decimal('exemption_percentage', 5, 2)->default(100); // Percentage of exemption
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('reason')->nullable();
            $table->json('applicable_taxes')->nullable(); // Tax types this exemption applies to
            $table->string('issuing_authority')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'exemptible_type', 'exemptible_id']);
            $table->index(['organization_id', 'certificate_number']);
            $table->index(['organization_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_exemptions');
    }
};
