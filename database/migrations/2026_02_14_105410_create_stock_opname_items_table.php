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
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_opname_id');
            $table->integer('ingredient_id');
            $table->integer('batch_id')->nullable();
            $table->decimal('system_qty', 15, 4);
            $table->decimal('physical_qty', 15, 4);
            $table->decimal('diff_qty', 15, 4);
            $table->decimal('cost_per_unit', 15, 4);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
