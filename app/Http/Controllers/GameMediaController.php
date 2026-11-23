<?php

namespace App\Http\Controllers;

use App\Models\{Game, ScratchCard, ControlCard, ChallengeCard, SpinnerImage};
use App\Support\GameTrial;
use Illuminate\Support\Facades\Storage;

class GameMediaController extends Controller
{
    public function show(string $type, int $id)
    {
        $model = match ($type) {
            'scratch' => ScratchCard::class,
            'control' => ControlCard::class,
            'challenge' => ChallengeCard::class,
            'spinner' => SpinnerImage::class,
            default => abort(404),
        };
        $user = auth()->user();
        abort_if($user && ! $user->is_active, 403);
        $item = $model::findOrFail($id);
        if (! $user?->is_admin) {
            $games = Game::active()->where('type', $type)->get();
            abort_unless($item->is_active && $games->isNotEmpty(), 403);
            $subscribed = $user && $games->contains(fn ($game) => $user->hasActiveSubscription($game->id));
            // Free-trial visitors may load images of the trial slice only, and only
            // while a game of this type still offers the trial.
            $trialOpen = $games->contains(fn ($game) => $game->trial_enabled);
            abort_unless($subscribed || ($trialOpen && in_array($item->id, GameTrial::mediaIds($type), true)), 403);
        }
        abort_unless($item->image && Storage::disk('premium')->exists($item->image), 404);
        return Storage::disk('premium')->response($item->image, null, ['Cache-Control' => 'private, no-store']);
    }
}
