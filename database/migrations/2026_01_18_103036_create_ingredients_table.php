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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('outlet_id')->nullable();
            $table->string('name');
            $table->string('code');
            $table->string('type');
            $table->integer('base_unit_id');
            $table->decimal('stock')->default(0);
            $table->decimal('min_stock');
            $table->integer('is_active')->default(1);
            $table->integer('is_sellable')->default(0);
            $table->integer('is_unlimited_stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
