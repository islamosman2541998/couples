<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Card;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'               => User::where('is_admin', false)->count(),
            'games'               => Game::count(),
            'subscriptions'       => Subscription::count(),
            'pending_subscriptions' => Subscription::where('status', 'pending')->count(),
            'approved_subscriptions' => Subscription::where('status', 'approved')->count(),
            'cards'               => Card::count(),
        ];

        $recentSubscriptions = Subscription::with(['user', 'game'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentSubscriptions'));
    }
}
