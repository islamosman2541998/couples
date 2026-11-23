# Per-game Trial Switch Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Let the admin turn the free trial on or off per game, and shrink the trial to 3 items.

**Architecture:** A `trial_enabled` boolean on `games` (default true). `GameTrial::locked()` is the single check every content endpoint uses; the play route redirects locked visitors to the subscribe page.

**Tech Stack:** Laravel 11, Blade, PHPUnit (SQLite in-memory).

**Spec:** `docs/superpowers/specs/2026-11-23-per-game-trial-switch-design.md`

## Global Constraints
- Existing games keep the trial on (column default `true`).
- Trial size default `3`; the stored `trial_items` setting becomes `'3'`.
- Copy: checkbox «متاحة كتجربة مجانية (أول N)», pill «بالاشتراك», badges «تجربة» / «مقفولة».

## Review Focus
- Locked game, guest hits `/game-media/{type}/{id}` for an item in the trial slice → 403.
- Two active games of the same type, one locked → media follows the game with trial on.
- Admin unticks the checkbox (field absent from request) → saved as false, not ignored.
- Subscriber to a locked game → full access, no redirect.
- Locked game detail page must not show «جرّب مجاناً».

---

### Task 1: `trial_enabled` column, `GameTrial::locked`, locked behaviour on every endpoint

**Files:** migration `database/migrations/2026_11_23_000001_add_trial_enabled_to_games.php`; `app/Models/Game.php`; `app/Support/GameTrial.php`; `app/Http/Controllers/GameController.php`; `app/Http/Controllers/CardApiController.php`; `app/Http/Controllers/GameMediaController.php`; test `tests/Feature/GameTrialTest.php`.

**Produces:** `Game::$trial_enabled` (bool cast, fillable); `GameTrial::locked(Game $game, ?User $user): bool` = `applies() && ! $game->trial_enabled`.

- [ ] Test `test_locked_game_sends_guests_to_subscribe_and_blocks_content`: game with `trial_enabled=false` → `/games/{slug}/play` redirects to `subscribe.create`; card API, scratch card API and media for a trial-slice item → 403; a subscriber still gets 200 on play.
- [ ] Run, expect FAIL (play returns 200).
- [ ] Implement migration (column after `is_free`, default true), model, `locked()`, guards in `play()`, `scratchCard()`, `CardApiController`, media controller (trial slice only when an active game of the type has trial on).
- [ ] Run test, expect PASS; run full suite.

### Task 2: Admin checkbox, admin badge, game detail page

**Files:** `app/Http/Controllers/Admin/GameManageController.php`; `resources/views/admin/games/{create,edit,index}.blade.php`; `resources/views/games/show.blade.php`; test `tests/Feature/GameTrialTest.php`.

- [ ] Test `test_admin_can_turn_a_game_trial_off_and_on`: PUT without `trial_enabled` → false; with `1` → true; edit form shows `name="trial_enabled"`; index shows «مقفولة».
- [ ] Test `test_locked_game_page_offers_subscription_only`: detail page has no «جرّب مجاناً», shows «بالاشتراك».
- [ ] Run, expect FAIL. Implement (`$request->boolean('trial_enabled')` in store/update). Run, expect PASS.

### Task 3: Trial of 3

**Files:** `app/Support/GameTrial.php` (`DEFAULT_SIZE = 3`); migration sets `trial_items` to `'3'` via `Setting::set`; tests that assume 5.

- [ ] Change assertions in `GameTrialTest` from 5 to 3 (lists, card API, scratch, spinner, snakes dice `[3, 6, 5]`, media) and watch them fail.
- [ ] Set `DEFAULT_SIZE = 3` and add the setting update to the Task 1 migration. Run full PHP + node suites, `npm run build`.
