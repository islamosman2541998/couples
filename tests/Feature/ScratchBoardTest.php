<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\ScratchCard;
use App\Models\User;
use Database\Seeders\ScratchBoardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScratchBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_expansion_preserves_existing_tasks_and_builds_three_levels(): void
    {
        $old = ScratchCard::create(['number' => 1, 'content' => 'My existing task', 'image' => 'scratch/my-image.jpg', 'is_active' => true]);
        $this->seed(ScratchBoardSeeder::class);
        $this->assertDatabaseCount('scratch_cards', 100);
        $this->assertSame('My existing task', $old->fresh()->content);
        $this->assertSame('scratch/my-image.jpg', $old->fresh()->image);
        foreach ([1 => 30, 2 => 30, 3 => 40] as $level => $count) {
            $this->assertSame($count, ScratchCard::where('level', $level)->count());
        }
        $this->seed(ScratchBoardSeeder::class);
        $this->assertDatabaseCount('scratch_cards', 100);
        $game = Game::create(['name' => 'Scratch', 'slug' => 'scratch', 'type' => 'scratch', 'is_free' => true]);
        $response = $this->get('/games/scratch/play')->assertOk()->assertSee('لوحة خربش والعب')->assertSee('نفذنا المهمة');
        $this->assertSame(100, substr_count($response->getContent(), 'class="scratch-silver"'));
    }

    public function test_admin_can_set_a_task_level_and_invalid_levels_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $data = ['number' => 1, 'content' => 'Test task', 'level' => 3, 'is_active' => 1];
        $this->post('/admin/scratch-cards', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('scratch_cards', ['number' => 1, 'level' => 3]);
        $card = ScratchCard::first();
        $this->get('/admin/scratch-cards/'.$card->id.'/edit')->assertSee('name="level"', false);
        $data['level'] = 7;
        $this->put('/admin/scratch-cards/'.$card->id, $data)->assertSessionHasErrors('level');
    }

    public function test_the_opened_task_fetches_the_latest_uploaded_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->post('/admin/scratch-cards', [
            'number' => 10,
            'content' => 'Task with an image',
            'level' => 1,
            'image' => UploadedFile::fake()->image('first.jpg'),
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $card = ScratchCard::firstOrFail();
        $firstImage = $card->image;
        Storage::disk('public')->assertExists($firstImage);

        Game::create(['name' => 'Scratch', 'slug' => 'scratch', 'type' => 'scratch', 'is_free' => true, 'is_active' => true]);
        $this->get('/games/scratch/play')
            ->assertOk()
            ->assertSee('const scratchCardEndpoint', false)
            ->assertSee('__NUMBER__', false);
        $latestCardResponse = $this->getJson('/games/scratch/scratch-cards/10')
            ->assertOk()
            ->assertJsonPath('number', 10)
            ->assertJsonPath('image', fn ($url) => str_contains($url, $firstImage));
        $this->assertStringContainsString('no-store', $latestCardResponse->headers->get('Cache-Control'));

        $this->put('/admin/scratch-cards/'.$card->id, [
            'number' => 10,
            'content' => 'Task with an image',
            'level' => 1,
            'image' => UploadedFile::fake()->image('replacement.jpg'),
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $replacementImage = $card->fresh()->image;
        $this->assertNotSame($firstImage, $replacementImage);
        Storage::disk('public')->assertMissing($firstImage);
        Storage::disk('public')->assertExists($replacementImage);
        $this->getJson('/games/scratch/scratch-cards/10')
            ->assertOk()
            ->assertJsonPath('image', fn ($url) => str_contains($url, $replacementImage));
    }
}
