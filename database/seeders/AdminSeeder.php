<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder {

    public function run(): void {
        User::create( [
            'name' => 'Admin Name',
            'password' => Hash::make( 'password' ),
            'email' => 'admin@gmail.com',
            'phone' => '+972565656565',
            'role' => 'admin',
            'notification_methods' => [
                'sms' => false,
                'email' => false,
                'whatsapp' => false,
            ],
        ] );
    }
}
