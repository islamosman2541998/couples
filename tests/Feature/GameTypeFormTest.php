<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Database\Seeders\GameInstructionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GameTypeFormTest extends TestCase
{
    use RefreshDatabase;

    public static function types(): array
    {
        return array_map(fn ($type) => [$type], ['card', 'spinner', 'scratch', 'who', 'challenge', 'know_me']);
    }

    #[DataProvider('types')]
    public function test_edit_form_preserves_the_real_type_when_saving_description(string $type): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $game = Game::create(['name' => 'Test', 'slug' => $type, 'type' => $type, 'is_free' => true]);
        $response = $this->get('/admin/games/'.$game->id.'/edit')->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $options = $xpath->query('//select[@name="type"]/option');
        $this->assertCount(6, $options);
        $selected = $xpath->query('//select[@name="type"]/option[@selected]');
        $this->assertCount(1, $selected);
        $submittedType = $selected->item(0)->getAttribute('value');
        $this->assertSame($type, $submittedType);
        $this->put('/admin/games/'.$game->id, [
            'name' => 'Test',
            'description' => 'New description',
            'how_to_play' => "First step\nSecond step",
            'type' => $submittedType,
            'price' => 0,
            'is_free' => 1,
            'is_active' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertSame($type, $game->fresh()->type);
        $this->assertSame("First step\nSecond step", $game->fresh()->how_to_play);
        $views = ['card' => 'card-game', 'spinner' => 'spinner-game', 'scratch' => 'scratch-game', 'who' => 'who-game', 'challenge' => 'challenge-game', 'know_me' => 'know-me-game'];
        $this->get('/games/'.$game->slug.'/play')->assertOk()->assertViewIs('games.'.$views[$type]);
        $this->get('/games/'.$game->slug)
            ->assertOk()
            ->assertSee('طريقة اللعب')
            ->assertSee('First step')
            ->assertSee('Second step');
    }

    public function test_instruction_seeder_populates_every_game_and_backs_up_existing_descriptions(): void
    {
        Storage::fake('local');

        foreach (array_column(self::types(), 0) as $index => $type) {
            Game::create([
                'name' => $type,
                'slug' => $type,
                'type' => $type,
                'description' => 'Old description '.$index,
                'is_free' => true,
            ]);
        }

        $this->seed(GameInstructionsSeeder::class);

        foreach (Game::all() as $game) {
            $this->assertStringNotContainsString('Old description', $game->description);
            $this->assertNotEmpty($game->how_to_play);
            $this->assertGreaterThanOrEqual(4, count(preg_split('/\R/u', $game->how_to_play)));
        }

        $backups = Storage::disk('local')->files('backups/game-descriptions');
        $this->assertCount(1, $backups);
        $this->assertStringContainsString('Old description 0', Storage::disk('local')->get($backups[0]));
    }
}
