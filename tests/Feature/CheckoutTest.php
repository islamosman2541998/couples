<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function game(): Game
    {
        return Game::create(['name' => 'Paid game', 'slug' => 'paid-checkout', 'type' => 'card', 'price' => 100, 'is_free' => false, 'is_active' => true]);
    }

    public function test_guest_can_open_checkout_but_cannot_submit_receipt(): void
    {
        $game = $this->game();
        $this->get('/subscribe/'.$game->id)->assertOk()->assertSee('مستخدم جديد')->assertSee('لديك حساب');
        $this->postJson('/subscribe', ['game_id' => $game->id])->assertUnauthorized();
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_registration_then_receipt_upload_uses_same_authenticated_session(): void
    {
        Storage::fake('local');
        $game = $this->game();
        $response = $this->postJson('/checkout/register', ['name' => 'عميل جديد', 'email' => 'NEW@example.com', 'phone' => '01012345678', 'password' => 'a-secure-password', 'is_admin' => true]);
        $response->assertOk()->assertJsonPath('user.email', 'new@example.com')->assertJsonStructure(['csrf_token', 'user' => ['name', 'email', 'phone']]);
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('a-secure-password', $user->password));
        $this->assertSame('01012345678', $user->phone);
        $this->post('/subscribe', ['game_id' => $game->id, 'full_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
            'receipt_image' => UploadedFile::fake()->image('receipt.png'), 'user_id' => 999, 'status' => 'approved'], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonStructure(['message']);
        $subscription = Subscription::firstOrFail();
        $this->assertSame($user->id, $subscription->user_id);
        $this->assertSame('pending', $subscription->status);
        Storage::disk('local')->assertExists($subscription->receipt_image);
    }

    public function test_existing_customer_login_returns_profile_and_new_csrf_token(): void
    {
        $user = User::factory()->create(['phone' => '01012345678', 'password' => Hash::make('my-password')]);
        $this->postJson('/checkout/login', ['email' => $user->email, 'password' => 'my-password'])
            ->assertOk()->assertJsonPath('user.name', $user->name)->assertJsonPath('user.phone', $user->phone)->assertJsonStructure(['csrf_token']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_registration_returns_inline_errors_without_creating_user(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $this->postJson('/checkout/register', ['name' => '', 'email' => 'existing@example.com', 'phone' => 'bad', 'password' => '123'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'phone', 'password']);
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_wrong_password_and_disabled_account_cannot_login(): void
    {
        $user = User::factory()->create(['password' => Hash::make('my-password'), 'is_active' => false]);
        $this->postJson('/checkout/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnprocessable();
        $this->assertGuest();
        $this->postJson('/checkout/login', ['email' => $user->email, 'password' => 'my-password'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_invalid_receipt_is_rejected_and_login_is_rate_limited(): void
    {
        $game = $this->game();
        $this->actingAs(User::factory()->create())->postJson('/subscribe', ['game_id' => $game->id, 'full_name' => 'Test', 'email' => 'test@example.com', 'phone' => '01012345678'])->assertUnprocessable()->assertJsonValidationErrors('receipt_image');
        $this->assertDatabaseCount('subscriptions', 0);
        auth()->logout();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/checkout/login', ['email' => 'blocked@example.com', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/checkout/login', ['email' => 'blocked@example.com', 'password' => 'wrong'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
    }
}
