<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
    * Seed the application's database.
    */

    public function run(): void {

        $this->call( [
            CitySeeder::class,
            DistanceSeeder::class,
            CategorySeeder::class,
            MainProductSeeder::class,
            UserSeeder::class,
            StoreSeeder::class,
            ProductSeeder::class,
            BarterSeeder::class,
        ] );

    }
}
