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
        return view('home', compact('freeGames', 'paidGames'));
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
