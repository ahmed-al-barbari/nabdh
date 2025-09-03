<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Name',
            'password' => Hash::make('password'),
            'email' => 'admin@gmail.com',
            'phone' => '+792565656565',
            'role' => 'admin',
            'notification_methods' => [
                'sms' => false,
                'email' => false,
                'whats' => false,
            ],
        ]);
    }
}
