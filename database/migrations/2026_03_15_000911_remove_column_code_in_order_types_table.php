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
        Schema::table('order_types', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table) {
            $table->string('code')->after('business_id')->nullable();
            $table->string('type')->after('name')->nullable();
        });
    }
};
