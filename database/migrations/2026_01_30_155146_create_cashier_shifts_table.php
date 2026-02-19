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
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('outlet_id');
            $table->integer('user_id');
            $table->string('shift_code');
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->float('opening_cash')->nullable();
            $table->float('opening_petty_cash')->nullable();
            $table->float('expected_cash')->nullable();
            $table->float('expected_petty_cash')->nullable();
            $table->float('actual_cash')->nullable();
            $table->float('actual_petty_cash')->nullable();
            $table->float('cash_difference')->nullable();
            $table->float('petty_cash_difference')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};
