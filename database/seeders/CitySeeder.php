<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        City::query()->insert([
            [
                'name' => 'بيت حانون',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'بيت لاهيا',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'جباليا',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'غزة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'النصيرات',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'البريج',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'المغازي',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'دير البلح',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'خان يونس',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'رفح',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
