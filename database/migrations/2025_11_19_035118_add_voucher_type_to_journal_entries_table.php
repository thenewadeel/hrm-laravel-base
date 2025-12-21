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
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->enum('voucher_type', ['GENERAL', 'SALES', 'PURCHASE', 'SALARY', 'EXPENSE'])->default('GENERAL')->after('description');
            $table->foreignId('customer_id')->nullable()->after('voucher_type');
            $table->foreignId('vendor_id')->nullable()->after('customer_id');
            $table->decimal('total_amount', 15, 2)->nullable()->after('vendor_id');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('total_amount');
            $table->string('invoice_number')->nullable()->after('tax_amount');
            $table->date('due_date')->nullable()->after('invoice_number');
        });

        // Add foreign key constraints separately if tables exist
        if (Schema::hasTable('customers')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->foreign('customer_id')->references('id')->on('customers');
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->foreign('vendor_id')->references('id')->on('vendors');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn([
                'voucher_type',
                'customer_id',
                'vendor_id',
                'total_amount',
                'tax_amount',
                'invoice_number',
                'due_date',
            ]);
        });
    }
};
