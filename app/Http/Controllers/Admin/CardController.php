<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardLevel;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $levels = CardLevel::orderBy('sort_order')->get();
        $query  = Card::with('level');

        if ($request->filled('level')) {
            $query->where('card_level_id', $request->level);
        }
        if ($request->filled('target')) {
            $query->where('target', $request->target);
        }

        $cards = $query->orderBy('card_level_id')->orderBy('sort_order')->paginate(20);

        return view('admin.cards.index', compact('cards', 'levels'));
    }

    public function create()
    {
        $levels = CardLevel::orderBy('sort_order')->get();
        return view('admin.cards.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_level_id' => 'required|exists:card_levels,id',
            'content'       => 'required|string',
            'target'        => 'required|in:male,female,both',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        Card::create($validated);
        return redirect()->route('admin.cards.index')->with('success', 'تم إضافة الكارت بنجاح');
    }

    public function edit(Card $card)
    {
        $levels = CardLevel::orderBy('sort_order')->get();
        return view('admin.cards.edit', compact('card', 'levels'));
    }

    public function update(Request $request, Card $card)
    {
        $validated = $request->validate([
            'card_level_id' => 'required|exists:card_levels,id',
            'content'       => 'required|string',
            'target'        => 'required|in:male,female,both',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $card->update($validated);
        return redirect()->route('admin.cards.index')->with('success', 'تم تحديث الكارت بنجاح');
    }

    public function destroy(Card $card)
    {
        $card->delete();
        return back()->with('success', 'تم حذف الكارت بنجاح');
    }
}
