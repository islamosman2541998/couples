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
            CardLevelSeeder::class,
            CardSeeder::class,
            SpinnerImageSeeder::class,
            ScratchCardSeeder::class,
            WhoQuestionSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
