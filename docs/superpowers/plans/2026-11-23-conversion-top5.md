# Conversion Top 5 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Free 5-item trial in every paid game, cleaner checkout, approval notifications with ETA, and branded error pages.

**Architecture:** A `App\Support\GameTrial` helper decides trial mode and slices content server-side; `GameController::play()`, `CardApiController` and the scratch endpoint pass only trial items plus a `$trial` array to views. Two Blade components (`trial-banner`, `trial-paywall`) render the trial UI in each game. Checkout/notification/error-page changes are independent.

**Tech Stack:** Laravel 11, Blade, Alpine.js, Tailwind, Vite, PHPUnit, node:test.

**Spec:** `docs/superpowers/specs/2026-11-23-conversion-top5-design.md`

## Global Constraints

- Trial size: Setting `trial_items`, default 5, clamped 1–20.
- Trial order: `sort_order` then `id`; no client shuffle in trial mode.
- Paid content beyond the trial never appears in the HTML/JSON for a non-subscriber.
- `is_free` games and subscribers are unaffected.
- Copy in Egyptian Arabic; no new emoji in markup.
- Mail failure never blocks approve/reject.
- Error pages: no DB queries, no Vite, inline CSS.

## Review Focus

- Guest reloads mid-trial → same items, same order (test: two requests return identical item IDs).
- Card API called with `target=male` in trial → still the same 5 (target ignored).
- Scratch endpoint asked for a number outside the trial → 403.
- Snakes trial roll after the last fixed die → no roll, paywall shown (JS test).
- Approve when mailer throws → subscription still approved (test with a throwing mailer).

---

### Task 1: GameTrial helper + list games (who, challenge, control, know_me)

**Files:**
- Create: `app/Support/GameTrial.php`, `resources/views/components/trial-banner.blade.php`, `resources/views/components/trial-paywall.blade.php`
- Modify: `app/Http/Controllers/GameController.php` (`play()`), `resources/views/games/{who,challenge,control,know-me}-game.blade.php`
- Test: `tests/Feature/GameTrialTest.php`

**Interfaces — produces:**
- `GameTrial::size(): int`
- `GameTrial::applies(Game $game, ?User $user): bool` — `!$game->is_free && $game->price > 0 && !$user?->hasActiveSubscription($game->id)`
- `GameTrial::meta(Game $game, int $shown, int $total): array` → `['size'=>int,'shown'=>int,'total'=>int,'subscribe_url'=>string,'bundle_url'=>string,'price'=>string]`; views receive `$trial` = this array or `null`.
- `<x-trial-banner :trial="$trial" />`, `<x-trial-paywall :trial="$trial" :game="$game" />`

- [ ] Tests: guest GET `games.play` for a paid `who` game → 200 (not redirect), JSON blob in page contains exactly the first 5 questions by sort_order and not the 6th; two requests identical; subscriber gets all; free game guest gets all.
- [ ] Implement; in each view, when `trial` is set skip the shuffle, show banner, and replace the end/"play again" screen with the paywall.
- [ ] `php artisan test --filter=GameTrialTest` passes.

### Task 2: Card game trial

**Files:** `app/Http/Controllers/CardApiController.php`, `GameController::play()` card branch, `resources/views/games/card-game.blade.php`; test in `GameTrialTest`.

- [ ] Tests: guest API call for first level returns 5 cards ordered by sort_order,id even with `target=male`; other level → 403; subscriber unchanged.
- [ ] Implement: levels after the first render locked in trial; paywall after the 5th card.

### Task 3: Scratch trial

**Files:** `GameController::play()` scratch branch + `scratchCard()`, `resources/views/games/scratch-game.blade.php`; test in `GameTrialTest`.

- [ ] Tests: guest page has 5 cards only; endpoint for a trial number → 200; non-trial number → 403.
- [ ] Implement; board-finished state shows paywall in trial.

### Task 4: Spinner trial

