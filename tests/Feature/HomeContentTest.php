<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Setting;
use App\Models\User;
use App\Support\HomeContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_defaults_and_hides_disabled_items(): void
    {
        $this->assertCount(100, HomeContent::defaults()['names']);
        $this->get('/')->assertOk()->assertSee('data-home-slider', false)->assertSee('data-countdown', false);
        Setting::set('home_slides', json_encode([
            ['id' => 'hidden', 'title' => 'Hidden slide', 'enabled' => false, 'sort_order' => 0],
        ]));
        $this->get('/')->assertOk()->assertDontSee('Hidden slide')->assertDontSee('data-home-slider', false);
    }

    public function test_admin_routes_are_protected_and_admin_page_renders(): void
    {
        $this->get('/admin/home-content')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/home-content')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/home-content')->assertOk()->assertSee('إدارة السلايدر');
    }

    public function test_review_upload_update_visibility_and_delete(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/home-content/reviews', ['title' => 'عميل سعيد', 'sort_order' => 2, 'enabled' => 1,
            'image' => UploadedFile::fake()->image('chat.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $item = HomeContent::items('reviews')[0];
        Storage::disk('public')->assertExists('homepage/'.basename($item['image']));
        $this->get('/')->assertOk()->assertSee('عميل سعيد')->assertSee('data-review-image', false);
        $this->post('/admin/home-content/reviews/'.$item['id'], ['title' => 'عميل سعيد', 'sort_order' => 1])
            ->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('عميل سعيد');
        $this->delete('/admin/home-content/reviews/'.$item['id'])->assertRedirect();
        Storage::disk('public')->assertMissing('homepage/'.basename($item['image']));
        $this->assertSame([], HomeContent::items('reviews'));
    }

    public function test_rejects_unsafe_links_and_non_images(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach (['javascript:alert(1)', '//evil.example', 'data:text/html,test'] as $url) {
            $this->post('/admin/home-content/slides', ['title' => 'Unsafe', 'sort_order' => 0, 'url' => $url])->assertSessionHasErrors('url');
        }
        $this->post('/admin/home-content/reviews', ['title' => 'Invalid', 'sort_order' => 0, 'image' => UploadedFile::fake()->create('bad.php', 2)])->assertSessionHasErrors('image');
        $this->post('/admin/home-content/reviews', ['title' => 'Missing', 'sort_order' => 0])->assertSessionHasErrors('image');
    }

    public function test_saving_settings_persists_names_and_countdown_version(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $settings = HomeContent::defaults();
        $settings['names'] = "أحمد\nمحمد\nأحمد";
        unset($settings['countdown_version']);
        $this->post('/admin/home-content/settings', $settings)->assertSessionHasNoErrors();
        $this->assertSame(['أحمد', 'محمد'], HomeContent::settings()['names']);
        $version = HomeContent::settings()['countdown_version'];
        $this->post('/admin/home-content/settings', $settings)->assertSessionHasNoErrors();
        $this->assertSame($version, HomeContent::settings()['countdown_version']);
        $this->post('/admin/home-content/settings', $settings + ['restart_countdown' => 1])->assertSessionHasNoErrors();
        $this->assertNotSame($version, HomeContent::settings()['countdown_version']);
        $settings['countdown_mode'] = 'fixed';
        $this->post('/admin/home-content/settings', $settings)->assertSessionHasErrors('ends_at');
    }

    public function test_notifications_only_include_selected_active_games(): void
    {
        $game = Game::create(['name' => 'Selected', 'slug' => 'selected', 'type' => 'card', 'is_free' => false, 'is_active' => true]);
        Game::create(['name' => 'Free', 'slug' => 'free', 'type' => 'card', 'is_free' => true, 'is_active' => true]);
        Setting::set('home_settings', json_encode(['game_ids' => [$game->id]]));
        $this->get('/')->assertOk()->assertViewHas('notificationGames', fn ($games) => $games->pluck('name')->all() === ['Selected']);
    }
}
