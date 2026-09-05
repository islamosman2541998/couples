<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $instructions = require database_path('seeders/data/game-instructions.php');

        Game::create([
            'name' => 'لعبة الكروت',
            'slug' => 'card-game',
            'description' => $instructions['card']['description'],
            'how_to_play' => $instructions['card']['how_to_play'],
            'type' => 'card',
            'is_free' => false,
            'price' => 29.99,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Game::create([
            'name' => 'لعبة السبينر',
            'slug' => 'spinner-game',
            'description' => $instructions['spinner']['description'],
            'how_to_play' => $instructions['spinner']['how_to_play'],
            'type' => 'spinner',
            'is_free' => true,
            'price' => 0,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        foreach ([
            ['scratch', 'scratch-game', 'خربش والعب'],
            ['who', 'who-game', 'بوس أو دوس'],
            ['challenge', 'challenge-game', 'شوق أو دوق'],
            ['know_me', 'know-me-game', 'عارف شريكك؟'],
        ] as $index => [$type, $slug, $name]) {
            Game::create(compact('type', 'slug', 'name') + $instructions[$type] + ['is_free' => true, 'price' => 0, 'is_active' => true, 'sort_order' => $index + 3]);
        }
    }
}
