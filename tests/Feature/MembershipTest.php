<?php

namespace Tests\Feature;

use App\Models\{Game, Setting, Subscription, User, ScratchCard};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundle_checkout_records_server_price_and_only_approval_unlocks_games(): void
    {
        Storage::fake('local');
        Setting::set('membership_price', '700');
        $game = Game::create(['name' => 'Scratch', 'slug' => 'scratch', 'type' => 'scratch', 'is_free' => true]);
        ScratchCard::create(['number' => 1, 'content' => 'PRIVATE TASK', 'is_active' => true]);
        $this->get('/')->assertOk()->assertSee('700')->assertDontSee('PRIVATE TASK');
        $this->get('/games/scratch')->assertOk()->assertDontSee('PRIVATE TASK');
        $this->get('/games/scratch/play')->assertRedirect();
        $this->getJson('/games/scratch/scratch-cards/1')->assertForbidden();
        $this->get('/membership')->assertOk()->assertSee('باقة كل الألعاب');
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/subscribe', [
            'game_id' => 'all', 'amount' => 1, 'status' => 'approved',
            'full_name' => $user->name, 'email' => $user->email, 'phone' => '01012345678',
            'receipt_image' => UploadedFile::fake()->image('receipt.png'),
        ])->assertCreated();
        $sub = Subscription::firstOrFail();
        $this->assertTrue($sub->is_bundle);
        $this->assertNull($sub->game_id);
        $this->assertSame('700.00', $sub->amount);
        $this->assertSame('pending', $sub->status);
        $this->get('/games/scratch/play')->assertRedirect();
        $this->get('/my-profile')->assertOk()->assertSee('باقة كل الألعاب');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/subscriptions/'.$sub->id)->assertOk()->assertSee('باقة كل الألعاب');
        $this->get('/admin')->assertOk()->assertSee('باقة كل الألعاب');
        $this->patch('/admin/subscriptions/'.$sub->id.'/approve')->assertRedirect();
        $this->actingAs($user)->get('/games/scratch/play')->assertOk()->assertSee('PRIVATE TASK');
        $this->getJson('/games/scratch/scratch-cards/1')->assertOk();
        $sub->update(['expires_at' => now()->subMinute()]);
        $this->get('/games/scratch/play')->assertRedirect();
        $this->getJson('/games/scratch/scratch-cards/1')->assertForbidden();
        $sub->update(['expires_at' => null, 'status' => 'rejected']);
        $this->get('/games/scratch/play')->assertRedirect();
        $sub->update(['status' => 'approved']);
        $user->update(['is_active' => false]);
        $this->assertFalse($user->hasActiveSubscription($game->id));
    }

    public function test_private_media_requires_membership_and_active_content(): void
    {
        Storage::fake('premium');
        Storage::disk('premium')->put('scratch/example.png', 'image bytes');
        $game = Game::create(['name' => 'Scratch', 'slug' => 'scratch', 'type' => 'scratch', 'is_free' => true]);
        $card = ScratchCard::create(['number' => 1, 'content' => 'Secret', 'image' => 'scratch/example.png', 'is_active' => true]);
        $url = $card->image_url;
        $this->get($url)->assertForbidden();
        $user = User::factory()->create();
        $this->actingAs($user)->get($url)->assertForbidden();
        $this->signInMember($user);
        $response = $this->get($url)->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $game->update(['is_active' => false]);
        $this->get($url)->assertForbidden();
        $game->update(['is_active' => true]);
        $card->update(['is_active' => false]);
        $this->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get($url)->assertOk();
    }

    public function test_admin_price_is_validated_and_bundle_unavailable_without_games(): void
    {
        Setting::set('membership_price', '700');
        $this->get('/membership')->assertNotFound();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/settings', ['membership_price' => -1])->assertSessionHasErrors('membership_price');
        $this->assertSame('700', Setting::get('membership_price'));
    }
}
