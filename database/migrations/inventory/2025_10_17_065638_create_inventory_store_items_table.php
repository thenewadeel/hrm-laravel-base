<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_store_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('inventory_stores')->nullOnDelete();
            $table->foreignId('item_id')->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('head_id')->constrained('inventory_heads')->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['store_id', 'item_id']);
            $table->index(['store_id', 'quantity']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_store_items');
    }
};
