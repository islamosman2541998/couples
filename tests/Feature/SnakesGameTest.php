<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\SnakeCell;
use App\Models\User;
use Database\Seeders\SnakesGameSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SnakesGameTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_adds_one_hundred_unique_cells_and_preserves_existing_edits(): void
    {
        $this->seed(SnakesGameSeeder::class);
        $this->assertDatabaseCount('snake_cells', 100);
        $this->assertSame(range(1, 100), SnakeCell::orderBy('number')->pluck('number')->all());
        $this->assertSame(100, SnakeCell::distinct()->count('content'));
        $cell = SnakeCell::where('number', 5)->firstOrFail();
        $cell->update(['title' => 'Custom task', 'is_active' => false]);
        $game = Game::where('type', 'snakes')->firstOrFail();
        $game->update(['price' => 30, 'is_free' => false]);
        $this->seed(SnakesGameSeeder::class);
        $this->assertDatabaseCount('snake_cells', 100);
        $this->assertDatabaseCount('games', 1);
        $this->assertSame('Custom task', $cell->fresh()->title);
        $this->assertFalse($cell->fresh()->is_active);
        $this->assertFalse($game->fresh()->is_free);
    }

    public function test_board_has_safe_links_and_hides_disabled_task_text(): void
    {
        $this->seed(SnakesGameSeeder::class);
        SnakeCell::where('number', 10)->update(['is_active' => false, 'content' => 'Hidden content']);
        $this->get('/')->assertOk()->assertSee('/games/snakes-and-ladders/play');
        $this->get('/games/snakes-and-ladders/play')->assertOk()->assertViewIs('games.snakes-game')
            ->assertDontSee('Hidden content')->assertViewHas('cells', fn ($cells) => count($cells) === 100 && $cells[9]['active'] === false && $cells[9]['title'] === 'استراحة');
        $links = config('snakes.links');
        $this->assertCount(12, $links);
        $this->assertCount(12, array_unique(array_column($links, 'from')));
        foreach ($links as $link) {
            $this->assertGreaterThan(1, $link['from']);
            $this->assertLessThan(100, $link['from']);
            $this->assertGreaterThanOrEqual(1, $link['to']);
            $this->assertLessThan(100, $link['to']);
            $this->assertNotContains($link['to'], array_column($links, 'from'));
        }
    }

    public function test_admin_can_edit_a_task_but_cannot_change_board_numbers(): void
    {
        $this->seed(SnakesGameSeeder::class);
        $cell = SnakeCell::firstOrFail();
        $this->get('/admin/snake-cells')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->put('/admin/snake-cells/'.$cell->id)->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/admin/snake-cells')->assertOk();
        $this->get('/admin/snake-cells/'.$cell->id.'/edit')->assertOk();
        $this->put('/admin/snake-cells/'.$cell->id, ['title' => 'Edited', 'content' => 'Custom', 'mood' => 'romantic', 'is_active' => 0, 'number' => 100])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(1, $cell->fresh()->number);
        $this->assertSame('Edited', $cell->fresh()->title);
        $this->assertFalse($cell->fresh()->is_active);
        $this->put('/admin/snake-cells/'.$cell->id, ['title' => '', 'content' => '', 'mood' => 'unknown', 'is_active' => 5])
            ->assertSessionHasErrors(['title', 'content', 'mood', 'is_active']);
    }
}
