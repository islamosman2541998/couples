<?php

namespace App\Console\Commands;

use Database\Seeders\RomanticContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class RefreshGameContent extends Command
{
    protected $signature = 'games:refresh-content {--replace : Back up and replace existing game content}';
    protected $description = 'Install the romantic game collection, preserving accounts, games and subscriptions';

    public function handle(): int
    {
        if (!$this->option('replace')) {
            $this->error('Use --replace to explicitly authorize replacing game content. A private backup is created first.');
            return self::FAILURE;
        }

        $backup = 'backups/game-content/'.now()->format('Ymd-His').'-'.Str::uuid().'.json';
        DB::transaction(function () use ($backup) {
            $snapshot = ['version' => 1, 'created_at' => now()->toIso8601String(), 'tables' => []];
            foreach (RomanticContentSeeder::TABLES as $table) {
                $snapshot['tables'][$table] = DB::table($table)->orderBy('id')->lockForUpdate()->get()->toArray();
            }
            $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            $disk = Storage::disk('local');
            if (!$disk->put($backup, $json) || $disk->get($backup) !== $json) {
                throw new RuntimeException('Backup could not be verified; existing content was not changed.');
            }
            foreach (RomanticContentSeeder::TABLES as $table) {
                DB::table($table)->delete();
            }
            (new RomanticContentSeeder())->run();
        });

        $this->info('Private backup: '.Storage::disk('local')->path($backup));
        $this->table(['Content', 'Count'], collect(RomanticContentSeeder::TABLES)->map(fn ($table) => [$table, DB::table($table)->count()])->all());
        return self::SUCCESS;
    }
}
