<?php

namespace Database\Seeders;

use App\Models\Distance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistanceSeeder extends Seeder {
    /**
    * Run the database seeds.
    */

    public function run(): void {
        Distance::query()->insert( [
            [ 'city_id_one' => 1, 'city_id_two' => 2, 'distance' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 3, 'distance' => 6, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 4, 'distance' => 10, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 5, 'distance' => 21, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 6, 'distance' => 22, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 7, 'distance' => 23, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 8, 'distance' => 25, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 9, 'distance' => 38, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 1, 'city_id_two' => 10, 'distance' => 45, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 3, 'distance' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 4, 'distance' => 7, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 5, 'distance' => 18, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 6, 'distance' => 19, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 7, 'distance' => 20, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 8, 'distance' => 22, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 9, 'distance' => 35, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 2, 'city_id_two' => 10, 'distance' => 42, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 3, 'city_id_two' => 4, 'distance' => 4, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 3, 'city_id_two' => 5, 'distance' => 15, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 3, 'city_id_two' => 6, 'distance' => 16, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 3, 'city_id_two' => 7, 'distance' => 17, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 3, 'city_id_two' => 8, 'distance' => 19, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 3, 'city_id_two' => 9, 'distance' => 32, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 3, 'city_id_two' => 10, 'distance' => 39, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 4, 'city_id_two' => 5, 'distance' => 11, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 4, 'city_id_two' => 6, 'distance' => 12, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 4, 'city_id_two' => 7, 'distance' => 13, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 4, 'city_id_two' => 8, 'distance' => 15, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 4, 'city_id_two' => 9, 'distance' => 28, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 4, 'city_id_two' => 10, 'distance' => 35, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 5, 'city_id_two' => 6, 'distance' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 5, 'city_id_two' => 7, 'distance' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 5, 'city_id_two' => 8, 'distance' => 5, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 5, 'city_id_two' => 9, 'distance' => 18, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 5, 'city_id_two' => 10, 'distance' => 25, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 6, 'city_id_two' => 7, 'distance' => 1, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 6, 'city_id_two' => 8, 'distance' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 6, 'city_id_two' => 9, 'distance' => 16, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 6, 'city_id_two' => 10, 'distance' => 23, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 7, 'city_id_two' => 8, 'distance' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 7, 'city_id_two' => 9, 'distance' => 15, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 7, 'city_id_two' => 10, 'distance' => 22, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 8, 'city_id_two' => 9, 'distance' => 13, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 8, 'city_id_two' => 10, 'distance' => 20, 'created_at' => now(), 'updated_at' => now() ],
            [ 'city_id_one' => 9, 'city_id_two' => 10, 'distance' => 7, 'created_at' => now(), 'updated_at' => now() ],
        ] );

    }
}
