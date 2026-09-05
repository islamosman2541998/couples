<?php

namespace App\Http\Controllers;

use App\Models\CardLevel;
use App\Models\ChallengeCard;
use App\Models\Game;
use App\Models\KnowMeQuestion;
use App\Models\ScratchCard;
use App\Models\SpinnerImage;
use App\Models\WhoQuestion;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function show(string $slug)
    {
        $game = Game::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('games.show', compact('game'));
    }

    public function scratchCard(string $slug, int $number): JsonResponse
    {
        $game = Game::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless($game->type === 'scratch', 404);

        if (! $game->is_free) {
            abort_unless(auth()->check() && auth()->user()->hasActiveSubscription($game->id), 403);
        }

        $card = ScratchCard::active()->where('number', $number)->firstOrFail();

        return response()
            ->json($this->scratchCardData($card))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function play(string $slug)
    {
        $game = Game::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Check if paid game requires subscription
        if (! $game->is_free) {
            if (! auth()->check() || ! auth()->user()->hasActiveSubscription($game->id)) {
                return redirect()->route('subscribe.create', $game->id)
                    ->with('warning', 'هذه اللعبة تتطلب اشتراكاً مدفوعاً');
            }
        }

        if ($game->type === 'card') {
            $levels = CardLevel::withCount(['cards' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get();

            return view('games.card-game', compact('game', 'levels'));
        }

        if ($game->type === 'spinner') {
            $images = SpinnerImage::active()->orderBy('sort_order')->get();
            $spinnerData = $images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'name' => $img->name,
                    'color' => $img->color,
                    'image' => $img->image !== 'spinner/placeholder.png' ? asset('storage/'.$img->image) : null,
                ];
            })->values()->toArray();

            return view('games.spinner-game', compact('game', 'images', 'spinnerData'));
        }

        if ($game->type === 'scratch') {
            $cards = ScratchCard::active()
                ->orderBy('sort_order')
                ->orderBy('number')
                ->get()
                ->map(fn ($card) => $this->scratchCardData($card))
                ->values()
                ->toArray();

            return view('games.scratch-game', compact('game', 'cards'));
        }

        if ($game->type === 'who') {
            $questions = WhoQuestion::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($q) => [
                    'id' => $q->id,
                    'question' => $q->question,
                    'category_emoji' => $q->category_emoji,
                    'category_label' => $q->category_label,
                    'category_color' => $q->category_color,
                    'challenge' => $q->challenge,
                ])
                ->values()
                ->toArray();

            return view('games.who-game', compact('game', 'questions'));
        }

        if ($game->type === 'challenge') {
            $cards = ChallengeCard::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'description' => $c->description,
                    'image' => $c->image ? asset('storage/'.$c->image) : null,
                    'timer' => $c->timer,
                    'category' => $c->category,
                    'category_emoji' => $c->category_emoji,
                    'category_label' => $c->category_label,
                    'category_color' => $c->category_color,
                ])
                ->values()
                ->toArray();

            return view('games.challenge-game', compact('game', 'cards'));
        }

        if ($game->type === 'know_me') {
            $questions = KnowMeQuestion::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($q) => [
                    'id' => $q->id,
                    'question' => $q->question,
                    'category' => $q->category,
                    'category_emoji' => $q->category_emoji,
                    'category_label' => $q->category_label,
                    'category_color' => $q->category_color,
                    'answer_type' => $q->answer_type,
                    'choices' => $q->choices ?? [],
                    'hint' => $q->hint,
                ])
                ->values()
                ->toArray();

            return view('games.know-me-game', compact('game', 'questions'));
        }

        abort(404);
    }

    private function scratchCardData(ScratchCard $card): array
    {
        return [
            'number' => $card->number,
            'level' => $card->level,
            'content' => $card->content,
            'image' => $card->image
                ? asset('storage/'.$card->image).'?v='.$card->updated_at->timestamp
                : null,
        ];
    }
}
