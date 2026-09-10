<?php

namespace App\Support;

use Database\Seeders\ControlGameSeeder;
use Database\Seeders\SnakesGameSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class InstallNewGames
{
    public function run(): void
    {
        // A file lock also works on hosts where a database cache is not configured.
        $lock = Cache::store('file')->lock('install-new-games', 180);
        if (! $lock->get()) {
            throw new RuntimeException('تثبيت الألعاب جارٍ بالفعل. انتظر قليلاً ثم حدّث الصفحة.');
        }

        try {
            $paths = [];
            if (! Schema::hasColumn('games', 'how_to_play')) {
                $paths[] = 'database/migrations/2026_09_05_190000_add_how_to_play_to_games_table.php';
            }
            if (! Schema::hasTable('control_cards')) {
                $paths[] = 'database/migrations/2026_09_07_000001_create_control_cards_table.php';
            }
            if (! Schema::hasTable('snake_cells')) {
                $paths[] = 'database/migrations/2026_09_07_000002_create_snake_cells_table.php';
            }
            if ($paths && Artisan::call('migrate', ['--path' => $paths, '--force' => true]) !== 0) {
                throw new RuntimeException('تعذر تجهيز جداول الألعاب. راجع سجل أخطاء السيرفر.');
            }
            DB::transaction(function () {
                (new ControlGameSeeder)->run();
                (new SnakesGameSeeder)->run();
            });
        } finally {
            $lock->release();
        }
    }
}
