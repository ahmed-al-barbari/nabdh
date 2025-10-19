<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Safe to run multiple times - will update existing records
     */
    public function run(): void
    {
        $cities = [
            ['id' => 1, 'name' => 'بيت حانون'],
            ['id' => 2, 'name' => 'بيت لاهيا'],
            ['id' => 3, 'name' => 'جباليا'],
            ['id' => 4, 'name' => 'غزة'],
            ['id' => 5, 'name' => 'النصيرات'],
            ['id' => 6, 'name' => 'البريج'],
            ['id' => 7, 'name' => 'المغازي'],
            ['id' => 8, 'name' => 'دير البلح'],
            ['id' => 9, 'name' => 'خان يونس'],
            ['id' => 10, 'name' => 'رفح'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['id' => $city['id']],
                $city
            );
        }
    }
}
