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
        Schema::create('member_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('card_number')->unique();
            $table->string('card_type')->default('standard'); // standard, premium, family, corporate
            $table->string('template')->default('modern'); // modern, classic, corporate, family
            $table->string('status')->default('active'); // active, expired, lost, damaged, reprinted
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('qr_code_path')->nullable();
            $table->string('barcode_path')->nullable();
            $table->json('design_settings')->nullable(); // Store custom design settings
            $table->text('notes')->nullable();
            $table->integer('print_count')->default(0);
            $table->timestamp('last_printed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'member_id']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'expiry_date']);
            $table->index(['card_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_cards');
    }
};
