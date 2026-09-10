<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>

    <div class="control-game" x-data="controlGame()" x-init="$watch('screen', () => $nextTick(() => $el.scrollIntoView({block: 'start'})))">
        <section x-show="screen === 'intro'" class="control-intro">
            <a href="{{ route('home') }}" class="control-back">← كل الألعاب</a>
            <div class="control-heading">
                <div class="control-crown" aria-hidden="true">♛</div>
                <p class="control-eyebrow">كارت واحد. قرار واحد. الدور عليك.</p>
                <h1>{{ $game->name }}</h1>
                <p>مين هيمسك زمام الجولة؟</p>
                <div class="control-tags"><span>شخصين</span><span>{{ count($cards) }} كارت</span><span>أدوار بالتبادل</span></div>
            </div>

            <div class="control-rules">
                <h2>قوانين اللعبة وطريقة اللعب <span aria-hidden="true">✦</span></h2>
                <div class="control-rule-body">
                    <h3>طريقة اللعب</h3>
                    <p>كل كارت فيه تحدٍ للاعب اللي عليه الدور، وشريكه يقود التحدي. الكروت بتتخلط، وبعد كل قرار الدور بيتبدّل تلقائيًا.</p>
                    <div class="control-points"><span><b>+٢</b> نفذت التحدي</span><span><b>−٢</b> رفضت التحدي</span></div>
                    <ul>
                        <li>أول رفض لكل لاعب مجاني، وبعده كل رفض يخصم نقطتين.</li>
                        <li>الكارت ما بيتكررش في نفس الجولة، والنقاط ممكن تبقى بالسالب.</li>
                        <li>اضغط على زر التكبير لقراءة الكارت بحجم أكبر.</li>
                        <li>اللعب باتفاقكم، وتقدروا ترفضوا أو توقفوا في أي وقت.</li>
                    </ul>
                </div>
            </div>

            <fieldset class="control-starter" :disabled="hasRound">
                <legend>مين يبدأ؟</legend>
                <label :class="startingPlayer === 0 && 'is-selected'"><input type="radio" name="starting-player" :value="0" x-model.number="startingPlayer"> الزوج</label>
                <label :class="startingPlayer === 1 && 'is-selected'"><input type="radio" name="starting-player" :value="1" x-model.number="startingPlayer"> الزوجة</label>
            </fieldset>
            <button x-show="!hasRound" @click="startGame()" :disabled="allCards.length === 0" class="control-primary">ابدأ اللعب <span aria-hidden="true">←</span></button>
            <button x-show="hasRound" x-cloak @click="screen = 'game'" class="control-primary">كمّل الجولة <span aria-hidden="true">←</span></button>
            <p x-show="allCards.length === 0" class="control-empty">الكروت لسه بتتجهز. ارجعوا قريب علشان تبدأوا الجولة.</p>
            <p class="control-footnote">الجولة على الجهاز ده · تحديث الصفحة يبدأ من جديد</p>
        </section>

        <section x-show="screen === 'game'" x-cloak class="control-play" aria-label="جولة السيطرة">
            <header class="control-toolbar">
                <button @click="screen = 'intro'" class="control-back">← القواعد</button>
                <span class="control-mini-title">♛ {{ $game->name }}</span>
                <button @click="finishGame()" class="control-back">إنهاء الجولة</button>
            </header>
            <div class="control-scoreboard">
                <template x-for="(player, index) in players" :key="index">
                    <div class="control-score" :class="currentPlayer === index && 'is-current'">
                        <span x-text="player.name"></span>
                        <strong dir="ltr" x-text="player.score"></strong>
                        <small x-text="player.refused === 0 ? 'رفض مجاني متاح' : 'استخدم الرفض المجاني'"></small>
                    </div>
                </template>
            </div>
            <div class="control-progress-heading"><span>تقدّم الجولة</span><span dir="ltr"><b x-text="currentIndex + 1"></b> / <span x-text="deck.length"></span></span></div>
            <div class="control-progress" role="progressbar" aria-label="الكروت المنتهية" :aria-valuenow="currentIndex" :aria-valuemax="deck.length" aria-valuemin="0"><span :style="`width: ${deck.length ? currentIndex / deck.length * 100 : 0}%`"></span></div>

            <div class="control-stage">
                <div class="control-turn"><span class="control-dot"></span> دور <strong x-text="players[currentPlayer].name"></strong></div>
                <article class="control-card" :class="currentPlayer === 0 ? 'control-blue' : 'control-purple'" aria-live="polite" aria-atomic="true">
                    <div class="control-card-top"><span>تحدّي السيطرة</span><span aria-hidden="true">✦</span></div>
                    <template x-if="currentCard?.image"><img :src="currentCard.image" :alt="currentCard.title" class="control-card-image"></template>
                    <div class="control-card-copy"><h2 x-text="currentCard?.title"></h2><p x-text="currentCard?.description"></p></div>
                    <div class="control-card-bottom"><span x-text="'دور ' + players[currentPlayer].name"></span><button @click="$refs.enlarged.showModal()" aria-label="تكبير الكارت" title="تكبير الكارت">⛶</button></div>
                </article>
                <p class="control-feedback" role="status" x-text="feedback || 'اقرأوا التحدي… والقرار لصاحب الدور'"></p>
            </div>

            <div class="control-actions">
                <button @click="answer(true)" :disabled="locked" class="control-answer control-accept"><span aria-hidden="true">✓</span><b>نفذت</b><small>+٢ نقطة</small></button>
                <button @click="answer(false)" :disabled="locked" class="control-answer control-reject"><span aria-hidden="true">×</span><b>رفضت</b><small x-text="players[currentPlayer].refused === 0 ? 'المرة دي مجانًا' : '−٢ نقطة'"></small></button>
            </div>
            <p class="control-footnote">كل قرار ينقلكم لكارت جديد · خُدوا وقتكم</p>
        </section>

        <section x-show="screen === 'done'" x-cloak class="control-result">
            <div class="control-crown" aria-hidden="true">♛</div>
            <p class="control-eyebrow" x-text="answeredCount === deck.length ? 'اكتملت الجولة' : 'انتهت الجولة هنا'"></p>
            <h2 x-text="winnerText"></h2>
            <p>لعبتوا <b x-text="answeredCount"></b> من <b x-text="deck.length"></b> كارت</p>
            <div class="control-result-grid">
                <template x-for="(player, index) in players" :key="index">
                    <div class="control-result-player"><h3 x-text="player.name"></h3><strong dir="ltr" x-text="player.score"></strong><span>نقطة</span><p><span x-text="player.done"></span> تنفيذ · <span x-text="player.refused"></span> رفض</p></div>
                </template>
            </div>
            <button @click="resetGame()" class="control-primary">جولة جديدة ↻</button>
            <a href="{{ route('home') }}" class="control-back">الرجوع للألعاب</a>
        </section>

        <dialog x-ref="enlarged" class="control-dialog" @click="if ($event.target === $refs.enlarged) $refs.enlarged.close()">
            <div class="control-dialog-content">
                <button @click="$refs.enlarged.close()" autofocus class="control-dialog-close" aria-label="إغلاق الكارت المكبر">إغلاق ×</button>
                <p class="control-eyebrow" x-text="'دور ' + players[currentPlayer].name"></p>
                <h2 x-text="currentCard?.title"></h2>
                <template x-if="currentCard?.image"><img :src="currentCard.image" :alt="currentCard.title"></template>
                <p x-text="currentCard?.description"></p>
            </div>
        </dialog>
    </div>

    @push('styles')
    <style>
        [x-cloak]{display:none!important}
        .control-game{--gold:#f8cb68;background:radial-gradient(ellipse at 50% 0, #38201866, transparent 55%),#08090e;color:#f6f3ec;min-height:calc(100svh - 64px);padding:24px 20px 40px;scroll-margin-top:64px}
        .control-game button,.control-game a,.control-game label{touch-action:manipulation}
        .control-game button:focus-visible,.control-game a:focus-visible,.control-game label:focus-within{outline:2px solid var(--gold);outline-offset:5px}
        .control-game button:disabled{opacity:.45;cursor:not-allowed}
        .control-intro,.control-play,.control-result{max-width:600px;margin:0 auto}
        .control-back{color:#aaa7ac;font-size:14px;padding:10px 0;display:inline-block}
        .control-back:hover{color:#fff}
        .control-heading{text-align:center;margin:8px 0 30px}
        .control-crown{font-family:Georgia,serif;font-size:68px;line-height:1.1;color:var(--gold);text-align:center;text-shadow:0 0 40px #e5ac3940;margin-bottom:15px}
        .control-eyebrow{font-size:12px;letter-spacing:.7px;color:var(--gold);margin:10px 0}
        .control-heading h1{font-size:44px;font-weight:900;line-height:1.35;margin-bottom:7px}
        .control-heading>p:last-of-type,.control-result>p{color:#aba7b0}
        .control-tags{display:flex;justify-content:center;gap:8px;margin-top:20px;font-size:12px;color:#d6d0c4}
        .control-tags span{padding:6px 12px;border:1px solid #ffffff16;border-radius:20px;background:#ffffff04}
        .control-rules{border:1px solid #eab74c33;border-radius:18px;overflow:hidden;background:linear-gradient(140deg,#26101890,#0c0c16)}
        .control-rules h2{padding:16px 20px;background:#f8cb6812;color:var(--gold);font-weight:700;display:flex;justify-content:space-between;border-bottom:1px solid #eab74c22}
        .control-rule-body{padding:20px;font-size:14px;line-height:1.9;color:#c7c3cc}
        .control-rule-body h3{color:#fff;font-weight:700;margin-bottom:5px}
        .control-rule-body ul{padding-right:17px;list-style:disc;display:grid;gap:5px}
        .control-rule-body li::marker{color:var(--gold)}
        .control-points{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:18px 0}
        .control-points span{background:#ffffff05;border:1px solid #ffffff0c;border-radius:10px;padding:10px;display:flex;gap:10px;align-items:center}
        .control-points b{color:#6bdea2;font-size:23px;direction:ltr}
        .control-points span:last-child b{color:#ff7a83}
        .control-starter{display:flex;justify-content:center;gap:10px;margin:24px 0 20px;text-align:center}
        .control-starter legend{margin-bottom:10px;color:#c7c3cc;font-size:14px;width:100%}
        .control-starter label{padding:10px 32px;background:#12131b;border:1px solid #ffffff16;border-radius:10px;cursor:pointer}
        .control-starter label.is-selected{color:var(--gold);border-color:#f8cb6870;background:#f8cb6810}
        .control-starter input{margin-left:7px;accent-color:#eab74c}
        .control-primary{display:flex;justify-content:center;gap:25px;align-items:center;width:100%;background:linear-gradient(100deg,#e9ae41,#ffda80);color:#261a09;padding:16px;border-radius:12px;font-size:18px;font-weight:900;box-shadow:0 6px 30px #f4bf4a12}
        .control-primary:hover{filter:brightness(1.06)}
        .control-footnote{text-align:center;color:#827d89;font-size:12px;margin:16px 0 0}
        .control-empty{text-align:center;margin-top:20px;color:var(--gold)}
        .control-play{min-height:calc(100svh - 128px);display:flex;flex-direction:column}
        .control-toolbar{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:20px}
        .control-mini-title{color:var(--gold);font-size:14px;font-weight:700}
        .control-scoreboard{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .control-score{display:grid;grid-template-columns:1fr auto;align-items:center;padding:13px 16px;border:1px solid #ffffff12;background:#11121a;border-radius:14px;color:#b3b0ba}
        .control-score strong{font-size:25px;color:var(--gold)}
        .control-score small{font-size:11px;grid-column:1/-1;color:#918a9a}
        .control-score.is-current{border-color:#f8cb6866;background:#f8cb6809}
        .control-score.is-current>span{color:#fff}
        .control-progress-heading{display:flex;justify-content:space-between;color:#96909f;font-size:12px;margin:20px 0 8px}
        .control-progress{height:3px;background:#23202a;border-radius:5px;overflow:hidden;direction:ltr}
        .control-progress>span{display:block;height:100%;background:var(--gold);transition:width .3s}
        .control-stage{flex:1;display:flex;flex-direction:column;justify-content:center;padding:32px 0 12px}
        .control-turn{display:flex;justify-content:center;align-items:center;gap:7px;font-size:14px;color:#b9b1c7;margin-bottom:18px}
        .control-turn strong{color:#fff}
        .control-dot{height:6px;width:6px;border-radius:50%;background:var(--gold);box-shadow:0 0 10px #f8cb6844}
        .control-card{border:1px solid #9dbafa60;border-radius:24px 6px 24px 6px;overflow:hidden;background:radial-gradient(ellipse at 5% 90%,#2158b866,transparent 65%),linear-gradient(135deg,#17274c,#0b1125);box-shadow:0 22px 80px #0008;padding:16px}
        .control-card.control-purple{border-color:#c9a2f260;background:radial-gradient(ellipse at 5% 90%,#873ab666,transparent 65%),linear-gradient(135deg,#34204c,#170e28)}
        .control-card-top{display:flex;justify-content:space-between;color:#c2c8e5;font-size:11px;padding:0 5px 14px;letter-spacing:1px}
        .control-card-top span:last-child{color:var(--gold);font-size:18px}
        .control-card-copy{background:#f6f3ee;color:#272131;border-radius:13px 4px 13px 4px;padding:26px 24px;min-height:150px;display:flex;flex-direction:column;justify-content:center;text-align:center;overflow-wrap:anywhere}
        .control-card-copy h2{font-size:22px;font-weight:900;margin-bottom:12px}
        .control-card-copy p{font-size:18px;line-height:1.9;white-space:pre-line}
        .control-card-image{width:100%;max-height:260px;object-fit:contain;border-radius:8px;margin-bottom:12px}
        .control-card-bottom{display:flex;justify-content:space-between;align-items:center;padding:12px 3px 0;font-size:11px;color:#e1d9ec}
        .control-card-bottom button{font-size:24px;width:40px;height:36px;border-radius:8px;background:#0005;color:#fff}
        .control-feedback{font-size:12px;min-height:24px;text-align:center;color:#b3a8bf;margin-top:18px}
        .control-actions{display:flex;justify-content:space-around;gap:20px;padding-top:14px}
        .control-answer{display:flex;flex-direction:column;align-items:center;gap:6px;min-width:108px;padding:8px 18px;border-radius:15px}
        .control-answer>span{display:grid;place-items:center;width:66px;height:66px;border-radius:50%;font-size:34px;color:white;margin-bottom:5px}
        .control-accept>span{background:#149650;box-shadow:0 6px 25px #14965025}
        .control-reject>span{background:#dc3d48;box-shadow:0 6px 25px #dc3d4825}
        .control-answer b{font-size:16px}.control-answer small{font-size:11px;color:#aaa1b5}
        .control-result{text-align:center;padding:60px 0}
        .control-result h2{font-size:32px;font-weight:900;margin:15px 0}
        .control-result-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:30px 0}
        .control-result-player{background:#14121b;border:1px solid #f8cb6826;border-radius:18px;padding:24px 12px;display:flex;flex-direction:column}
        .control-result-player strong{font-size:46px;color:var(--gold);font-weight:900}
        .control-result-player p{font-size:12px;color:#b5adbd;margin-top:14px}
        .control-result>.control-back{margin-top:20px}
        .control-dialog{border:1px solid #c2a66b55;border-radius:20px;background:#14101f;color:#f6f3ec;width:min(92vw,850px);max-height:90svh;padding:0}
        .control-dialog::backdrop{background:#000d;backdrop-filter:blur(6px)}
        .control-dialog-content{padding:24px;overflow-wrap:anywhere}
        .control-dialog-close{display:block;margin-right:auto;padding:10px;color:var(--gold)}
        .control-dialog h2{font-size:28px;font-weight:900;margin:20px 0}
        .control-dialog img{max-height:50svh;object-fit:contain;width:100%;margin-bottom:20px}
        .control-dialog-content>p:last-child{font-size:24px;line-height:2;white-space:pre-line}
        @media(max-width:400px){.control-game{padding:16px 14px 30px}.control-mini-title{font-size:12px}.control-toolbar{gap:8px}.control-back{font-size:12px}.control-card-copy{padding:22px 16px}.control-card-copy p{font-size:17px}.control-score{padding:10px}.control-points span{padding:8px;gap:7px}.control-heading h1{font-size:38px}}
        @media(prefers-reduced-motion:reduce){.control-progress>span{transition:none}}
    </style>
    @endpush

    @push('scripts')
    <script>
        function controlGame() {
            return {
                allCards: @json($cards),
                screen: 'intro',
                startingPlayer: 0,
                currentPlayer: 0,
                currentIndex: 0,
                deck: [],
                players: [{name: 'الزوج', score: 0, done: 0, refused: 0}, {name: 'الزوجة', score: 0, done: 0, refused: 0}],
                locked: false,
                unlockTimer: null,
                feedback: '',
                get currentCard() { return this.deck[this.currentIndex] || null; },
                get hasRound() { return this.deck.length > 0 && this.currentIndex < this.deck.length; },
                get answeredCount() { return this.players.reduce((total, player) => total + player.done + player.refused, 0); },
                get winnerText() {
                    if (!this.answeredCount) return 'الجولة لسه ما بدأتش';
                    if (this.players[0].score === this.players[1].score) return 'تعادل… السيطرة مشتركة!';
                    return 'السيطرة من نصيب ' + (this.players[0].score > this.players[1].score ? 'الزوج' : 'الزوجة') + ' 👑';
                },
                startGame() {
                    if (!this.allCards.length) return;
                    this.resetGame();
                    this.deck = this.shuffle([...this.allCards]);
                    this.currentPlayer = this.startingPlayer === 1 ? 1 : 0;
                    this.screen = 'game';
                },
                answer(completed) {
                    if (this.screen !== 'game' || this.locked || !this.currentCard || typeof completed !== 'boolean') return;
                    this.locked = true;
                    const player = this.players[this.currentPlayer];
                    const points = completed ? 2 : (player.refused === 0 ? 0 : -2);
                    player.score += points;
                    if (completed) player.done++;
                    else player.refused++;
                    this.feedback = player.name + ': ' + (completed ? 'نفذت · +٢ نقطة' : (points === 0 ? 'رفض مجاني · بدون خصم' : 'رفضت · −٢ نقطة'));
                    this.currentIndex++;
                    if (this.currentIndex >= this.deck.length) {
                        this.finishGame();
                        return;
                    }
                    this.currentPlayer = 1 - this.currentPlayer;
                    this.unlockTimer = setTimeout(() => { this.locked = false; this.unlockTimer = null; }, 500);
                },
                finishGame() {
                    if (this.screen !== 'game') return;
                    clearTimeout(this.unlockTimer);
                    this.unlockTimer = null;
                    this.locked = false;
                    this.screen = 'done';
                },
                resetGame() {
                    clearTimeout(this.unlockTimer);
                    this.unlockTimer = null;
                    this.screen = 'intro';
                    this.deck = [];
                    this.currentIndex = 0;
                    this.currentPlayer = 0;
                    this.locked = false;
                    this.feedback = '';
                    this.players = this.players.map(player => ({name: player.name, score: 0, done: 0, refused: 0}));
                },
                destroy() { clearTimeout(this.unlockTimer); },
                shuffle(cards) {
                    for (let i = cards.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [cards[i], cards[j]] = [cards[j], cards[i]];
                    }
                    return cards;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
