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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('outlet_id');
            $table->integer('cashier_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('code');
            $table->string('queue_number');
            $table->string('type');
            $table->string('channel');
            $table->string('status');
            $table->string('payment_status');
            $table->float('sub_total')->default(0);
            $table->float('adjustment_total')->default(0);
            $table->float('grand_total')->default(0);
            $table->text('note')->nullable();
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
