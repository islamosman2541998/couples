<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionStatusChanged;
use App\Support\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function pendingSubscription(): Subscription
    {
        $game = Game::create(['name' => 'Paid game', 'slug' => 'paid', 'type' => 'who', 'price' => 100, 'is_free' => false, 'is_active' => true]);
        $user = User::factory()->create();

        return Subscription::create(['user_id' => $user->id, 'game_id' => $game->id, 'full_name' => 'Customer', 'email' => 'buyer@example.com', 'phone' => '01012345678', 'receipt_image' => 'receipts/a.png', 'status' => 'pending']);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_approving_emails_the_customer(): void
    {
        Notification::fake();
        $subscription = $this->pendingSubscription();

        $this->actingAs($this->admin())->patch('/admin/subscriptions/'.$subscription->id.'/approve')->assertRedirect();

        Notification::assertSentOnDemand(SubscriptionStatusChanged::class, function ($notification, $channels, AnonymousNotifiable $notifiable) {
            $mail = $notification->toMail($notifiable);

            return $notifiable->routes['mail'] === 'buyer@example.com'
                && $notification->subscription->status === 'approved'
                && str_contains($mail->actionUrl, '/games/paid/play');
        });
    }

    public function test_rejecting_emails_the_customer_with_the_admin_note(): void
    {
        Notification::fake();
        $subscription = $this->pendingSubscription();

        $this->actingAs($this->admin())->patch('/admin/subscriptions/'.$subscription->id.'/reject', ['admin_notes' => 'المبلغ ناقص'])->assertRedirect();

        Notification::assertSentOnDemand(SubscriptionStatusChanged::class, function ($notification, $channels, AnonymousNotifiable $notifiable) {
            $lines = implode(' ', $notification->toMail($notifiable)->introLines);

            return $notification->subscription->status === 'rejected' && str_contains($lines, 'المبلغ ناقص');
        });
    }

    public function test_a_mail_failure_does_not_block_the_approval(): void
    {
        Notification::shouldReceive('route')->andThrow(new \RuntimeException('SMTP down'));
        $subscription = $this->pendingSubscription();

        $this->actingAs($this->admin())->patch('/admin/subscriptions/'.$subscription->id.'/approve')->assertRedirect()->assertSessionHas('success');

        $this->assertSame('approved', $subscription->fresh()->status);
    }

    public function test_whatsapp_link_uses_the_egyptian_international_format(): void
    {
        $this->assertSame('https://wa.me/201012345678', Phone::whatsapp('01012345678'));
        $this->assertSame('https://wa.me/201012345678', Phone::whatsapp('+20 101 234 5678'));
        $this->assertSame('https://wa.me/201012345678', Phone::whatsapp('00201012345678'));
        $this->assertSame('https://wa.me/201012345678', Phone::whatsapp('٠١٠١٢٣٤٥٦٧٨'));
        $this->assertNull(Phone::whatsapp('12'));
    }

    public function test_site_whatsapp_buttons_use_the_international_number(): void
    {
        \App\Models\Setting::set('contact_whatsapp', '01066490296');

        $this->get('/')->assertOk()->assertSee('https://wa.me/201066490296', false)->assertDontSee('https://wa.me/01066490296', false);
        $this->get('/contact')->assertOk()->assertDontSee('https://wa.me/01066490296', false);
    }

    public function test_admin_request_page_has_a_prefilled_whatsapp_button(): void
    {
        $subscription = $this->pendingSubscription();

        $this->actingAs($this->admin())->get('/admin/subscriptions/'.$subscription->id)->assertOk()
            ->assertSee('https://wa.me/201012345678?text=', false);
    }
}
