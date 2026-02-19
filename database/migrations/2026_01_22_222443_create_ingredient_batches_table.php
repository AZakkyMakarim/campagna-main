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
        Schema::create('ingredient_batches', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->integer('purchase_id')->nullable();
            $table->integer('ingredient_id');
            $table->integer('vendor_id')->nullable();
            $table->integer('outlet_id');
            $table->decimal('qty_in')->default(0);
            $table->decimal('qty_remaining')->default(0);
            $table->decimal('cost_per_unit')->default(0);
            $table->string('source');
            $table->dateTime('received_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_batches');
    }
};
