<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'مدير النظام',
            'email'    => 'admin@admin.com',
            'phone'    => '0500000000',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'is_active'=> true,
        ]);

        User::create([
            'name'     => 'مستخدم تجريبي',
            'email'    => 'user@test.com',
            'phone'    => '0511111111',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_active'=> true,
        ]);
    }
}
