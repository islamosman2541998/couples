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
use App\Support\GameTrial;
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

        if (GameTrial::applies($game, auth()->user())) {
            abort_unless($this->trialScratchCards()->pluck('number')->contains($number), 403);
        }

        $card = ScratchCard::active()->where('number', $number)->firstOrFail();

        return response()
            ->json($this->scratchCardData($card))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function play(string $slug)
    {
        $game = Game::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $isTrial = GameTrial::applies($game, auth()->user());
        $trialSize = GameTrial::size();
        $limit = fn ($query) => $isTrial ? $query->orderBy('id')->limit($trialSize) : $query;
        $trialMeta = fn (int $shown, ?int $total = null) => $isTrial ? GameTrial::meta($game, $shown, $total) : null;

        if ($game->type === 'card') {
            $levels = CardLevel::withCount(['cards' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get();

            $trialLevel = $isTrial ? GameTrial::cardLevelSlug() : null;
            $trialLevelTotal = $trialLevel ? (int) $levels->firstWhere('slug', $trialLevel)?->cards_count : 0;
            $trial = $trialMeta(min($trialSize, $trialLevelTotal), $levels->sum('cards_count'));

            return response()->view('games.card-game', compact('game', 'levels', 'trial', 'trialLevel'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'control') {
            $total = ControlCard::active()->count();
            $cards = ControlCard::active()->orderBy('sort_order')->orderBy('id')->when($isTrial, fn ($q) => $q->limit($trialSize))->get()
                ->map(fn ($card) => [
                    'id' => $card->id,
                    'title' => $card->title,
                    'description' => $card->description,
                    'image' => $card->image ? $card->image_url : null,
                ])->values()->toArray();

            $trial = $trialMeta(count($cards), $total);

            return response()->view('games.control-game', compact('game', 'cards', 'trial'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'snakes') {
            $stored = SnakeCell::whereBetween('number', [1, 100])->get()->keyBy('number');
            $trialDice = $isTrial ? GameTrial::snakesDice() : null;
            $visible = $isTrial ? GameTrial::snakesDestinations($trialDice, config('snakes.links')) : null;
            $cells = collect(range(1, 100))->map(function ($number) use ($stored, $visible) {
                if ($visible !== null && ! in_array($number, $visible, true)) {
                    return ['number' => $number, 'title' => 'للمشتركين', 'content' => 'الخانة دي بتتكشف مع الاشتراك.', 'mood' => 'warm', 'active' => false];
                }
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

            $trial = $trialMeta($isTrial ? count($trialDice) : 0);

            return response()->view('games.snakes-game', compact('game', 'cells', 'boardLinks', 'trial', 'trialDice'))->header('Cache-Control', 'private, no-store');
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

            $trialSequence = $isTrial ? $images->sortBy([['sort_order', 'asc'], ['id', 'asc']])->take($trialSize)->pluck('id')->values()->all() : null;
            $trial = $trialMeta($isTrial ? count($trialSequence) : 0, $images->count());

            return response()->view('games.spinner-game', compact('game', 'images', 'spinnerData', 'trial', 'trialSequence'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'scratch') {
            $total = ScratchCard::active()->count();
            $cards = ($isTrial ? $this->trialScratchCards() : ScratchCard::active()->orderBy('sort_order')->orderBy('number')->get())
                ->map(fn ($card) => $this->scratchCardData($card))
                ->values()
                ->toArray();
            $trial = $trialMeta(count($cards), $total);

            return response()->view('games.scratch-game', compact('game', 'cards', 'trial'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'who') {
            $total = WhoQuestion::active()->count();
            $questions = $limit(WhoQuestion::active()->orderBy('sort_order'))
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

            $trial = $trialMeta(count($questions), $total);

            return response()->view('games.who-game', compact('game', 'questions', 'trial'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'challenge') {
            $total = ChallengeCard::active()->count();
            $cards = $limit(ChallengeCard::active()->orderBy('sort_order'))
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

            $trial = $trialMeta(count($cards), $total);

            return response()->view('games.challenge-game', compact('game', 'cards', 'trial'))->header('Cache-Control', 'private, no-store');
        }

        if ($game->type === 'know_me') {
            $total = KnowMeQuestion::active()->count();
            $questions = $limit(KnowMeQuestion::active()->orderBy('sort_order'))
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

            $trial = $trialMeta(count($questions), $total);

            return response()->view('games.know-me-game', compact('game', 'questions', 'trial'))->header('Cache-Control', 'private, no-store');
        }

        abort(404);
    }

    private function trialScratchCards()
    {
        return ScratchCard::active()->orderBy('sort_order')->orderBy('number')->limit(GameTrial::size())->get();
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
