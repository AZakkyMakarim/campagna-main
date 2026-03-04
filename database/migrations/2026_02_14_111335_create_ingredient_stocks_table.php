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
        Schema::create('ingredient_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('outlet_id');
            $table->integer('ingredient_id');
            $table->decimal('qty', 18, 4)->default(0);
            $table->decimal('avg_cost', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['outlet_id', 'ingredient_id']);
            $table->index('ingredient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_stocks');
    }
};
