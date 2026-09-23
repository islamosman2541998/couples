<?php

namespace App\Http\Controllers;

use App\Support\VisitorAnalytics as Analytics;
use Illuminate\Http\Request;

class AnalyticsEventController extends Controller
{
    public function preference(Request $r)
    {
        $r->validate(['disabled' => 'required|boolean']);

        return redirect()->route('privacy')->withCookie(cookie('analytics_optout', $r->boolean('disabled') ? '1' : '0', 60 * 24 * 180, '/', null, $r->isSecure(), true, false, 'lax'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|in:heartbeat,subscribe_click,whatsapp_click,mood_select',
            'path' => ['required', 'string', 'max:255', 'regex:~^/[^?\x00-\x20\x23]*$~'],
            'detail' => 'nullable|in:الكل,ضحك,تعارف,تحدي,مفاجأة',
            'timezone' => 'nullable|timezone', 'screen' => ['nullable', 'regex:/^\d{2,5}x\d{2,5}$/'],
        ]);
        if (Analytics::excluded($r)) {
            return response()->noContent();
        }
        $visit = Analytics::current($r);
        if (! $visit || ! $visit->events()->where('name', 'page_view')->where('path', $data['path'])->exists()) {
            return response()->noContent();
        }
        $updates = ['last_seen_at' => now()];
        if ($data['name'] === 'heartbeat') {
            // Bounded by server elapsed time, including concurrent tabs; never trust a client duration.
            $seconds = min(30, max(0, (int) $visit->last_seen_at->diffInSeconds(now())));
            $updated = $visit->whereKey($visit->id)->where('last_seen_at', $visit->last_seen_at)
                ->update(['last_seen_at' => now(), 'active_seconds' => $visit->active_seconds + $seconds]);
            if (! $updated) {
                return response()->noContent();
            }
            if (! empty($data['timezone'])) {
                $updates['timezone'] = $data['timezone'];
            }
            if (! empty($data['screen'])) {
                $updates['screen'] = $data['screen'];
            }
        } else {
            Analytics::event($visit, $data['name'], $data['path'], $data['name'] === 'mood_select' ? ($data['detail'] ?? null) : null);
        }
        $visit->update($updates);

        return response()->noContent();
    }
}
