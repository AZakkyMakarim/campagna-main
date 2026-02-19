<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = config('array.ingredient.units');

        foreach ($units as $name => $data) {
            Unit::updateOrCreate(
                [
                    'name' => $name,
                ],
                [
                    'symbol' => $data['symbol'],
                ]
            );
        }
    }
}
