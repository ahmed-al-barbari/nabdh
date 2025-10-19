<?php

namespace Database\Seeders;

use App\Models\MainProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MainProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Safe to run multiple times - will update existing records
     */
    public function run(): void
    {
        $products = [
            ['id' => 1, 'name' => 'كتاب', 'price' => 10, 'category_id' => 4],
            ['id' => 2, 'name' => 'معلبات', 'price' => 13, 'category_id' => 6],
            ['id' => 4, 'name' => 'هاتف', 'price' => 740, 'category_id' => 1],
            ['id' => 5, 'name' => 'كمبيوتر محمول', 'price' => 1000, 'category_id' => 1],
            ['id' => 6, 'name' => 'خشب', 'price' => 500, 'category_id' => 3],
            ['id' => 7, 'name' => 'أكياس رمل', 'price' => 400, 'category_id' => 8],
            ['id' => 8, 'name' => 'شاحن', 'price' => 80, 'category_id' => 1],
            ['id' => 9, 'name' => 'بيض', 'price' => 30, 'category_id' => 6],
            ['id' => 10, 'name' => 'أرز', 'price' => 35, 'category_id' => 6],
            ['id' => 11, 'name' => 'حليب', 'price' => 13, 'category_id' => 6],
            ['id' => 12, 'name' => 'إطار سيارة', 'price' => 400, 'category_id' => 7],
            ['id' => 13, 'name' => 'فلتر مكيف', 'price' => 150, 'category_id' => 7],
            ['id' => 14, 'name' => 'بوجيهات', 'price' => 40, 'category_id' => 7],
            ['id' => 15, 'name' => 'ورق معطر', 'price' => 27, 'category_id' => 9],
            ['id' => 16, 'name' => 'قطرات عيون', 'price' => 20, 'category_id' => 9],
            ['id' => 17, 'name' => 'كمامات', 'price' => 40, 'category_id' => 9],
            ['id' => 18, 'name' => 'طحين', 'price' => 38, 'category_id' => 6],
            ['id' => 20, 'name' => 'سكر', 'price' => 8, 'category_id' => 6],
            ['id' => 22, 'name' => 'حليب', 'price' => 67, 'category_id' => 6],
            ['id' => 23, 'name' => 'جبنة بيضاء', 'price' => 80, 'category_id' => 6],
            ['id' => 24, 'name' => 'عسل', 'price' => 150, 'category_id' => 6],
            ['id' => 25, 'name' => 'قشطة بوك', 'price' => 18, 'category_id' => 6],
            ['id' => 26, 'name' => 'زيت للطهي', 'price' => 77, 'category_id' => 6],
            ['id' => 27, 'name' => 'عصير', 'price' => 10, 'category_id' => 6],
            ['id' => 28, 'name' => 'كريم', 'price' => 55, 'category_id' => 9],
            ['id' => 29, 'name' => 'جل طبي', 'price' => 29, 'category_id' => 9],
        ];

        foreach ($products as $product) {
            MainProduct::updateOrCreate(
                ['id' => $product['id']],
                $product
            );
        }
    }
}
