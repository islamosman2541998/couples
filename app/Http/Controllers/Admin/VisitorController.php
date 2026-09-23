<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisit;
use App\Models\AnalyticsVisitor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $request->merge(['from' => $request->input('from') ?: now()->subDays(29)->toDateString(), 'to' => $request->input('to') ?: now()->toDateString()]);
        $data = $request->validate([
            'from' => 'required|date_format:Y-m-d', 'to' => 'required|date_format:Y-m-d|after_or_equal:from',
            'source' => 'nullable|string|max:150', 'device' => 'nullable|in:Mobile,Desktop,Tablet', 'q' => 'nullable|string|max:100',
        ]);
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $visits = AnalyticsVisit::whereBetween('created_at', [$from, $to])
            ->when($request->filled('source'), fn ($q) => $q->where('source', $data['source']))
            ->when($request->filled('device'), fn ($q) => $q->where('device', $data['device']))
            ->when($request->filled('q'), function ($q) use ($data) {
                $term = '%'.$data['q'].'%';
                $q->where(fn ($q) => $q->where('visitor_id', 'like', $term)->orWhereHas('visitor.user', fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)));
            });
        $ids = (clone $visits)->select('id');
        $events = AnalyticsEvent::whereIn('visit_id', $ids);
        $unique = (clone $visits)->distinct()->count('visitor_id');
        $new = (clone $visits)->whereHas('visitor', fn ($q) => $q->whereBetween('created_at', [$from, $to]))->distinct()->count('visitor_id');
        $stats = [
            'visitors' => $unique, 'visits' => (clone $visits)->count(), 'new' => $new, 'returning' => $unique - $new,
            'online' => (clone $visits)->where('last_seen_at', '>=', now()->subMinutes(5))->distinct()->count('visitor_id'),
            'views' => (clone $events)->where('name', 'page_view')->count(),
            'subscribe_clicks' => (clone $events)->where('name', 'subscribe_click')->count(),
            'whatsapp_clicks' => (clone $events)->where('name', 'whatsapp_click')->count(),
            'plays' => (clone $events)->where('name', 'game_play')->count(),
            'duration' => (int) round((clone $visits)->avg('active_seconds') ?? 0),
        ];
        $funnel = [];
        foreach (['game_preview', 'checkout_view', 'register', 'receipt_uploaded', 'subscription_approved'] as $name) {
            $funnel[$name] = AnalyticsVisit::whereIn('id', $ids)->whereHas('events', fn ($q) => $q->where('name', $name))->distinct()->count('visitor_id');
        }
        $sources = (clone $visits)->selectRaw('source, COUNT(*) as total')->groupBy('source')->orderByDesc('total')->limit(10)->get();
        $devices = (clone $visits)->selectRaw('device, COUNT(*) as total')->groupBy('device')->orderByDesc('total')->get();
        $pages = (clone $events)->where('name', 'page_view')->selectRaw('path, COUNT(*) as total')->groupBy('path')->orderByDesc('total')->limit(10)->get();
        $days = (clone $visits)->selectRaw('DATE(created_at) as day, COUNT(*) as total')->groupBy('day')->orderByDesc('day')->limit(31)->get()->reverse();
        $visitors = AnalyticsVisitor::with('user')->whereHas('visits', fn ($q) => $q->whereIn('id', $ids))
            ->withCount(['visits as filtered_visits_count' => fn ($q) => $q->whereIn('id', $ids)])
            ->withMax(['visits as last_seen' => fn ($q) => $q->whereIn('id', $ids)], 'last_seen_at')
            ->orderByDesc('last_seen')->paginate(25)->withQueryString();
        $latest = AnalyticsVisit::whereIn('id', $ids)->whereIn('visitor_id', $visitors->pluck('id'))->latest('last_seen_at')->get()->unique('visitor_id')->keyBy('visitor_id');

        return response()->view('admin.visitors.index', compact('stats', 'funnel', 'sources', 'devices', 'pages', 'days', 'visitors', 'latest'))->header('Cache-Control', 'private, no-store');
    }

    public function show(AnalyticsVisitor $visitor)
    {
        $visitor->load('user');
        $visits = $visitor->visits()->with('user')->withCount('events')->latest()->paginate(10, ['*'], 'visits_page');
        $events = AnalyticsEvent::whereIn('visit_id', $visitor->visits()->select('id'))->latest('id')->paginate(50, ['*'], 'events_page');

        return response()->view('admin.visitors.show', compact('visitor', 'visits', 'events'))->header('Cache-Control', 'private, no-store');
    }
}
