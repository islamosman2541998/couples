<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration {
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('premium');
        foreach (['scratch_cards' => 'scratch/', 'control_cards' => 'control-cards/', 'challenge_cards' => 'challenges/', 'spinner_images' => 'spinner/'] as $table => $prefix) {
            foreach (DB::table($table)->whereNotNull('image')->pluck('image')->unique() as $path) {
                // Only move known game assets within the public disk, never arbitrary paths.
                if (! str_starts_with($path, $prefix) || str_contains($path, '..') || str_contains($path, '\\')) {
                    throw new RuntimeException('Unexpected game media path; review before migration.');
                }
                if (! $public->exists($path)) continue;
                $content = $public->get($path);
                $private->put($path, $content);
                if (hash('sha256', $private->get($path)) !== hash('sha256', $content)) {
                    throw new RuntimeException('Game media copy verification failed.');
                }
                $public->delete($path);
            }
        }
    }

    public function down(): void
    {
        // Keep paid media private when rolling back schema changes.
    }
};
