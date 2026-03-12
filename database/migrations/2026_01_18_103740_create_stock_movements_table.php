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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->morphs('movementable');
            $table->integer('business_id');
            $table->integer('ingredient_id');
            $table->integer('batch_id');
            $table->integer('outlet_id');
//            $table->string('code')->nullable();
            $table->string('type');
            $table->decimal('qty')->default(0);
            $table->decimal('cost_per_unit')->default(0);
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
