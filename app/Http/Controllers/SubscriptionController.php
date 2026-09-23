<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function bundle()
    {
        abort_unless(\App\Support\Membership::price() > 0 && Game::active()->exists(), 404);
        $game = \App\Support\Membership::product();
        $paidGames = $this->products();

        return view('subscribe.create', compact('game', 'paidGames'));
    }

    private function products()
    {
        $products = Game::active()->where('is_free', false)->where('price', '>', 0)->get()
            ->map(fn ($game) => (object) $game->only(['id', 'name', 'price']));
        if (\App\Support\Membership::price() > 0 && Game::active()->exists()) {
            $products->prepend(\App\Support\Membership::product());
        }

        return $products;
    }

    public function create(int $gameId)
    {
        $game = Game::active()->findOrFail($gameId);

        if ($game->is_free || $game->price <= 0) {
            return redirect()->route('subscribe.bundle');
        }

        $paidGames = $this->products();

        return view('subscribe.create', compact('game', 'paidGames'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', Rule::in($this->products()->pluck('id')->map(fn ($id) => (string) $id)->all())],
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'receipt_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'game_id.required' => 'يرجى اختيار اللعبة',
            'full_name.required' => 'يرجى إدخال الاسم الكامل',
            'phone.required' => 'يرجى إدخال رقم الجوال',
            'email.required' => 'يرجى إدخال البريد الإلكتروني',
            'receipt_image.required' => 'يرجى رفع صورة إيصال التحويل',
            'receipt_image.image' => 'يجب أن يكون الملف صورة',
            'receipt_image.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
        ]);

        $product = $this->products()->first(fn ($item) => (string) $item->id === (string) $validated['game_id']);
        $validated['is_bundle'] = $validated['game_id'] === 'all';
        $validated['amount'] = $product->price;
        $validated['game_id'] = $validated['is_bundle'] ? null : $validated['game_id'];
        $validated['receipt_image'] = $request->file('receipt_image')->store('receipts', 'local');
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        $subscription = Subscription::create($validated);
        try {
            if (! \App\Support\VisitorAnalytics::excluded($request)) {
                $visit = \App\Support\VisitorAnalytics::visit($request);
                $subscription->update(['analytics_visit_id' => $visit->id]);
                \App\Support\VisitorAnalytics::event($visit, 'receipt_uploaded', '/subscribe', (string) $subscription->id);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'تم استلام طلب الاشتراك وإيصال التحويل. سنراجع الطلب ونفعّل اللعبة بعد الموافقة.'], 201);
        }

        return redirect()->route('subscribe.success')->with('success', 'تم إرسال طلب الاشتراك بنجاح! سيتم مراجعته قريباً.');
    }

    public function success()
    {
        return view('subscribe.success');
    }
}
