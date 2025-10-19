<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Safe to run multiple times - will update existing records
     */
    public function run(): void
    {
        // Admin user - password: 12345678
        User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'سامي عودة',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'phone' => '+972565656565',
                'role' => 'admin',
                'status' => 'pending',
                'theme' => 'light',
                'currency' => 'ILS',
                'city_id' => 3,
            ]
        );

        // Merchant users - password: 12345678
        $merchants = [
            ['id' => 2, 'name' => 'علي عمر', 'email' => 'ali@gazatech.ps', 'phone' => '+970592000001', 'city_id' => 5],
            ['id' => 6, 'name' => 'خالد نوفل', 'email' => 'khaled@pharma.ps', 'phone' => '+970598888888', 'city_id' => 4, 'theme' => 'dark'],
            ['id' => 7, 'name' => 'محمد عبدالله', 'email' => 'm@g.com', 'phone' => null, 'city_id' => 9],
            ['id' => 8, 'name' => 'محمد عبد الهادي', 'email' => 'moh@g.com', 'phone' => '+971111111111', 'city_id' => 9],
            ['id' => 10, 'name' => 'ابراهيم حسونة', 'email' => 'h@mail.com', 'phone' => '+970592111111', 'city_id' => 8],
            ['id' => 11, 'name' => 'أحمد سلامة', 'email' => 'ah@mail.com', 'phone' => '+970596000000', 'city_id' => 4],
            ['id' => 12, 'name' => 'سامي حسن', 'email' => 'sami@mail.com', 'phone' => '+970591234567', 'city_id' => 8, 'status' => 'pending'],
            ['id' => 15, 'name' => 'محمد إياد الدالي', 'email' => 'eyad@mail.com', 'phone' => '+970593000001', 'city_id' => 4, 'theme' => 'dark'],
        ];

        foreach ($merchants as $merchant) {
            User::updateOrCreate(
                ['id' => $merchant['id']],
                array_merge([
                    'password' => Hash::make('12345678'),
                    'role' => 'merchant',
                    'status' => $merchant['status'] ?? 'active',
                    'theme' => $merchant['theme'] ?? 'light',
                    'currency' => 'ILS',
                ], $merchant)
            );
        }

        // Customer users - password: 12345678
        $customers = [
            ['id' => 3, 'name' => 'عمر سليم', 'email' => 'omar@mail.com', 'city_id' => 1, 'theme' => 'dark'],
            ['id' => 4, 'name' => 'زكريا حلبي', 'email' => 'zakaria@halabi.ps', 'phone' => '+970591000000', 'city_id' => 8],
            ['id' => 9, 'name' => 'مسعود بدر', 'email' => 'mas@g.com', 'city_id' => 2, 'currency' => 'USD'],
            ['id' => 22, 'name' => 'أسامة هاني', 'email' => 'os@g.com', 'phone' => '+970595000049', 'city_id' => 1, 'theme' => 'dark'],
            ['id' => 23, 'name' => 'دير فلح', 'email' => 'dih@gmail.com'],
            ['id' => 24, 'name' => 'عاصم الشامي', 'email' => 'asem@g.com'],
            ['id' => 26, 'name' => 'دير الفير', 'email' => 'DER@G.C'],
            ['id' => 27, 'name' => 'سامي الدير', 'email' => 'sa@g.com'],
            ['id' => 28, 'name' => 'عميد حميد', 'email' => 'am@m.com'],
            ['id' => 29, 'name' => 'سمير سالم', 'email' => 's@f'],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['id' => $customer['id']],
                array_merge([
                    'password' => Hash::make('12345678'),
                    'role' => 'customer',
                    'status' => 'pending',
                    'theme' => $customer['theme'] ?? 'light',
                    'currency' => $customer['currency'] ?? 'ILS',
                ], $customer)
            );
        }
    }
}
