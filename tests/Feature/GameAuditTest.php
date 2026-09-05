<?php

namespace Tests\Feature;

use App\Models\{Game, User, CardLevel, Card, Subscription, SpinnerImage, ScratchCard, WhoQuestion, ChallengeCard, KnowMeQuestion};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GameAuditTest extends TestCase
{
    use RefreshDatabase;

    public static function types(): array
    {
        return array_map(fn ($type) => [$type], ['card', 'spinner', 'scratch', 'who', 'challenge', 'know_me']);
    }

    private function game(string $type = 'card', array $attributes = []): Game
    {
        return Game::create(array_merge(['name' => 'Test game', 'slug' => $type, 'type' => $type, 'is_free' => true, 'is_active' => true], $attributes));
    }

    private function subscription(Game $game, User $user, array $attributes = []): Subscription
    {
        return Subscription::create(array_merge(['game_id' => $game->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '01234567890', 'email' => $user->email, 'receipt_image' => 'receipts/test.png', 'status' => 'approved'], $attributes));
    }

    #[DataProvider('types')]
    public function test_each_game_renders_and_enforces_access(string $type): void
    {
        $game = $this->game($type);
        $this->get('/games/'.$game->slug)->assertOk();
        $this->get('/games/'.$game->slug.'/play')->assertOk();
        $game->update(['is_free' => false]);
        $this->get('/games/'.$game->slug.'/play')->assertRedirect(route('subscribe.create', $game->id));
        $user = User::factory()->create();
        $this->actingAs($user)->get('/games/'.$game->slug.'/play')->assertRedirect();
        $sub = $this->subscription($game, $user);
        $this->get('/games/'.$game->slug.'/play')->assertOk();
        $sub->update(['expires_at' => now()->subSecond()]);
        $this->get('/games/'.$game->slug.'/play')->assertRedirect();
        $sub->update(['expires_at' => null, 'status' => 'pending']);
        $this->get('/games/'.$game->slug.'/play')->assertRedirect();
        $game->update(['is_active' => false]);
        $this->get('/games/'.$game->slug)->assertNotFound();
        $this->get('/games/'.$game->slug.'/play')->assertNotFound();
    }

    public function test_card_api_requires_the_correct_game_subscription_and_filters_content(): void
    {
        $game = $this->game('card', ['is_free' => false]);
        $level = CardLevel::create(['name' => "Test's level", 'slug' => 'easy', 'color' => '#ffffff']);
        foreach (['male', 'female', 'both'] as $target) {
            Card::create(['card_level_id' => $level->id, 'content' => $target, 'target' => $target, 'is_active' => true]);
        }
        Card::create(['card_level_id' => $level->id, 'content' => 'hidden', 'target' => 'both', 'is_active' => false]);
        $url = '/api/cards/easy?game_id='.$game->id.'&target=all';
        $this->getJson($url)->assertForbidden();
        $user = User::factory()->create();
        $this->actingAs($user)->getJson($url)->assertForbidden();
        $other = $this->game('spinner');
        $this->subscription($other, $user);
        $this->getJson($url)->assertForbidden();
        $sub = $this->subscription($game, $user);
        $this->getJson($url)->assertOk()->assertJsonCount(3)->assertJsonMissing(['content' => 'hidden']);
        $this->getJson('/api/cards/easy?game_id='.$game->id.'&target=male')->assertOk()->assertJsonCount(2)->assertJsonMissing(['content' => 'female']);
        $this->getJson('/api/cards/easy')->assertUnprocessable();
        $this->getJson('/api/cards/easy?game_id='.$other->id)->assertNotFound();
        $sub->update(['expires_at' => now()->subDay()]);
        $this->getJson($url)->assertForbidden();
        $game->update(['is_free' => true]);
        $this->getJson($url)->assertOk();
        $game->update(['is_active' => false]);
        $this->getJson($url)->assertNotFound();
    }

    public function test_game_pages_render_populated_content_and_exclude_inactive_items(): void
    {
        $fixtures = [
            'spinner' => [SpinnerImage::class, ['name' => 'Visible item', 'image' => 'spinner/placeholder.png', 'color' => '#ffffff'], 'spinnerData'],
            'scratch' => [ScratchCard::class, ['number' => 1, 'content' => 'Visible item'], 'cards'],
            'who' => [WhoQuestion::class, ['question' => 'Visible item', 'category' => 'funny'], 'questions'],
            'challenge' => [ChallengeCard::class, ['title' => 'Visible item', 'description' => 'Test', 'category' => 'general', 'timer' => 2], 'cards'],
            'know_me' => [KnowMeQuestion::class, ['question' => 'Visible item', 'category' => 'daily', 'answer_type' => 'choice', 'choices' => ['A', 'B']], 'questions'],
        ];
        foreach ($fixtures as $type => [$model, $data, $variable]) {
            $game = $this->game($type);
            $model::create($data + ['is_active' => true]);
            if ($type === 'scratch') $data['number'] = 2;
            $model::create($data + ['is_active' => false]);
            $this->get('/games/'.$game->slug.'/play')->assertOk()->assertViewHas($variable, fn ($items) => count($items) === 1);
        }
    }

    public function test_subscriptions_are_attached_to_a_signed_in_user_and_valid_paid_game(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $game = $this->game('card', ['is_free' => false]);
        $this->post('/subscribe')->assertRedirect(route('login'));
        $user = User::factory()->create();
        $payload = ['game_id' => $game->id, 'full_name' => 'Test', 'phone' => '01234567890', 'email' => $user->email, 'receipt_image' => UploadedFile::fake()->image('receipt.png')];
        $this->actingAs($user)->post('/subscribe', $payload)->assertRedirect(route('subscribe.success'));
        $this->assertDatabaseHas('subscriptions', ['game_id' => $game->id, 'user_id' => $user->id, 'status' => 'pending']);
        $subscription = Subscription::firstOrFail();
        Storage::disk('public')->assertMissing($subscription->receipt_image);
        Storage::disk('local')->assertExists($subscription->receipt_image);
        $this->get($subscription->receipt_url)->assertForbidden();
        $game->update(['is_active' => false]);
        $this->post('/subscribe', $payload)->assertSessionHasErrors('game_id');
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get($subscription->receipt_url)->assertOk();
        $this->post('/logout');
        $this->get($subscription->receipt_url)->assertRedirect(route('login'));
        $this->actingAs($user)->get('/subscribe/'.$game->id)->assertNotFound();
        $game->update(['is_active' => true, 'is_free' => true]);
        $this->post('/subscribe', $payload)->assertSessionHasErrors('game_id');
    }

    public function test_public_and_admin_pages_and_disabled_accounts(): void
    {
        foreach (['/', '/about', '/privacy', '/contact', '/login', '/register'] as $url) $this->get($url)->assertOk();
        $this->get('/admin')->assertRedirect(route('login'));
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $user->update(['is_admin' => true]);
        $sidebar = $this->get('/admin')->assertOk();
        foreach ([
            'بيانات الألعاب',
            'الاسم والصورة والوصف وطريقة اللعب',
            'لعبة الكروت',
            'الكروت والأحكام',
            'لعبة السبينر',
            'صور ونتائج العجلة',
            'خربش والعب',
            'المهام ومستويات النقاط',
            'بوس أو دوس',
            'أسئلة أنا أو شريكي',
            'شوق أو دوق',
            'كروت التحديات',
            'لعبة المخطوبين',
            'أسئلة عارف شريكك؟',
        ] as $sidebarText) {
            $sidebar->assertSee($sidebarText);
        }
        foreach (['', '/games', '/cards', '/card-levels', '/spinner-images', '/scratch-cards', '/who-questions', '/challenge-cards', '/know-me', '/subscriptions', '/users', '/settings'] as $path) {
            $this->get('/admin'.$path)->assertOk();
            if (!in_array($path, ['', '/subscriptions', '/users', '/settings'])) $this->get('/admin'.$path.'/create')->assertOk();
        }
        $this->get('/my-profile')->assertOk();
        $this->get('/my-profile/edit')->assertOk();
        $user->update(['is_active' => false]);
        $this->get('/admin')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_manage_content_and_uncheck_active(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $level = CardLevel::create(['name' => 'Easy', 'slug' => 'easy', 'color' => '#ffffff']);
        $fixtures = [
            ['cards', Card::class, ['card_level_id' => $level->id, 'content' => 'Test', 'target' => 'both', 'sort_order' => 0]],
            ['scratch-cards', ScratchCard::class, ['number' => 1, 'content' => 'Test', 'sort_order' => '']],
            ['who-questions', WhoQuestion::class, ['question' => 'Test', 'category' => 'funny', 'challenge' => 'Test', 'sort_order' => '']],
            ['challenge-cards', ChallengeCard::class, ['title' => 'Test', 'description' => 'Test', 'category' => 'general', 'timer' => '', 'sort_order' => '']],
            ['know-me', KnowMeQuestion::class, ['question' => 'Test', 'category' => 'daily', 'answer_type' => 'choice', 'choices' => ['0', '1', ''], 'sort_order' => '']],
            ['spinner-images', SpinnerImage::class, ['name' => 'Test', 'image' => UploadedFile::fake()->image('spinner.png'), 'color' => '#ffffff', 'sort_order' => 0]],
        ];
        foreach ($fixtures as [$path, $model, $payload]) {
            $this->post('/admin/'.$path, $payload + ['is_active' => 1])->assertSessionHasNoErrors()->assertRedirect();
            $item = $model::firstOrFail();
            $this->get('/admin/'.$path.'/'.$item->id.'/edit')->assertOk();
            unset($payload['image']);
            $this->put('/admin/'.$path.'/'.$item->id, $payload)->assertSessionHasNoErrors()->assertRedirect();
            $this->assertFalse((bool) $item->fresh()->is_active);
            if ($path === 'know-me') $this->assertSame(['0', '1'], $item->fresh()->choices);
            $this->delete('/admin/'.$path.'/'.$item->id)->assertRedirect();
            $this->assertNull($item->fresh());
        }
        $this->post('/admin/know-me', ['question' => 'Invalid', 'category' => 'daily', 'answer_type' => 'choice', 'choices' => ['Only one', '']])->assertSessionHasErrors('choices');
    }

    public function test_game_management_handles_duplicate_names_and_preserves_links(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $payload = ['name' => 'Test game', 'type' => 'card', 'price' => 0, 'is_free' => 1, 'is_active' => 1];
        $this->post('/admin/games', $payload)->assertSessionHasNoErrors();
        $this->post('/admin/games', $payload)->assertSessionHasNoErrors();
        $this->assertSame(2, Game::distinct()->count('slug'));
        $game = Game::first();
        unset($payload['is_active']);
        $payload['name'] = 'Renamed game';
        $this->put('/admin/games/'.$game->id, $payload)->assertSessionHasNoErrors();
        $this->assertSame($game->slug, $game->fresh()->slug);
        $this->assertFalse($game->fresh()->is_active);
    }
}
