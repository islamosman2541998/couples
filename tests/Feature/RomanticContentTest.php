<?php

namespace Tests\Feature;

use App\Models\{Card, CardLevel, Game, User, Subscription, KnowMeQuestion};
use Database\Seeders\RomanticContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RomanticContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacement_backs_up_old_content_and_preserves_accounts_and_subscriptions(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $game = Game::create(['name' => 'Existing', 'slug' => 'existing', 'type' => 'card', 'is_free' => false, 'price' => 50]);
        $sub = Subscription::create(['game_id' => $game->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '123', 'email' => $user->email, 'receipt_image' => 'receipts/test.png']);
        $game->refresh();
        $sub->refresh();
        $level = CardLevel::create(['name' => 'Old level', 'slug' => 'old', 'color' => '#ffffff']);
        Card::create(['card_level_id' => $level->id, 'content' => 'Old content', 'target' => 'both']);
        $this->artisan('games:refresh-content')->assertFailed();
        $this->assertDatabaseHas('cards', ['content' => 'Old content']);
        $this->artisan('games:refresh-content --replace')->assertSuccessful();
        $backups = Storage::disk('local')->files('backups/game-content');
        $this->assertCount(1, $backups);
        $backup = json_decode(Storage::disk('local')->get($backups[0]), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('Old content', $backup['tables']['cards'][0]['content']);
        $this->assertDatabaseMissing('cards', ['content' => 'Old content']);
        $this->assertSame($game->getAttributes(), $game->fresh()->getAttributes());
        $this->assertSame($sub->getAttributes(), $sub->fresh()->getAttributes());
        $this->assertNotNull($user->fresh());
        foreach (['cards' => 45, 'spinner_images' => 12, 'scratch_cards' => 100, 'who_questions' => 24, 'challenge_cards' => 24, 'know_me_questions' => 24, 'card_levels' => 3] as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }
        foreach (CardLevel::withCount('cards')->get() as $level) $this->assertSame(15, $level->cards_count);
        foreach (KnowMeQuestion::where('answer_type', 'choice')->get() as $question) $this->assertCount(4, $question->choices);
        $this->assertSame(0, DB::table('challenge_cards')->whereNotNull('image')->count());
        $this->assertSame(0, DB::table('scratch_cards')->whereNotNull('image')->count());
    }

    public function test_new_collection_renders_in_all_six_games(): void
    {
        $this->seed(RomanticContentSeeder::class);
        foreach (['card', 'spinner', 'scratch', 'who', 'challenge', 'know_me'] as $type) {
            $game = Game::create(['name' => $type, 'slug' => $type, 'type' => $type, 'is_free' => true]);
            $this->get('/games/'.$game->slug.'/play')->assertOk()->assertSee('وقتكم على مزاجكم');
        }
    }
}
