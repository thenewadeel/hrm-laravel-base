<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('inventory_stores')->nullOnDelete();
            // $table->foreignId('inventory_id');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            // $table->foreignId('staff_id');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // ['incoming', 'outgoing', 'adjustment']
            // $table->enum('type', ["UP", "DOWN"])->default('UP');
            $table->string('status')->default('draft'); //['draft', 'finalized', 'cancelled']
            $table->string('reference')->unique();
            // $table->foreignId('vendor_id');
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamp('finalized_at')->nullable();
            // $table->timestamp('applied_on')->nullable()->default(null);


            $table->softDeletes();
            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index(['type', 'transaction_date']);
            $table->index(['reference']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
