<?php

namespace Database\Seeders;

use App\Models\CardLevel;
use Illuminate\Database\Seeder;

class CardLevelSeeder extends Seeder
{
    public function run(): void
    {
        CardLevel::create(['name' => 'سهل',   'slug' => 'easy',   'color' => '#22c55e', 'sort_order' => 1]);
        CardLevel::create(['name' => 'متوسط', 'slug' => 'medium', 'color' => '#f59e0b', 'sort_order' => 2]);
        CardLevel::create(['name' => 'صعب',   'slug' => 'hard',   'color' => '#ef4444', 'sort_order' => 3]);
    }
}
