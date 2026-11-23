<?php

namespace Tests\Feature;

use App\Models\ChallengeCard;
use App\Models\ControlCard;
use App\Models\Game;
use App\Models\KnowMeQuestion;
use App\Models\Setting;
use App\Models\WhoQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GameTrialTest extends TestCase
{
    use RefreshDatabase;

    private function paidGame(string $type, array $attributes = []): Game
    {
        return Game::create(array_merge(['name' => 'Trial game', 'slug' => 'g-'.$type, 'type' => $type, 'is_free' => false, 'price' => 200, 'is_active' => true], $attributes));
    }

    /** Creates 8 items in reverse sort order so ordering bugs show up. Returns texts in expected order. */
    private function seedListItems(string $type): array
    {
        $texts = [];
        foreach (range(1, 8) as $i) {
            $text = "TRIAL-ITEM-{$type}-".str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $texts[] = $text;
            $sort = $i;
            match ($type) {
                'who' => WhoQuestion::create(['question' => $text, 'category' => 'funny', 'is_active' => true, 'sort_order' => $sort]),
                'challenge' => ChallengeCard::create(['title' => $text, 'description' => 'desc', 'category' => 'funny', 'is_active' => true, 'sort_order' => $sort]),
                'know_me' => KnowMeQuestion::create(['question' => $text, 'category' => 'personal', 'answer_type' => 'text', 'is_active' => true, 'sort_order' => $sort]),
                'control' => ControlCard::create(['title' => $text, 'description' => 'desc', 'is_active' => true, 'sort_order' => $sort]),
            };
        }

        return $texts;
    }

    public static function listTypes(): array
    {
        return [['who'], ['challenge'], ['know_me'], ['control']];
    }

    #[DataProvider('listTypes')]
    public function test_guest_gets_the_first_five_items_of_a_list_game(string $type): void
    {
        $texts = $this->seedListItems($type);
        $game = $this->paidGame($type);

        $first = $this->get('/games/'.$game->slug.'/play')->assertOk()->assertSee('تجربة مجانية');
        foreach (array_slice($texts, 0, 5) as $text) {
            $first->assertSee($text);
        }
        foreach (array_slice($texts, 5) as $text) {
            $first->assertDontSee($text);
        }

        $again = $this->get('/games/'.$game->slug.'/play')->assertOk();
        $this->assertSame($this->itemOrder($first->getContent(), $type), $this->itemOrder($again->getContent(), $type));
        $this->assertSame(array_slice($texts, 0, 5), $this->itemOrder($first->getContent(), $type));
    }

    #[DataProvider('listTypes')]
    public function test_subscriber_gets_every_item_without_the_trial_banner(string $type): void
    {
        $texts = $this->seedListItems($type);
        $game = $this->paidGame($type);
        $this->signInMember();

        $response = $this->get('/games/'.$game->slug.'/play')->assertOk()->assertDontSee('تجربة مجانية');
        foreach ($texts as $text) {
            $response->assertSee($text);
        }
    }

    public function test_trial_size_follows_the_admin_setting_and_is_clamped(): void
    {
        $texts = $this->seedListItems('who');
        $game = $this->paidGame('who');

        Setting::set('trial_items', '3');
        $response = $this->get('/games/'.$game->slug.'/play')->assertOk();
        $response->assertSee($texts[2])->assertDontSee($texts[3]);

        Setting::set('trial_items', '0');
        $this->assertSame(1, \App\Support\GameTrial::size());
        Setting::set('trial_items', '500');
        $this->assertSame(20, \App\Support\GameTrial::size());
        Setting::set('trial_items', 'abc');
        $this->assertSame(5, \App\Support\GameTrial::size());
    }

    public function test_trial_paywall_links_to_the_game_subscription(): void
    {
        $this->seedListItems('who');
        $game = $this->paidGame('who');

        $this->get('/games/'.$game->slug.'/play')->assertOk()
            ->assertSee(route('subscribe.create', $game->id), false)
            ->assertSee(route('subscribe.bundle'), false);
    }

    private function seedCardLevels(): array
    {
        $easy = \App\Models\CardLevel::create(['name' => 'Easy', 'slug' => 'easy', 'color' => '#fff', 'sort_order' => 1]);
        $hard = \App\Models\CardLevel::create(['name' => 'Hard', 'slug' => 'hard', 'color' => '#000', 'sort_order' => 2]);
        $texts = [];
        foreach (range(1, 8) as $i) {
            $text = 'TRIAL-ITEM-card-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $texts[] = $text;
            \App\Models\Card::create(['card_level_id' => $easy->id, 'content' => $text, 'target' => $i % 2 ? 'male' : 'female', 'is_active' => true, 'sort_order' => $i]);
        }
        \App\Models\Card::create(['card_level_id' => $hard->id, 'content' => 'HARD-ONLY', 'target' => 'both', 'is_active' => true, 'sort_order' => 1]);

        return $texts;
    }

    public function test_card_api_gives_guests_the_same_first_five_cards_of_the_first_level(): void
    {
        $texts = $this->seedCardLevels();
        $game = $this->paidGame('card');

        foreach (['all', 'male'] as $target) {
            $cards = $this->getJson('/api/cards/easy?game_id='.$game->id.'&target='.$target)->assertOk()->json();
            $this->assertSame(array_slice($texts, 0, 5), array_column($cards, 'content'));
        }
        $this->getJson('/api/cards/hard?game_id='.$game->id)->assertForbidden();
    }

    public function test_card_game_page_locks_other_levels_in_trial(): void
    {
        $this->seedCardLevels();
        $game = $this->paidGame('card');

        $this->get('/games/'.$game->slug.'/play')->assertOk()
            ->assertSee('تجربة مجانية')
            ->assertSee('data-trial-locked="hard"', false)
            ->assertDontSee('data-trial-locked="easy"', false);
    }

    public function test_subscriber_card_api_returns_the_whole_level(): void
    {
        $this->seedCardLevels();
        $game = $this->paidGame('card');
        $this->signInMember();

        $this->assertCount(8, $this->getJson('/api/cards/easy?game_id='.$game->id.'&target=all')->assertOk()->json());
        $this->getJson('/api/cards/hard?game_id='.$game->id)->assertOk();
    }

    private function seedScratch(): array
    {
        $texts = [];
        foreach (range(1, 8) as $i) {
            $text = 'TRIAL-ITEM-scratch-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $texts[$i] = $text;
            \App\Models\ScratchCard::create(['number' => $i, 'content' => $text, 'level' => 1, 'is_active' => true, 'sort_order' => $i]);
        }

        return $texts;
    }

    public function test_scratch_trial_sends_five_cards_and_blocks_the_rest(): void
    {
        $texts = $this->seedScratch();
        $game = $this->paidGame('scratch');

        $page = $this->get('/games/'.$game->slug.'/play')->assertOk()->assertSee('تجربة مجانية');
        $this->assertSame(5, substr_count($page->getContent(), 'class="scratch-silver"'));
        $page->assertSee($texts[5])->assertDontSee($texts[6]);

        $this->getJson('/games/'.$game->slug.'/scratch-cards/5')->assertOk()->assertJson(['content' => $texts[5]]);
        $this->getJson('/games/'.$game->slug.'/scratch-cards/6')->assertForbidden();
    }

    public function test_spinner_trial_has_a_fixed_result_sequence_of_the_first_five_items(): void
    {
        $ids = [];
        foreach (range(1, 8) as $i) {
            $ids[] = \App\Models\SpinnerImage::create(['name' => 'Spin '.$i, 'image' => 'spinner/placeholder.png', 'color' => '#123456', 'is_active' => true, 'sort_order' => 9 - $i])->id;
        }
        $game = $this->paidGame('spinner');

        $content = $this->get('/games/'.$game->slug.'/play')->assertOk()->assertSee('تجربة مجانية')->getContent();
        $this->assertMatchesRegularExpression('/globalThis.spinnerTrialSequence = (\[[^\]]*\]);/', $content);
        preg_match('/globalThis.spinnerTrialSequence = (\[[^\]]*\]);/', $content, $match);
        $this->assertSame(array_slice(array_reverse($ids), 0, 5), json_decode($match[1], true));

        $this->signInMember();
        $this->get('/games/'.$game->slug.'/play')->assertOk()->assertSee('globalThis.spinnerTrialSequence = null;', false);
    }

    public function test_snakes_trial_destinations_follow_the_fixed_dice(): void
    {
        $this->assertSame([22, 6, 6, 30, 10], \App\Support\GameTrial::snakesDestinations([3, 6, 5, 2, 4], config('snakes.links')));
        $this->assertSame([3, 6, 5, 2, 4], \App\Support\GameTrial::snakesDice());
    }

    public function test_snakes_trial_page_only_reveals_reachable_squares(): void
    {
        foreach (range(1, 100) as $n) {
            \App\Models\SnakeCell::create(['number' => $n, 'title' => 'T'.$n, 'content' => sprintf('SNAKE-CONTENT-%03d', $n), 'mood' => 'warm', 'is_active' => true]);
        }
        $game = $this->paidGame('snakes');

        $page = $this->get('/games/'.$game->slug.'/play')->assertOk()->assertSee('تجربة مجانية');
        foreach ([6, 10, 22, 30] as $n) {
            $page->assertSee(sprintf('SNAKE-CONTENT-%03d', $n));
        }
        $page->assertDontSee('SNAKE-CONTENT-005')->assertDontSee('SNAKE-CONTENT-050');
        $page->assertSee('"trialDice":[3,6,5,2,4]', false);

        $this->signInMember();
        $this->get('/games/'.$game->slug.'/play')->assertOk()->assertSee('SNAKE-CONTENT-050')->assertDontSee('"trialDice"', false);
    }

    public function test_game_page_offers_a_free_trial_to_visitors_without_a_subscription(): void
    {
        $game = $this->paidGame('who');

        $this->get('/games/'.$game->slug)->assertOk()
            ->assertSee('جرّب مجاناً')
            ->assertSee(route('games.play', $game->slug), false);

        $this->signInMember();
        $this->get('/games/'.$game->slug)->assertOk()->assertDontSee('جرّب مجاناً');
    }

    public function test_admin_can_save_trial_size_and_activation_eta_with_validation(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['is_admin' => true]));

        $this->post('/admin/settings', ['trial_items' => '7', 'activation_eta' => 'خلال ساعتين'])->assertSessionHasNoErrors();
        $this->assertSame(7, \App\Support\GameTrial::size());
        $this->assertSame('خلال ساعتين', Setting::get('activation_eta'));

        $this->post('/admin/settings', ['trial_items' => '50'])->assertSessionHasErrors('trial_items');
        $this->get('/admin/settings')->assertOk()->assertSee('name="trial_items"', false)->assertSee('name="activation_eta"', false);
    }

    public static function mediaTypes(): array
    {
        return [['control'], ['challenge'], ['scratch'], ['spinner']];
    }

    /** Creates 7 items with images, sorted in reverse id order. Returns ids in trial order. */
    private function seedMediaItems(string $type): array
    {
        $ids = [];
        foreach (range(1, 7) as $i) {
            $image = "media/{$type}-{$i}.png";
            \Illuminate\Support\Facades\Storage::disk('premium')->put($image, 'bytes');
            $base = ['image' => $image, 'is_active' => true, 'sort_order' => 8 - $i];
            $ids[] = match ($type) {
                'control' => ControlCard::create([...$base, 'title' => "C{$i}", 'description' => 'd'])->id,
                'challenge' => ChallengeCard::create([...$base, 'title' => "C{$i}", 'description' => 'd', 'category' => 'funny'])->id,
                'scratch' => \App\Models\ScratchCard::create([...$base, 'number' => $i, 'content' => "S{$i}", 'level' => 1])->id,
                'spinner' => \App\Models\SpinnerImage::create([...$base, 'name' => "P{$i}", 'color' => '#123456'])->id,
            };
        }

        return array_reverse($ids);
    }

    #[DataProvider('mediaTypes')]
    public function test_guest_can_load_images_of_trial_items_only(string $type): void
    {
        \Illuminate\Support\Facades\Storage::fake('premium');
        $game = $this->paidGame($type);
        $ids = $this->seedMediaItems($type);
        $url = fn (int $id) => route('games.media', ['type' => $type, 'id' => $id]);

        foreach (array_slice($ids, 0, 5) as $id) {
            $this->get($url($id))->assertOk();
        }
        foreach (array_slice($ids, 5) as $id) {
            $this->get($url($id))->assertForbidden();
        }

        $this->actingAs(\App\Models\User::factory()->create())->get($url($ids[0]))->assertOk();
        $this->get($url($ids[6]))->assertForbidden();

        $game->update(['is_active' => false]);
        $this->get($url($ids[0]))->assertForbidden();
    }

    private function itemOrder(string $html, string $type): array
    {
        preg_match_all('/TRIAL-ITEM-'.preg_quote($type, '/').'-\d{2}/', $html, $matches);

        return array_values(array_unique($matches[0]));
    }
}
