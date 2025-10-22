<?php

namespace Database\Seeders;

use App\Models\Barter;
use Illuminate\Database\Seeder;

class BarterSeeder extends Seeder {
    /**
    * Run the database seeds.
    * Safe to run multiple times - will update existing records
    * Uses ACTUAL storage filenames ( hashed by Laravel )
    */

    public function run(): void {
        $barters = [
            [
                'id' => 1,
                'user_id' => 8,
                'offer_item' => '5 لتر سيرج',
                'request_item' => '2 كيلو طحين',
                'description' => 'أحتاج 2 كيلو طحين اكتسرا',
                'location' => 'حي التفاح',
                'contact_method' => 'واتساب',
                'availability' => 'متوفر اليوم',
                'offer_status' => 'جديد - لم يستعمل',
                'quantity' => 'كمية صغيرة (5 - 2)',
                'exchange_preferences' => 'مقابلة في مكان عام',
                'image' => 'barters/kPdpEt5aWLi1y9tWi1ecuzZWOKDkxcTM8fTgDprk.png',
                'status' => 'active',
                'accepted_by' => null,
            ],
            [
                'id' => 2,
                'user_id' => 9,
                'offer_item' => '5 كيلو سكر',
                'request_item' => 'طعام قطط',
                'description' => 'كيس سكر الأسرة حجم عائلي',
                'location' => 'حي الشجاعية',
                'contact_method' => 'مكالمات هاتفية',
                'availability' => 'متوفر غذا',
                'offer_status' => 'جديد - لم يستعمل',
                'quantity' => null,
                'exchange_preferences' => 'تسليم منزلي ممكن',
                'image' => 'barters/89P8NUT9AIMnrZLa3iZvLPhXg1bvmN4r7mWdd3FU.png',
                'status' => 'active',
                'accepted_by' => null,
            ],
            [
                'id' => 3,
                'user_id' => 2,
                'offer_item' => 'صابون سائل',
                'request_item' => 'حطب',
                'description' => 'صابون كيرفري سائل لليدين 500ml',
                'location' => 'شارع الجلاء',
                'contact_method' => 'رسائل نصية',
                'availability' => 'متوفر اليوم',
                'offer_status' => 'ممتاز - استعمال خفيف',
                'quantity' => 'كمية صغيرة (5 - 2)',
                'exchange_preferences' => 'استلام من المنزل',
                'image' => 'barters/zFpwDNFsJrj42en10yY0z2fSfbxEnB8qWLvvaWME.webp',
                'status' => 'active',
                'accepted_by' => null,
            ],
            [
                'id' => 5,
                'user_id' => 15,
                'offer_item' => 'كيسين قرشلة',
                'request_item' => 'ربطة خبز أم ال7 شيكل',
                'description' => 'قرشلة سخنة طازة من المصنع',
                'location' => 'حي الصبرة',
                'contact_method' => null,
                'availability' => null,
                'offer_status' => null,
                'quantity' => null,
                'exchange_preferences' => null,
                'image' => 'barters/Pk1ZbOnTVG8X3jWozJxSuPcWOJNplIjqhCXK7Xsc.png',
                'status' => 'completed',
                'accepted_by' => 3,
            ],
            [
                'id' => 7,
                'user_id' => 2,
                'offer_item' => 'جل غسيل',
                'request_item' => 'صبغة',
                'description' => 'حليب صافي',
                'location' => 'حي الشجاعية',
                'contact_method' => 'واتساب',
                'availability' => 'متوفر الان',
                'offer_status' => 'جديد - لم يستعمل',
                'quantity' => 'قطعة واحدة',
                'exchange_preferences' => null,
                'image' => null,
                'status' => 'active',
                'accepted_by' => null,
            ],
        ];

        foreach ( $barters as $barter ) {
            Barter::updateOrCreate(
                [ 'id' => $barter[ 'id' ] ],
                $barter
            );
        }
    }
}
