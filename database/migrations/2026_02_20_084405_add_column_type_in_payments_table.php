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
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('order_id', 'payable_id');
            $table->string( 'payable_type')->after('id')->default('App\\\Models\\\Order');
            $table->string('type')->nullable()->after('cashier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('payable_id', 'order_id');
            $table->dropColumn('payable_type');
            $table->dropColumn('type');
        });
    }
};
