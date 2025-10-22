<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder {
    /**
    * Run the database seeds.
    * Safe to run multiple times - will update existing records
    * Uses ACTUAL storage filenames ( hashed by Laravel )
    */

    public function run(): void {
        $stores = [
            [
                'id' => 1,
                'user_id' => 2,
                'city_id' => 5,
                'name' => 'Gaza Tech',
                'address' => 'غزة - شارع الوحدة',
                'image' => 'stores/uSldQEkCt9nFJss4azKaOG7aZ6yqhYdbQU8hB6mJ.webp',
                'status' => 'active',
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'city_id' => 8,
                'name' => 'حلبي للمقاولات',
                'address' => 'شارع البركة',
                'image' => 'stores/IGP5NUG4xQhSLXQGNlT95xQAd0b0H8sxoWAtFTOf.webp',
                'status' => 'active',
            ],
            [
                'id' => 4,
                'user_id' => 6,
                'city_id' => 4,
                'name' => 'صيدلية الحلو',
                'address' => 'شارع النصر',
                'image' => 'stores/XgOmubT2NWxVeUppzE7l8fVU1SWjlt4fWumpmVuV.jpg',
                'status' => 'active',
            ],
            [
                'id' => 5,
                'user_id' => 7,
                'city_id' => 9,
                'name' => 'أبو حمد للمواد الغذائية',
                'address' => 'شارع النص',
                'image' => 'stores/2RalQLtnHxiOQTsdrFgGkzzYPaIRKGLJ6ksdipoU.jpg',
                'status' => 'active',
            ],
            [
                'id' => 6,
                'user_id' => 8,
                'city_id' => 9,
                'name' => 'أبو حميد للمواد الغذائية',
                'address' => 'شارع البحر',
                'image' => 'stores/nDhJlGV4swu9gKyJ4QFcRNAHu9m92hqrmWExXh7m.jpg',
                'status' => 'active',
            ],
            [
                'id' => 7,
                'user_id' => 10,
                'city_id' => 4,
                'name' => 'بقالة حسونة',
                'address' => 'شارع عمر المختار',
                'image' => 'stores/r1l0ELOV6EPzxvT54F0V958Vho6lPPdYDwTNYddh.jpg',
                'status' => 'active',
            ],
            [
                'id' => 8,
                'user_id' => 11,
                'city_id' => 4,
                'name' => 'العمدة مول',
                'address' => 'غزة - تل الهوا',
                'image' => 'stores/RFEol34KzOHRpFzcGPHO5ynw5JIUzTG08VTFwhXD.jpg',
                'status' => 'active',
            ],
            [
                'id' => 9,
                'user_id' => 12,
                'city_id' => 8,
                'name' => 'سوبر ماركت البركة',
                'address' => 'دير البلح - مقابل مخبز اليمام',
                'image' => 'stores/syOj0YQT2kT8biTaMShW0xrh7ZKdF9ScCDKdjmfm.webp',
                'status' => 'active',
            ],
            [
                'id' => 10,
                'user_id' => 3,
                'city_id' => 1,
                'name' => null,
                'address' => null,
                'image' => null,
                'status' => 'pending',
            ],
            [
                'id' => 12,
                'user_id' => 15,
                'city_id' => 4,
                'name' => 'محل إياد للمنظفات',
                'address' => 'غزة - شارع الوحدة',
                'image' => 'stores/Z8lzVdIYQB9LDHghTkD80VEoxbflfyq7hnGIQulo.png',
                'status' => 'active',
            ],
        ];

        foreach ( $stores as $store ) {
            Store::updateOrCreate(
                [ 'id' => $store[ 'id' ] ],
                $store
            );
        }
    }
}
