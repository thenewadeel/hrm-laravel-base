<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kg, meter, etc.
            $table->decimal('cost_price', 10, 2)->nullable()->comment('in cents');
            $table->decimal('selling_price', 10, 2)->nullable()->comment('in cents');
            $table->integer('reorder_level')->default(0);
            $table->boolean('is_active')->default(true);

            $table->foreignId('head_id')->nullable()->constrained('inventory_heads')->nullOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            // $table->string('code')->nullable();
            //    $table->decimal('balance', 8, 2);
            // $table->foreignId('item_id');
            // $table->foreignId('inventory_id');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['sku']);
            $table->index(['category']);
            $table->index(['is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
};
