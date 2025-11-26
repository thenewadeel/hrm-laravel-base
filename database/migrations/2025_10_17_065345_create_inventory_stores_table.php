<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->nullable()->constrained('organization_units');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_unit_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_stores');
    }
};
