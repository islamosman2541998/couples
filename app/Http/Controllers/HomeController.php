<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $freeGames  = Game::active()->where('is_free', true)->orderBy('sort_order')->get();
        $paidGames  = Game::active()->where('is_free', false)->orderBy('sort_order')->get();
        $homeSettings = \App\Support\HomeContent::settings();
        $slides = \App\Support\HomeContent::visible('slides');
        $reviews = \App\Support\HomeContent::visible('reviews');
        $notificationGames = $freeGames->concat($paidGames)->when($homeSettings['game_ids'], fn ($games) => $games->whereIn('id', $homeSettings['game_ids']))
            ->map(fn ($game) => ['name' => $game->name, 'url' => route('games.show', $game->slug), 'image' => $game->image ? $game->image_url : null])->values();
        return view('home', compact('freeGames', 'paidGames', 'homeSettings', 'slides', 'reviews', 'notificationGames'));
    }

    public function about()
    {
        $text = Setting::get('about_text');
        return view('pages.about', compact('text'));
    }

    public function privacy()
    {
        $text = Setting::get('privacy_text');
        return view('pages.privacy', compact('text'));
    }

    public function contact()
    {
        return view('pages.contact');
    }
}
