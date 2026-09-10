<?php

namespace App\Console\Commands;

use App\Support\InstallNewGames;
use Illuminate\Console\Command;

class InstallNewGamesCommand extends Command
{
    protected $signature = 'games:install-new';

    protected $description = 'Install missing Control and Snakes games and their content without overwriting edits';

    public function handle(InstallNewGames $installer): int
    {
        try {
            $installer->run();
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Installation failed. See storage/logs/laravel.log for details.');
            return self::FAILURE;
        }

        $this->info('Control and Snakes games are ready. Existing game settings and content were preserved.');
        return self::SUCCESS;
    }
}
