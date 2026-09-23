<?php

namespace App\Support;

use App\Models\AnalyticsVisit;
use App\Models\AnalyticsVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class VisitorAnalytics
{
    public const PAGES = ['home', 'about', 'privacy', 'contact', 'games.show', 'games.play', 'subscribe.bundle', 'subscribe.create', 'subscribe.success', 'login', 'register', 'profile.index'];

    public static function excluded(Request $r): bool
    {
        return $r->user()?->is_admin || $r->cookie('analytics_optout') === '1'
            || $r->header('DNT') === '1' || $r->header('Sec-GPC') === '1'
            || preg_match('/bot|crawler|spider|headless|preview|facebookexternalhit/i', $r->userAgent() ?? '');
    }

    public static function current(Request $r): ?AnalyticsVisit
    {
        $id = $r->cookie('analytics_visitor');
        $visitor = Str::isUuid($id ?? '') ? AnalyticsVisitor::find($id) : null;
        if (! $visitor || ($r->user() && $visitor->user_id && $visitor->user_id !== $r->user()->id)) {
            return null;
        }

        return $visitor->visits()->where('last_seen_at', '>=', now()->subMinutes(30))->latest('last_seen_at')->first();
    }

    public static function visit(Request $r): AnalyticsVisit
    {
        $id = $r->cookie('analytics_visitor');
        $visitor = Str::isUuid($id ?? '') ? AnalyticsVisitor::find($id) : null;
        if (! $visitor || ($r->user() && $visitor->user_id && $visitor->user_id !== $r->user()->id)) {
            $visitor = AnalyticsVisitor::create(['id' => (string) Str::uuid(), 'user_id' => $r->user()?->id]);
        }
        Cookie::queue(cookie('analytics_visitor', $visitor->id, 60 * 24 * 180, '/', null, $r->isSecure(), true, false, 'lax'));
        if ($r->user()) {
            $visitor->update(['user_id' => $r->user()->id]);
        }
        $visit = $visitor->visits()->where('last_seen_at', '>=', now()->subMinutes(30))->latest('last_seen_at')->first();
        if (! $visit) {
            $ua = $r->userAgent() ?? '';
            $ref = strtolower((string) parse_url($r->header('referer', ''), PHP_URL_HOST));
            $ref = $ref !== $r->getHost() ? mb_substr($ref, 0, 150) : '';
            $clean = fn ($key, $max) => is_string($r->query($key)) ? mb_substr(preg_replace('/[^\pL\pN _.,+\/-]/u', '', $r->query($key)), 0, $max) : null;
            $ip = $r->ip();
            $network = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? preg_replace('/\.\d+$/', '.0/24', $ip) : null;
            if (! $network && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $network = inet_ntop(substr(inet_pton($ip), 0, 6).str_repeat("\0", 10)).'/48';
            }
            $visit = $visitor->visits()->create([
                'id' => (string) Str::uuid(), 'user_id' => $r->user()?->id, 'source' => $clean('utm_source', 150) ?: ($ref ?: 'direct'),
                'referrer_host' => $ref ?: null, 'campaign' => $clean('utm_campaign', 150), 'medium' => $clean('utm_medium', 100),
                'landing_path' => self::path($r), 'last_path' => self::path($r), 'last_seen_at' => now(),
                'device' => preg_match('/ipad|tablet/i', $ua) ? 'Tablet' : (preg_match('/mobile|android|iphone/i', $ua) ? 'Mobile' : 'Desktop'),
                'browser' => match (true) {
                    str_contains($ua, 'Edg/') => 'Edge', str_contains($ua, 'OPR/') => 'Opera', str_contains($ua, 'Firefox/') => 'Firefox', str_contains($ua, 'Chrome/') => 'Chrome', str_contains($ua, 'Safari/') => 'Safari', default => 'Unknown'
                },
                'os' => match (true) {
                    str_contains($ua, 'Android') => 'Android', preg_match('/iPhone|iPad/', $ua) === 1 => 'iOS', str_contains($ua, 'Windows') => 'Windows', str_contains($ua, 'Mac OS') => 'macOS', str_contains($ua, 'Linux') => 'Linux', default => 'Unknown'
                },
                'ip_network' => $network, 'language' => mb_substr($r->getPreferredLanguage() ?? '', 0, 35),
            ]);
        }
        $visit->update(['last_seen_at' => now(), 'user_id' => $r->user()?->id ?? $visit->user_id]);

        return $visit;
    }

    public static function path(Request $r): string
    {
        return mb_substr('/'.ltrim($r->path(), '/'), 0, 255);
    }

    public static function event(AnalyticsVisit $visit, string $name, string $path, ?string $detail = null): void
    {
        $visit->events()->create(['name' => $name, 'path' => $path, 'detail' => $detail, 'created_at' => now()]);
    }
}
