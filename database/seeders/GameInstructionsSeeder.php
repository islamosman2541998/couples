<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameInstructionsSeeder extends Seeder
{
    public function run(): void
    {
        $games = Game::orderBy('id')->get(['id', 'name', 'type', 'description', 'how_to_play']);

        if ($games->isNotEmpty()) {
            $filename = 'backups/game-descriptions/'.now()->format('Y-m-d_His').'-'.Str::lower(Str::random(6)).'.json';
            Storage::disk('local')->put($filename, $games->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->command?->info('تم حفظ نسخة الوصف القديمة في '.Storage::disk('local')->path($filename));
        }

        $instructions = require database_path('seeders/data/game-instructions.php');

        DB::transaction(function () use ($instructions) {
            foreach ($instructions as $type => $content) {
                Game::where('type', $type)->update($content);
            }
        });
    }
}