**Files:** `GameController::play()` spinner branch, `resources/views/games/spinner-game.blade.php`.

- [ ] Test: guest page includes `trialSequence` = IDs of the first N items by sort_order.
- [ ] Implement: `spin()` uses `trialSequence[spinCount]` as winner when present; after N spins show paywall.

### Task 5: Snakes trial

**Files:** `app/Support/GameTrial.php` (`snakesDice(): array`, `snakesDestinations(array $dice, array $links): array`), `GameController::play()` snakes branch, `resources/js/games/snakes-game.js`, `resources/views/games/snakes-game.blade.php`; tests in `GameTrialTest` and `tests/snakes-game.test.mjs`.

- Fixed dice sequence: `[3, 6, 5, 2, 4, 1, 5, 3, 6, 2, 4, 1, 3, 5, 2, 6, 4, 1, 2, 5]`, first N used. Players alternate starting at 0; one link per landing (matches JS). With N=5 destinations are 22, 6, 6, 30, 10.
- [ ] PHP test: `snakesDestinations` of first 5 dice with `config('snakes.links')` → `[22, 6, 6, 30, 10]`; guest page cells outside {6,10,22,30} carry placeholder content, not DB content.
- [ ] JS test: `createSnakesGame({cells, links, trialDice:[3,6]})` — rolls yield 3 then 6; third roll is a no-op and `trialOver` is true.
- [ ] Implement `data.trialDice` (die = `trialDice[this.turns]`), getter `trialOver`, paywall in view.

### Task 6: Try-free button + trial setting

**Files:** `resources/views/games/show.blade.php` (or its include), `resources/views/admin/settings/index.blade.php`, `app/Http/Controllers/Admin/SettingController.php` (validate `trial_items` integer 1–20, `activation_eta` string ≤120).
- [ ] Test: game show page for a paid game contains a link to `games.play` labeled "جرّب مجاناً".

### Task 7: Checkout cleanup + ETA

**Files:** `resources/views/subscribe/create.blade.php`, `resources/js/checkout.js`, `resources/views/profile/index.blade.php`; tests `tests/Feature/CheckoutTest.php`, `tests/checkout.test.mjs`.
- [ ] Logged-in user: step-2 name/email become hidden inputs bound to details; phone visible only if the account has none.
- [ ] Copy buttons for number and amount (`navigator.clipboard.writeText`, fallback select), "اتنسخ ✓" for 2s.
- [ ] Prices: helper `App\Support\Money::egp(float|string $v): string` → "700" or "199.50"; used in checkout select/amount, home and game pages where `number_format(...,2)` was used for prices.
- [ ] ETA `Setting::get('activation_eta', 'عادةً خلال ساعات قليلة')` shown in payment section, success state, pending subscriptions on profile.

### Task 8: Approval/rejection notification + WhatsApp button

**Files:** Create `app/Notifications/SubscriptionStatusChanged.php`; modify `Admin/SubscriptionManageController` (approve/reject), `resources/views/admin/subscriptions/show.blade.php`; test `tests/Feature/SubscriptionNotificationTest.php`.
- [ ] Tests: approve sends notification to the subscription email (Notification::fake, on-demand route `mail`); reject includes admin note; with a throwing mailer approve still sets status approved.
- [ ] WhatsApp link helper `App\Support\Phone::whatsapp(string $phone): ?string` (`01xxxxxxxxx` → `201xxxxxxxxx`), button with prefilled message.

### Task 9: Error pages

**Files:** Create `resources/views/errors/layout.blade.php`, `404`, `403`, `419`, `500`, `503`; test `tests/Feature/ErrorPagesTest.php`.
- [ ] Test: GET `/games/does-not-exist` → 404 containing "الصفحة دي مش موجودة" and a link to home.

### Task 10: Verify

- [ ] `php artisan test --compact`, `node --test tests/*.test.mjs`, `npm run build` all pass.
- [ ] Browser check locally: guest plays 5 items in each game, reload shows the same, paywall appears.
