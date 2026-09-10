<?php

namespace Database\Seeders;

use App\Models\ControlCard;
use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControlGameSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $instructions = require database_path('seeders/data/game-instructions.php');
            Game::firstOrCreate(['slug' => 'control-game'], [
                'name' => 'لعبة السيطرة',
                'type' => 'control',
                'is_free' => true,
                'price' => 0,
                'is_active' => true,
                'sort_order' => 7,
                ...$instructions['control'],
            ]);

            $cards = require database_path('seeders/data/control-cards.php');
            foreach ($cards as $index => [$title, $description]) {
                ControlCard::firstOrCreate(['seed_key' => 'control-'.($index + 1)], [
                    'title' => $title,
                    'description' => $description,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        });
    }
}
