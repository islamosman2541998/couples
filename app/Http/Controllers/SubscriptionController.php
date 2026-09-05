<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function create(int $gameId)
    {
        $game = Game::active()->findOrFail($gameId);

        if ($game->is_free) {
            return redirect()->route('games.play', $game->slug)->with('info', 'هذه اللعبة مجانية!');
        }

        $paidGames = Game::active()->where('is_free', false)->get();
        return view('subscribe.create', compact('game', 'paidGames'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id'       => ['required', Rule::exists('games', 'id')->where('is_active', 1)->where('is_free', 0)],
            'full_name'     => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'email'         => 'required|email|max:255',
            'receipt_image' => 'required|image|max:5120',
        ], [
            'game_id.required'       => 'يرجى اختيار اللعبة',
            'full_name.required'     => 'يرجى إدخال الاسم الكامل',
            'phone.required'         => 'يرجى إدخال رقم الجوال',
            'email.required'         => 'يرجى إدخال البريد الإلكتروني',
            'receipt_image.required' => 'يرجى رفع صورة إيصال التحويل',
            'receipt_image.image'    => 'يجب أن يكون الملف صورة',
            'receipt_image.max'      => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
        ]);

        $validated['receipt_image'] = $request->file('receipt_image')->store('receipts', 'local');
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        Subscription::create($validated);

        return redirect()->route('subscribe.success')->with('success', 'تم إرسال طلب الاشتراك بنجاح! سيتم مراجعته قريباً.');
    }

    public function success()
    {
        return view('subscribe.success');
    }
}
