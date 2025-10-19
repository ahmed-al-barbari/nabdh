<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Safe to run multiple times - will update existing records
     * Uses ACTUAL storage filenames (hashed by Laravel)
     */
    public function run(): void
    {
        $products = [
            ['id' => 1, 'store_id' => 1, 'product_id' => 5, 'description' => "لابتوب HP مواصفات core-i5 جيل 10\r\nرام 8GB , كرت شاشة 2GB", 'price' => '1200.00', 'image' => 'products/F7ILkoq7sxvg2aKf4XI3ttJCGvqY86UeY9a41cQY.png'],
            ['id' => 2, 'store_id' => 2, 'product_id' => 7, 'description' => 'أكياس رمل للبيع بالجملة - متينة ومسامية للماء', 'price' => '350.00', 'image' => 'products/HLe2qh3U7MtRhXHnByanthy2EHvEpGjU7eaTUm0F.png'],
            ['id' => 3, 'store_id' => 1, 'product_id' => 4, 'description' => 'Xiaomi Note 20 Pro', 'price' => '650.00', 'image' => 'products/4sQjgqR2Km3MIGTKXPNBE2tuKzTqpAaH8FURldLI.png'],
            ['id' => 4, 'store_id' => 1, 'product_id' => 8, 'description' => 'شاحن Type C', 'price' => '75.00', 'image' => 'products/1z4mZyUkZJ1SWfEDRmkerHrSHoEPJ688HVCU4wpw.webp'],
            ['id' => 8, 'store_id' => 4, 'product_id' => 15, 'description' => 'ورق golden tex معطر', 'price' => '30.00', 'image' => 'products/Yy7anmuHdgfwZZGIyItFwfj8onSaEGA6cYIR9OKg.png'],
            ['id' => 9, 'store_id' => 4, 'product_id' => 16, 'description' => 'قطرات systane', 'price' => '22.00', 'image' => 'products/9rRObeBG8VV3VgXpgYcdQslHRSjS4sjUIpZZ02pg.webp'],
            ['id' => 10, 'store_id' => 4, 'product_id' => 17, 'description' => 'كمامات "goodcare"عدد 50', 'price' => '35.00', 'image' => 'products/sjKK1TOok3cnU1CXyyz4eG7ApvQafK9fN1KOMpSd.png'],
            ['id' => 11, 'store_id' => 6, 'product_id' => 2, 'description' => 'فول أمريكانا عدد 1', 'price' => '30.00', 'image' => 'products/875AlEvRY5e5byE9O0COD0Ffcy8VD5fBsSVUSrCG.png'],
            ['id' => 12, 'store_id' => 6, 'product_id' => 11, 'description' => 'حليب المراعي كامل الدسم', 'price' => '55.00', 'image' => 'products/MbHBVN2j6pDAuI08QJIaiKmGuvCOedmkYS1Ra8RT.webp'],
            ['id' => 13, 'store_id' => 6, 'product_id' => 18, 'description' => 'طحين قمح أبيض اكسترا', 'price' => '150.00', 'image' => 'products/G0umBILSHkGNeomIK7hsW3CIGCzpz9cK7dgoBadp.png'],
            ['id' => 14, 'store_id' => 6, 'product_id' => 20, 'description' => 'سكر ناعم أبيض صافي', 'price' => '5.00', 'image' => 'products/0B7w3FJpPQKaLkikFPnfqKYJ3sZ2QWoERlJZftir.png'],
            ['id' => 15, 'store_id' => 7, 'product_id' => 10, 'description' => 'أرز بسمتي  أبيض 1 كيلو صافي', 'price' => '9.00', 'image' => 'products/TAzTLHKNS675nVp7p2SznOrVBkjIwQzvc4MtZSxG.png'],
            ['id' => 16, 'store_id' => 8, 'product_id' => 10, 'description' => 'أرز حبة قصيرة 2 كيلو', 'price' => '36.00', 'image' => 'products/8yUUiF51iugfk1qfyrS77x0Vkv8QvoJLsXOeP5gu.png'],
            ['id' => 17, 'store_id' => 8, 'product_id' => 26, 'description' => 'زيت دوار الشمس 5 لتر', 'price' => '77.00', 'image' => 'products/W4OlLB3lcaoYv4TxmYIIaAWdZnvsIpNO2ExXfE1g.png'],
            ['id' => 18, 'store_id' => 8, 'product_id' => 23, 'description' => 'جبنة بيضاء تركية', 'price' => '42.00', 'image' => 'products/Zb4FOHAJPeqcdvtdmhLmXdFpH3Rq5DgmfA5ar88g.png'],
            ['id' => 19, 'store_id' => 8, 'product_id' => 25, 'description' => 'قشطة بوك عدد 1', 'price' => '22.00', 'image' => 'products/wLp99TYDeo333QzvMKWouWopl45kHs2WsXkpyxR4.webp'],
            ['id' => 20, 'store_id' => 8, 'product_id' => 27, 'description' => 'عصير تفاح أورجينال 1 لتر', 'price' => '15.00', 'image' => 'products/K0ZrBXsBJK12LLsV9GOxpYZDq5dxDi7aTxVcftpH.png'],
            ['id' => 21, 'store_id' => 8, 'product_id' => 22, 'description' => 'حليب Nestle مكثف كامل الدسم', 'price' => '20.00', 'image' => 'products/G4qKTW6MRLsctODAp32dei7Ei6VCCpXUehvdy98C.png'],
            ['id' => 22, 'store_id' => 9, 'product_id' => 10, 'description' => 'أرز بني قصير الحبة', 'price' => '3.00', 'image' => 'products/duBbv7IOIM7WXPIUQq2APsnuwIr9muHo1kVMgk6i.webp'],
            ['id' => 23, 'store_id' => 4, 'product_id' => 28, 'description' => 'كريم Aloe Vera للوجه', 'price' => '45.00', 'image' => 'products/YQ3cwviZQ8Xi9CC3qS4JeYc6mIlOuY6fYsgSDFCp.webp'],
            ['id' => 24, 'store_id' => 4, 'product_id' => 29, 'description' => 'جل طبي للعضلات', 'price' => '30.00', 'image' => 'products/YI5SpaQBXGO6FcTKoDdFcjtXwArlhZS9YpfM2VFN.png'],
            ['id' => 25, 'store_id' => 6, 'product_id' => 24, 'description' => 'عسل العطار صافي 1/2 كيلو', 'price' => '250.00', 'image' => 'products/YFkth28DA1WsZoOg6N7rUgpsjHhjVIbSm8tIw3pX.webp'],
            ['id' => 35, 'store_id' => 1, 'product_id' => 14, 'description' => 'بوج', 'price' => '140.00', 'image' => 'products/7pxbbQKc8QVS6EE6Ezd2zT7E8WGsTo8KOQ1sgv1X.png'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['id' => $product['id']],
                $product
            );
        }
    }
}
