<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisitor;
use App\Models\Game;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisitorAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function startVisit(): AnalyticsVisitor
    {
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Linux; Android 14) Chrome/120 Mobile', 'Referer' => 'https://facebook.com/?private=secret'])
            ->get('/?utm_source=facebook&utm_campaign=summer&utm_medium=paid&email=secret@example.com')->assertOk();
        $visitor = AnalyticsVisitor::firstOrFail();
        $this->withCredentials()->withCookie('analytics_visitor', $visitor->id);

        return $visitor;
    }

    public function test_visits_sources_session_expiry_and_no_sensitive_query_storage(): void
    {
        $visitor = $this->startVisit();
        $visit = $visitor->visits()->firstOrFail();
        $this->assertSame('facebook', $visit->source);
        $this->assertSame('summer', $visit->campaign);
        $this->assertSame('facebook.com', $visit->referrer_host);
        $this->assertSame('/', $visit->landing_path);
        $this->assertSame('Mobile', $visit->device);
        $this->assertStringNotContainsString('secret', $visit->toJson());
        $this->get('/about')->assertOk();
        $this->assertDatabaseCount('analytics_visitors', 1);
        $this->assertDatabaseCount('analytics_visits', 1);
        $this->assertDatabaseCount('analytics_events', 2);
        $this->travel(31)->minutes();
        $this->get('/contact')->assertOk();
        $this->assertDatabaseCount('analytics_visits', 2);
        $this->assertDatabaseCount('analytics_visitors', 1);
    }

    public function test_registration_payment_and_approval_are_linked_and_not_client_forgeable(): void
    {
        Storage::fake('local');
        $visitor = $this->startVisit();
        Setting::set('membership_price', '700');
        Game::create(['name' => 'Cards', 'slug' => 'cards', 'type' => 'card', 'is_free' => true]);
        $this->get('/membership')->assertOk();
        $this->postJson('/checkout/register', ['name' => 'Tracked customer', 'email' => 'customer@example.com', 'phone' => '01012345678', 'password' => 'a-secret-password'])->assertOk();
        $user = User::where('email', 'customer@example.com')->firstOrFail();
        $this->assertSame($user->id, $visitor->fresh()->user_id);
        $this->assertDatabaseHas('analytics_events', ['name' => 'register']);
        $this->postJson('/subscribe', ['game_id' => 'all', 'full_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone, 'receipt_image' => UploadedFile::fake()->image('receipt.png')])->assertCreated();
        $subscription = Subscription::firstOrFail();
        $this->assertNotNull($subscription->analytics_visit_id);
        $this->assertDatabaseHas('analytics_events', ['name' => 'receipt_uploaded', 'visit_id' => $subscription->analytics_visit_id]);
        $this->postJson('/analytics/events', ['name' => 'subscription_approved', 'path' => '/membership'])->assertUnprocessable();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->patch('/admin/subscriptions/'.$subscription->id.'/approve')->assertRedirect();
        $this->patch('/admin/subscriptions/'.$subscription->id.'/approve')->assertRedirect();
        $this->assertSame(1, AnalyticsEvent::where('name', 'subscription_approved')->count());
        $this->assertStringNotContainsString('a-secret-password', AnalyticsEvent::all()->toJson());
    }

    public function test_admin_only_reports_filters_and_visitor_timeline(): void
    {
        $visitor = $this->startVisit();
        $this->get('/admin/visitors')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/visitors/'.$visitor->id)->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $response = $this->get('/admin/visitors?source=facebook&device=Mobile')->assertOk()->assertSee('facebook');
        $response->assertViewHas('stats', fn ($stats) => $stats['visitors'] === 1 && $stats['views'] === 1);
        $this->get('/admin/visitors?source=missing')->assertOk()->assertViewHas('stats', fn ($stats) => $stats['visitors'] === 0);
        $this->get('/admin/visitors?from=2026-09-30&to=2026-09-01')->assertSessionHasErrors('to');
        $this->get('/admin/visitors/'.$visitor->id)->assertOk()->assertSee('facebook.com')->assertSee('مشاهدة صفحة');
        $this->assertDatabaseCount('analytics_visits', 1);
    }

    public function test_browser_events_require_real_visit_and_heartbeat_is_bounded(): void
    {
        $this->postJson('/analytics/events', ['name' => 'subscribe_click', 'path' => '/'])->assertNoContent();
        $this->assertDatabaseCount('analytics_events', 0);
        $visitor = $this->startVisit();
        $this->travel(30)->seconds();
        $this->postJson('/analytics/events', ['name' => 'heartbeat', 'path' => '/', 'timezone' => 'Africa/Cairo', 'screen' => '390x844'])->assertNoContent();
        $visit = $visitor->visits()->firstOrFail();
        $this->assertSame(30, $visit->active_seconds);
        $this->assertSame('Africa/Cairo', $visit->timezone);
        $this->postJson('/analytics/events', ['name' => 'heartbeat', 'path' => '/'])->assertNoContent();
        $this->assertSame(30, $visit->fresh()->active_seconds);
        $this->postJson('/analytics/events', ['name' => 'mood_select', 'path' => '/', 'detail' => 'ضحك'])->assertNoContent();
        $this->assertDatabaseHas('analytics_events', ['name' => 'mood_select', 'detail' => 'ضحك']);
        $this->postJson('/analytics/events', ['name' => 'subscribe_click', 'path' => '/admin'])->assertNoContent();
        $this->assertDatabaseMissing('analytics_events', ['path' => '/admin']);
        $this->postJson('/analytics/events', ['name' => 'subscribe_click', 'path' => '/?password=secret'])->assertUnprocessable();
    }

    public function test_tracking_excludes_admins_bots_optout_and_private_routes(): void
    {
        $this->withHeader('DNT', '1')->get('/')->assertOk();
        $this->withHeader('DNT', '0')->withHeader('User-Agent', 'Googlebot')->get('/')->assertOk();
        $this->withHeader('User-Agent', 'Mozilla/5.0')->withCookie('analytics_optout', '1')->get('/')->assertOk();
        $this->withCookie('analytics_optout', '0')->get('/forgot-password')->assertOk();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/')->assertOk();
        $this->assertDatabaseCount('analytics_visitors', 0);
    }
}
