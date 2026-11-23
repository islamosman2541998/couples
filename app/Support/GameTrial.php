<?php

namespace App\Support;

use App\Models\Game;
use App\Models\Setting;
use App\Models\User;

/**
 * Free trial for visitors without an active subscription: a fixed slice of
 * each game's content, always in the same order so a reload shows the same items.
 */
class GameTrial
{
    public const DEFAULT_SIZE = 3;

    /** Fixed snakes dice; the first {@see size()} values are used. */
    public const SNAKES_DICE = [3, 6, 5, 2, 4, 1, 5, 3, 6, 2, 4, 1, 3, 5, 2, 6, 4, 1, 2, 5];

    public static function size(): int
    {
        $value = Setting::get('trial_items', (string) self::DEFAULT_SIZE);

        if (! is_numeric($value)) {
            return self::DEFAULT_SIZE;
        }

        return max(1, min(20, (int) $value));
    }

    public static function applies(Game $game, ?User $user): bool
    {
        return ! ($user?->hasActiveSubscription($game->id) ?? false);
    }

    /** The game is subscription-only and this visitor has no subscription. */
    public static function locked(Game $game, ?User $user): bool
    {
        return ! $game->trial_enabled && self::applies($game, $user);
    }

    public static function meta(Game $game, int $shown, ?int $total = null): array
    {
        return [
            'size' => self::size(),
            'shown' => $shown,
            'total' => $total,
            'subscribe_url' => route('subscribe.create', $game->id),
            'bundle_url' => route('subscribe.bundle'),
            'price' => Money::egp($game->price),
        ];
    }

    /** The only card level open in trial: the first level (by sort order) with active cards. */
    public static function cardLevelSlug(): ?string
    {
        return \App\Models\CardLevel::whereHas('cards', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')->orderBy('id')->value('slug');
    }

    /**
     * IDs of the items whose images a trial visitor may load, in the same
     * order and slice the game pages use.
     */
    public static function mediaIds(string $type): array
    {
        $query = match ($type) {
            'control' => \App\Models\ControlCard::active()->orderBy('sort_order')->orderBy('id'),
            'challenge' => \App\Models\ChallengeCard::active()->orderBy('sort_order')->orderBy('id'),
            'scratch' => \App\Models\ScratchCard::active()->orderBy('sort_order')->orderBy('number'),
            'spinner' => \App\Models\SpinnerImage::active()->orderBy('sort_order')->orderBy('id'),
            default => null,
        };

        return $query ? $query->limit(self::size())->pluck('id')->all() : [];
    }

    public static function snakesDice(): array
    {
        return array_slice(self::SNAKES_DICE, 0, self::size());
    }

    /**
     * Destination square of every roll, mirroring resources/js/games/snakes-game.js:
     * two players alternate from square 0, a landing follows at most one link,
     * and an overshoot past 100 keeps the player in place.
     */
    public static function snakesDestinations(array $dice, array $links): array
    {
        $positions = [0, 0];
        $destinations = [];
        $linkMap = collect($links)->pluck('to', 'from');

        foreach (array_values($dice) as $turn => $die) {
            $player = $turn % 2;
            $landed = $positions[$player] + $die;
            if ($landed > 100) {
                $destinations[] = $positions[$player];
                continue;
            }
            $positions[$player] = $linkMap[$landed] ?? $landed;
            $destinations[] = $positions[$player];
        }

        return $destinations;
    }
}
