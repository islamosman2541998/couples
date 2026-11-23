<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardLevel;
use App\Models\Game;
use App\Support\GameTrial;
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
        $level = CardLevel::where('slug', $levelSlug)->firstOrFail();

        if (GameTrial::applies($game, $request->user())) {
            abort_unless($level->slug === GameTrial::cardLevelSlug(), 403);

            $cards = Card::where('card_level_id', $level->id)->where('is_active', true)
                ->orderBy('sort_order')->orderBy('id')->limit(GameTrial::size())
                ->get(['id', 'content', 'target']);

            return response()->json($cards)->header('Cache-Control', 'private, no-store');
        }

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

        return response()->json($cards)->header('Cache-Control', 'private, no-store');
    }
}
