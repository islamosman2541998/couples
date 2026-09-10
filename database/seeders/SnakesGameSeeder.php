<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\SnakeCell;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SnakesGameSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $instructions = require database_path('seeders/data/game-instructions.php');
            Game::firstOrCreate(['slug' => 'snakes-and-ladders'], [
                'name' => 'السلم والتعبان', 'type' => 'snakes', 'is_free' => true,
                'price' => 0, 'is_active' => true, 'sort_order' => 8,
                ...$instructions['snakes'],
            ]);
            $cells = require database_path('seeders/data/snake-cells.php');
            foreach ($cells as $index => [$title, $content, $mood]) {
                SnakeCell::firstOrCreate(['number' => $index + 1], compact('title', 'content', 'mood') + ['is_active' => true]);
            }
        });
    }
}
