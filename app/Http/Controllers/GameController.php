<?php

namespace App\Http\Controllers;

use App\Models\CardLevel;
use App\Models\ChallengeCard;
use App\Models\ControlCard;
use App\Models\Game;
use App\Models\KnowMeQuestion;
use App\Models\ScratchCard;
use App\Models\SnakeCell;
use App\Models\SpinnerImage;
use App\Models\WhoQuestion;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function show(string $slug)
    {
        $game = Game::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->view('games.show', compact('game'))->header('Cache-Control', 'private, no-store');
    }

    public function scratchCard(string $slug, int $number): JsonResponse
    {
        $game = Game::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless($game->type === 'scratch', 404);

        abort_unless(auth()->user()?->hasActiveSubscription($game->id) ?? false, 403);

        $card = ScratchCard::active()->where('number', $number)->firstOrFail();

        return response()
            ->json($this->scratchCardData($card))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function play(string $slug)
    {
        $game = Game::where('slug', $slug)->where('is_active', true)->firstOrFail();

        if (! (auth()->user()?->hasActiveSubscription($game->id) ?? false)) {
            return redirect()->route('subscribe.create', $game->id)
                ->with('warning', 'هذه اللعبة تتطلب اشتراكاً مدفوعاً');
        }

        if ($game->type === 'card') {
            $levels = CardLevel::withCount(['cards' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get();

            return response()->view('games.card-game', compact('game', 'levels'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'control') {
            $cards = ControlCard::active()->orderBy('sort_order')->orderBy('id')->get()
                ->map(fn ($card) => [
                    'id' => $card->id,
                    'title' => $card->title,
                    'description' => $card->description,
                    'image' => $card->image ? $card->image_url : null,
                ])->values()->toArray();

            return response()->view('games.control-game', compact('game', 'cards'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'snakes') {
            $stored = SnakeCell::whereBetween('number', [1, 100])->get()->keyBy('number');
            $cells = collect(range(1, 100))->map(function ($number) use ($stored) {
                $cell = $stored->get($number);
                $active = $cell && $cell->is_active;

                return [
                    'number' => $number,
                    'title' => $active ? $cell->title : 'استراحة',
                    'content' => $active ? $cell->content : 'خدوا لحظة هادية مع بعض، وبعدها كمّلوا اللعب.',
                    'mood' => $active ? $cell->mood : 'warm',
                    'active' => (bool) $active,
                ];
            })->all();
            $boardLinks = config('snakes.links');

            return response()->view('games.snakes-game', compact('game', 'cells', 'boardLinks'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'spinner') {
            $images = SpinnerImage::active()->orderBy('sort_order')->get();
            $spinnerData = $images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'name' => $img->name,
                    'color' => $img->color,
                    'image' => $img->image !== 'spinner/placeholder.png' ? $img->image_url : null,
                ];
            })->values()->toArray();

            return response()->view('games.spinner-game', compact('game', 'images', 'spinnerData'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'scratch') {
            $cards = ScratchCard::active()
                ->orderBy('sort_order')
                ->orderBy('number')
                ->get()
                ->map(fn ($card) => $this->scratchCardData($card))
                ->values()
                ->toArray();

            return response()->view('games.scratch-game', compact('game', 'cards'))->header('Cache-Control', 'private, no-store');
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

            return response()->view('games.who-game', compact('game', 'questions'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'challenge') {
            $cards = ChallengeCard::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'description' => $c->description,
                    'image' => $c->image ? $c->image_url : null,
                    'timer' => $c->timer,
                    'category' => $c->category,
                    'category_emoji' => $c->category_emoji,
                    'category_label' => $c->category_label,
                    'category_color' => $c->category_color,
                ])
                ->values()
                ->toArray();

            return response()->view('games.challenge-game', compact('game', 'cards'))->header('Cache-Control', 'private, no-store');
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

            return response()->view('games.know-me-game', compact('game', 'questions'))->header('Cache-Control', 'private, no-store');
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
                ? $card->image_url.'?v='.$card->updated_at->timestamp
                : null,
        ];
    }
}
