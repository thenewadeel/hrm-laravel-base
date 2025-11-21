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
        Schema::create('tax_jurisdictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['country', 'state', 'province', 'city', 'county', 'municipality', 'other']);
            $table->string('parent_code')->nullable(); // For hierarchical jurisdictions
            $table->string('tax_id_number')->nullable(); // Tax registration number
            $table->boolean('is_active')->default(true);
            $table->text('address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('filing_requirements')->nullable(); // Filing frequency and requirements
            $table->timestamps();

            $table->index(['organization_id', 'type', 'is_active']);
            $table->unique(['organization_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_jurisdictions');
    }
};
