<?php

namespace Database\Seeders;

use App\Models\Distance;
use Illuminate\Database\Seeder;

class DistanceSeeder extends Seeder {
    /**
    * Run the database seeds.
    * Safe to run multiple times - will update existing records
    */

    public function run(): void {
        $distances = [
            [ 'city_id_one' => 1, 'city_id_two' => 2, 'distance' => 3 ],
            [ 'city_id_one' => 1, 'city_id_two' => 3, 'distance' => 6 ],
            [ 'city_id_one' => 1, 'city_id_two' => 4, 'distance' => 10 ],
            [ 'city_id_one' => 1, 'city_id_two' => 5, 'distance' => 21 ],
            [ 'city_id_one' => 1, 'city_id_two' => 6, 'distance' => 22 ],
            [ 'city_id_one' => 1, 'city_id_two' => 7, 'distance' => 23 ],
            [ 'city_id_one' => 1, 'city_id_two' => 8, 'distance' => 25 ],
            [ 'city_id_one' => 1, 'city_id_two' => 9, 'distance' => 38 ],
            [ 'city_id_one' => 1, 'city_id_two' => 10, 'distance' => 45 ],
            [ 'city_id_one' => 2, 'city_id_two' => 3, 'distance' => 3 ],
            [ 'city_id_one' => 2, 'city_id_two' => 4, 'distance' => 7 ],
            [ 'city_id_one' => 2, 'city_id_two' => 5, 'distance' => 18 ],
            [ 'city_id_one' => 2, 'city_id_two' => 6, 'distance' => 19 ],
            [ 'city_id_one' => 2, 'city_id_two' => 7, 'distance' => 20 ],
            [ 'city_id_one' => 2, 'city_id_two' => 8, 'distance' => 22 ],
            [ 'city_id_one' => 2, 'city_id_two' => 9, 'distance' => 35 ],
            [ 'city_id_one' => 2, 'city_id_two' => 10, 'distance' => 42 ],
            [ 'city_id_one' => 3, 'city_id_two' => 4, 'distance' => 4 ],
            [ 'city_id_one' => 3, 'city_id_two' => 5, 'distance' => 15 ],
            [ 'city_id_one' => 3, 'city_id_two' => 6, 'distance' => 16 ],
            [ 'city_id_one' => 3, 'city_id_two' => 7, 'distance' => 17 ],
            [ 'city_id_one' => 3, 'city_id_two' => 8, 'distance' => 19 ],
            [ 'city_id_one' => 3, 'city_id_two' => 9, 'distance' => 32 ],
            [ 'city_id_one' => 3, 'city_id_two' => 10, 'distance' => 39 ],
            [ 'city_id_one' => 4, 'city_id_two' => 5, 'distance' => 11 ],
            [ 'city_id_one' => 4, 'city_id_two' => 6, 'distance' => 12 ],
            [ 'city_id_one' => 4, 'city_id_two' => 7, 'distance' => 13 ],
            [ 'city_id_one' => 4, 'city_id_two' => 8, 'distance' => 15 ],
            [ 'city_id_one' => 4, 'city_id_two' => 9, 'distance' => 28 ],
            [ 'city_id_one' => 4, 'city_id_two' => 10, 'distance' => 35 ],
            [ 'city_id_one' => 5, 'city_id_two' => 6, 'distance' => 2 ],
            [ 'city_id_one' => 5, 'city_id_two' => 7, 'distance' => 3 ],
            [ 'city_id_one' => 5, 'city_id_two' => 8, 'distance' => 5 ],
            [ 'city_id_one' => 5, 'city_id_two' => 9, 'distance' => 18 ],
            [ 'city_id_one' => 5, 'city_id_two' => 10, 'distance' => 25 ],
            [ 'city_id_one' => 6, 'city_id_two' => 7, 'distance' => 1 ],
            [ 'city_id_one' => 6, 'city_id_two' => 8, 'distance' => 3 ],
            [ 'city_id_one' => 6, 'city_id_two' => 9, 'distance' => 16 ],
            [ 'city_id_one' => 6, 'city_id_two' => 10, 'distance' => 23 ],
            [ 'city_id_one' => 7, 'city_id_two' => 8, 'distance' => 2 ],
            [ 'city_id_one' => 7, 'city_id_two' => 9, 'distance' => 15 ],
            [ 'city_id_one' => 7, 'city_id_two' => 10, 'distance' => 22 ],
            [ 'city_id_one' => 8, 'city_id_two' => 9, 'distance' => 13 ],
            [ 'city_id_one' => 8, 'city_id_two' => 10, 'distance' => 20 ],
            [ 'city_id_one' => 9, 'city_id_two' => 10, 'distance' => 7 ],
        ];

        foreach ( $distances as $distance ) {
            Distance::updateOrCreate(
                [
                    'city_id_one' => $distance[ 'city_id_one' ],
                    'city_id_two' => $distance[ 'city_id_two' ]
                ],
                $distance
            );
        }
    }
}
