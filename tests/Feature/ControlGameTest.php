<?php

namespace Tests\Feature;

use App\Models\ControlCard;
use App\Models\Game;
use App\Models\User;
use Database\Seeders\ControlGameSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ControlGameTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_is_repeatable_and_preserves_edited_cards_and_game_settings(): void
    {
        $this->seed(ControlGameSeeder::class);
        $game = Game::where('slug', 'control-game')->firstOrFail();
        $game->update(['price' => 42, 'is_free' => false]);
        $card = ControlCard::firstOrFail();
        $card->update(['title' => 'Edited title', 'is_active' => false]);
        $this->seed(ControlGameSeeder::class);

        $this->assertDatabaseCount('control_cards', 50);
        $this->assertDatabaseCount('games', 1);
        $this->assertSame('Edited title', $card->fresh()->title);
        $this->assertFalse($card->fresh()->is_active);
        $this->assertFalse($game->fresh()->is_free);
        $this->assertSame('42.00', $game->fresh()->price);
    }

    public function test_game_is_listed_and_only_active_cards_are_passed_to_the_player(): void
    {
        $this->seed(ControlGameSeeder::class);
        $card = ControlCard::firstOrFail();
        $card->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertSee('لعبة السيطرة')->assertSee('/games/control-game/play');
        $this->get('/games/control-game')->assertOk()->assertSee('أول رفض مجاني');
        $this->get('/games/control-game/play')->assertOk()->assertViewIs('games.control-game')
            ->assertViewHas('cards', fn ($cards) => count($cards) === 49 && !in_array($card->id, array_column($cards, 'id')));
        ControlCard::query()->update(['is_active' => false]);
        $this->get('/games/control-game/play')->assertOk()->assertViewHas('cards', []);
    }

    public function test_admin_can_manage_cards_images_and_activation(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/admin/control-cards/create')->assertOk();
        $data = ['title' => 'A card', 'description' => 'A challenge', 'sort_order' => 1, 'is_active' => 1];
        $this->post('/admin/control-cards', $data + ['image' => UploadedFile::fake()->image('card.jpg')])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.control-cards.index'));
        $card = ControlCard::firstOrFail();
        $image = $card->image;
        Storage::disk('public')->assertExists($image);
        $this->get('/admin/control-cards')->assertOk()->assertSee('A card');
        $this->get('/admin/control-cards/'.$card->id.'/edit')->assertOk();
        $this->put('/admin/control-cards/'.$card->id, [...$data, 'is_active' => 0, 'remove_image' => 1])
            ->assertSessionHasNoErrors();
        $this->assertFalse($card->fresh()->is_active);
        $this->assertNull($card->fresh()->image);
        Storage::disk('public')->assertMissing($image);
        $this->delete('/admin/control-cards/'.$card->id)->assertRedirect();
        $this->assertDatabaseCount('control_cards', 0);
    }

    public function test_card_management_rejects_non_admins_and_invalid_content(): void
    {
        $this->get('/admin/control-cards')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post('/admin/control-cards', [])->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/control-cards', ['title' => '', 'description' => '', 'sort_order' => -1, 'is_active' => 3])
            ->assertSessionHasErrors(['title', 'description', 'sort_order', 'is_active']);
        $this->assertDatabaseCount('control_cards', 0);
    }
}
