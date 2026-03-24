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
        $outlets = \App\Models\Outlet::all();
        foreach ($outlets as $outlet) {
            \App\Models\Setting::firstOrCreate(
                ['outlet_id' => $outlet->id, 'name' => 'void_code'],
                ['value' => '1234']
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Setting::where('name', 'void_code')->delete();
    }
};
