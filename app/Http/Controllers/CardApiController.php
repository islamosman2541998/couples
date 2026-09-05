<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardLevel;
use App\Models\Game;
use Illuminate\Http\Request;

class CardApiController extends Controller
{
    public function getCards(Request $request, string $levelSlug)
    {
        $request->validate([
            'game_id' => ['required', 'integer'],
            'target' => ['sometimes', 'in:all,both,male,female'],
        ]);
        $game = Game::active()->where('type', 'card')->findOrFail($request->integer('game_id'));
        abort_unless($game->is_free || ($request->user()?->hasActiveSubscription($game->id) ?? false), 403);

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
