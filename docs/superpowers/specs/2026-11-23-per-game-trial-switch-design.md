# Per-game free-trial switch — design (short)

**Intent.** The owner wants to decide, per game, whether visitors without a
subscription get a free taste or the game is fully locked behind a
subscription. The free taste shrinks from 5 items to 3.

**Goals**
- Admin checkbox on the game create/edit form: «متاحة كتجربة مجانية (أول N)».
- Trial on: behaviour stays exactly as today (first N items, same order every time).
- Trial off: a non-subscriber pressing «العب» is redirected to the game's
  subscribe page (the pre-trial behaviour), and every server endpoint that
  serves game content (card API, scratch card API, game media) refuses with 403.
- Game detail page: when trial is off, the «جرّب مجاناً» button is hidden and the
  pill reads «بالاشتراك».
- Admin games list shows a small «تجربة» / «مقفولة» badge per game.
- Trial size default becomes 3, and the stored `trial_items` setting is set to 3.

**Non-goals.** Per-game trial size (one global size stays). Changing the home
page cards.

**Anti-goals.** A locked game must never leak content through a direct URL.

**Approaches considered**
1. *Boolean `trial_enabled` column on `games`, default true (picked).* Simple,
   matches the existing `is_free`/`is_active` flags, existing games keep working.
2. Store a list of trial game ids in `settings`. Rejected: separate from the game
   form, easy to drift when games are added or deleted.
3. Global on/off setting. Rejected: owner asked for per-game control.

**Decided by the owner.** Per-game control; default on for existing games
(proposed, approved); trial size 3.

**Left to the builder (my calls).** `GameTrial::locked(Game, ?User)` as the single
check (not subscribed and trial off). Media route serves the trial slice only
when at least one active game of that type has the trial on.
