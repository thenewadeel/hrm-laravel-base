<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('inventory_transactions')->onDelete('cascade');
            // $table->foreignId('inventory_transaction_id');
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            // $table->foreignId('item_id');
            $table->integer('quantity');
            // $table->integer('qty');
            $table->integer('unit_price')->comment('in cents');
            // $table->decimal('rate', 8, 2);
            $table->text('notes')->nullable();
            // $table->text('notes')->nullable()->default('NULL');


            $table->timestamps();

            $table->index(['transaction_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_transaction_items');
    }
};
