<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GameSeeder::class,
            ControlGameSeeder::class,
            SnakesGameSeeder::class,
            RomanticContentSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
