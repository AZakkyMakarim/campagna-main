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
        \Illuminate\Support\Facades\DB::statement("
            UPDATE printers
            SET section = JSON_ARRAY(section)
            WHERE section IS NOT NULL
        ");

        Schema::table('printers', function (Blueprint $table) {
            $table->json('section')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("
            UPDATE printers
            SET section = JSON_UNQUOTE(JSON_EXTRACT(section, '$[0]'))
            WHERE section IS NOT NULL
        ");

        Schema::table('printers', function (Blueprint $table) {
            $table->string('section')->change();
        });
    }
};
