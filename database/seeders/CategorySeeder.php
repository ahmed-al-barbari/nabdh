<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Safe to run multiple times - will update existing records
     */
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'إلكترونيات', 'description' => null],
            ['id' => 2, 'name' => 'أدوات منزلية', 'description' => null],
            ['id' => 3, 'name' => 'أثاث', 'description' => null],
            ['id' => 4, 'name' => 'الكتب', 'description' => null],
            ['id' => 5, 'name' => 'الرياضة', 'description' => null],
            ['id' => 6, 'name' => 'مواد غذائية', 'description' => null],
            ['id' => 7, 'name' => 'السيارات', 'description' => null],
            ['id' => 8, 'name' => 'مواد بناء', 'description' => null],
            ['id' => 9, 'name' => 'مسلتزمات طبية', 'description' => null],
            ['id' => 11, 'name' => 'أدوات حرفية', 'description' => null],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['id' => $category['id']],
                $category
            );
        }
    }
}
