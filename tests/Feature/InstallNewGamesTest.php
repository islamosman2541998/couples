<?php

namespace Tests\Feature;

use App\Models\ControlCard;
use App\Models\Game;
use App\Models\SnakeCell;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstallNewGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_install_games(): void
    {
        $this->post('/admin/games/install-new')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->post('/admin/games/install-new')->assertForbidden();
        $this->assertDatabaseCount('games', 0);
    }

    public function test_install_adds_playable_games_and_preserves_edits_on_repeat(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/games/install-new')->assertSessionHas('success');
        $this->assertDatabaseCount('games', 2);
        $this->assertDatabaseCount('control_cards', 50);
        $this->assertDatabaseCount('snake_cells', 100);
        $this->get('/')->assertSee('لعبة السيطرة')->assertSee('السلم والتعبان');
        $this->get('/games/control-game/play')->assertOk();
        $this->get('/games/snakes-and-ladders/play')->assertOk();
        $game = Game::where('type', 'control')->firstOrFail();
        $game->update(['name' => 'اسمي الخاص', 'price' => 75, 'is_free' => false, 'is_active' => false]);
        $card = ControlCard::firstOrFail();
        $card->update(['title' => 'كارت معدل']);
        $cell = SnakeCell::firstOrFail();
        $cell->update(['content' => 'محتوى معدل']);
        $this->post('/admin/games/install-new')->assertSessionHas('success');
        $this->assertDatabaseCount('games', 2);
        $this->assertDatabaseCount('control_cards', 50);
        $this->assertSame('اسمي الخاص', $game->fresh()->name);
        $this->assertSame('75.00', $game->fresh()->price);
        $this->assertFalse($game->fresh()->is_active);
        $this->assertSame('كارت معدل', $card->fresh()->title);
        $this->assertSame('محتوى معدل', $cell->fresh()->content);
    }

    public function test_install_creates_missing_tables_on_an_old_database(): void
    {
        Schema::drop('control_cards');
        Schema::drop('snake_cells');
        DB::table('migrations')->whereIn('migration', [
            '2026_09_07_000001_create_control_cards_table',
            '2026_09_07_000002_create_snake_cells_table',
        ])->delete();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/games/install-new')->assertSessionHas('success');
        $this->assertDatabaseCount('control_cards', 50);
        $this->assertDatabaseCount('snake_cells', 100);
        $this->assertDatabaseCount('games', 2);
    }
}
