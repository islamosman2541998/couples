<?php

namespace Database\Seeders;

use App\Models\{Card, CardLevel, SpinnerImage, ScratchCard, WhoQuestion, ChallengeCard, KnowMeQuestion};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RomanticContentSeeder extends Seeder
{
    public const TABLES = ['cards', 'spinner_images', 'scratch_cards', 'who_questions', 'challenge_cards', 'know_me_questions', 'card_levels'];

    public function run(): void
    {
        foreach (self::TABLES as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException('Content already exists. Use games:refresh-content --replace to back it up before replacing it.');
            }
        }

        $content = require database_path('seeders/data/romantic-content.php');
        DB::transaction(function () use ($content) {
            foreach ($content['levels'] as $index => $attributes) {
                $level = CardLevel::create($attributes + ['sort_order' => $index + 1]);
                foreach ($content['cards'][$level->slug] as $order => $text) {
                    Card::create(['card_level_id' => $level->id, 'content' => $text, 'target' => 'both', 'is_active' => true, 'sort_order' => $order + 1]);
                }
            }
            $colors = ['#ec4899', '#8b5cf6', '#f43f5e', '#6366f1', '#a855f7', '#db2777'];
            foreach ($content['spinner'] as $index => $name) {
                SpinnerImage::create(['name' => $name, 'image' => 'spinner/placeholder.png', 'color' => $colors[$index % count($colors)], 'is_active' => true, 'sort_order' => $index + 1]);
            }
            foreach ($content['scratch'] as $index => $text) {
                ScratchCard::create(['number' => $index + 1, 'content' => $text, 'image' => null, 'is_active' => true, 'sort_order' => $index + 1]);
            }
            foreach ($content['who'] as $index => [$category, $question, $challenge]) {
                WhoQuestion::create(compact('category', 'question', 'challenge') + ['is_active' => true, 'sort_order' => $index + 1]);
            }
            foreach ($content['challenges'] as $index => [$category, $title, $description, $timer]) {
                ChallengeCard::create(compact('category', 'title', 'description', 'timer') + ['image' => null, 'is_active' => true, 'sort_order' => $index + 1]);
            }
            foreach ($content['know_me'] as $index => $row) {
                [$category, $question, $choices] = $row;
                KnowMeQuestion::create(compact('category', 'question', 'choices') + ['answer_type' => $choices === null ? 'open' : 'choice', 'hint' => $row[3] ?? null, 'is_active' => true, 'sort_order' => $index + 1]);
            }
            (new ScratchBoardSeeder())->run();
        });
    }
}
