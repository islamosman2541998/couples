<?php

namespace App\Http\Middleware;

use App\Support\VisitorAnalytics as Analytics;
use Closure;
use Illuminate\Http\Request;

class TrackVisitors
{
    public function handle(Request $request, Closure $next)
    {
        $wasAuthenticated = $request->user() !== null;
        $response = $next($request);
        if (Analytics::excluded($request) || $response->getStatusCode() >= 400) {
            return $response;
        }
        $route = $request->route()?->getName();
        if (! $route && $request->isMethod('POST') && in_array($request->path(), ['login', 'register'], true)) {
            $route = $request->path();
        }
        $page = $request->isMethod('GET') && in_array($route, Analytics::PAGES, true) && $response->getStatusCode() === 200;
        $action = $request->isMethod('POST') ? match ($route) {
            'checkout.register','register' => 'register', 'checkout.login','login' => 'login', default => null
        } : null;
        if (! $page && (! $action || $wasAuthenticated || ! $request->user())) {
            return $response;
        }
        try {
            $visit = Analytics::visit($request);
            if ($page) {
                $path = Analytics::path($request);
                $visit->update(['last_path' => $path]);
                Analytics::event($visit, 'page_view', $path);
                $name = match ($route) {
                    'games.show' => 'game_preview','games.play' => 'game_play','subscribe.bundle','subscribe.create' => 'checkout_view',default => null
                };
                if ($name) {
                    Analytics::event($visit, $name, $path);
                }
            } elseif (! $wasAuthenticated && $request->user()) {
                Analytics::event($visit, $action, Analytics::path($request));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }
}
