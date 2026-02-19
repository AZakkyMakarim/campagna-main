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
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->string('code');
            $table->string('name');
            $table->string('type');
            $table->string('address')->nullable();
            $table->time('opening_hours');
            $table->time('closing_hours');
            $table->float('initial_cash')->default(0);
            $table->float('petty_cash')->default(0);
            $table->integer('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
