<?php
// database/migrations/xxxx_create_sequences_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->string('name')->primary(); // e.g., 'journal_entry_ref', 'invoice_number'
            $table->unsignedBigInteger('last_value')->default(0);
            $table->unsignedBigInteger('increment_by')->default(1);
            $table->string('prefix')->nullable(); // e.g., 'JE-', 'INV-'
            $table->string('suffix')->nullable();
            $table->integer('pad_length')->default(0); // e.g., 6 → JE-000001
            $table->timestamps();
        });

        // Pre-populate with required sequences
        DB::table('sequences')->insert([
            ['name' => 'journal_entry_ref', 'prefix' => 'JE-', 'pad_length' => 6, 'last_value' => 0],
            ['name' => 'invoice_number', 'prefix' => 'INV-', 'pad_length' => 6, 'last_value' => 0],
            ['name' => 'purchase_order', 'prefix' => 'PO-', 'pad_length' => 6, 'last_value' => 0],
            ['name' => 'payment_voucher', 'prefix' => 'PV-', 'pad_length' => 6, 'last_value' => 0],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
