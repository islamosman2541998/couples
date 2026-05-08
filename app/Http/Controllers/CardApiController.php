<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardLevel;
use Illuminate\Http\Request;

class CardApiController extends Controller
{
    public function getCards(Request $request, string $levelSlug)
    {
        $level = CardLevel::where('slug', $levelSlug)->firstOrFail();

        $target = $request->query('target', 'both');

        $cards = Card::where('card_level_id', $level->id)
            ->where('is_active', true)
            ->when($target !== 'all', function ($q) use ($target) {
                $q->where(function ($q2) use ($target) {
                    $q2->where('target', 'both')->orWhere('target', $target);
                });
            })
            ->inRandomOrder()
            ->get(['id', 'content', 'target']);

        return response()->json($cards);
    }
}
