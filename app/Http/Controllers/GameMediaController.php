<?php

namespace App\Http\Controllers;

use App\Models\{Game, ScratchCard, ControlCard, ChallengeCard, SpinnerImage};
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
        abort_unless($user && $user->is_active, 403);
        $item = $model::findOrFail($id);
        if (! $user->is_admin) {
            abort_unless($item->is_active && Game::active()->where('type', $type)->get()
                ->contains(fn ($game) => $user->hasActiveSubscription($game->id)), 403);
        }
        abort_unless($item->image && Storage::disk('premium')->exists($item->image), 404);
        return Storage::disk('premium')->response($item->image, null, ['Cache-Control' => 'private, no-store']);
    }
}
