<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>
    @push('styles')
        @vite('resources/css/snakes-game.css')
    @endpush

    <div class="sl-game" x-data="snakesGame" x-init="$watch('screen', () => $nextTick(() => $el.scrollIntoView({block: 'start'}))); $watch('phase', value => { if (window.matchMedia('(max-width: 900px)').matches &amp;&amp; ['rolling', 'challenge', 'won'].includes(value)) $nextTick(() => (value === 'rolling' ? $refs.boardArea : $refs.turnArea).scrollIntoView({block: 'start', behavior: reducedMotion ? 'instant' : 'smooth'})) })">
        <div class="sl-shell">
            <header class="sl-header">
                <a href="{{ route('home') }}" class="sl-quiet">← الألعاب</a>
                <span class="sl-brand">♥ وقت ليكم</span>
                <button class="sl-quiet" @click="$refs.rules.showModal()">طريقة اللعب ⓘ</button>
            </header>

            <section x-show="screen === 'intro'" class="sl-intro">
                <div class="sl-intro-copy">
                    <span class="sl-eyebrow">ليلة مختلفة… وخطوة تقرّبكم</span>
                    <h1>السلم <span>والتعبان</span><i>♥</i></h1>
                    <p class="sl-lead">شوية حظ. شوية مفاجآت.<br>و١٠٠ فرصة تعيشوا لحظة حلوة سوا.</p>
                    <div class="sl-feature-row"><span>١٠٠ خانة</span><span>لاعبين</span><span>جولة على مزاجكم</span></div>
                    <div class="sl-setup">
                        <h2>جهّزوا سهرتكم <span>✦</span></h2>
                        <div class="sl-name-fields">
                            <label><span><i class="sl-dot-blue"></i> اللاعب الأول</span><input x-model="players[0].name" maxlength="24" autocomplete="off" aria-label="اسم اللاعب الأول" placeholder="الزوج"></label>
                            <label><span><i class="sl-dot-rose"></i> اللاعب الثاني</span><input x-model="players[1].name" maxlength="24" autocomplete="off" aria-label="اسم اللاعب الثاني" placeholder="الزوجة"></label>
                        </div>
                        <label class="sl-prize-label"><span>هدية الفوز <small>اختاروا حاجة تفرّحكم</small></span><input x-model="prize" maxlength="160" aria-label="هدية الفوز" placeholder="الفائز يختار موعدنا الجاي"></label>
                        <fieldset class="sl-first"><legend>مين يبدأ الحكاية؟</legend><label><input type="radio" name="sl-first" :value="0" x-model.number="startingPlayer"><span x-text="players[0].name || 'اللاعب الأول'"></span></label><label><input type="radio" name="sl-first" :value="1" x-model.number="startingPlayer"><span x-text="players[1].name || 'اللاعب الثاني'"></span></label></fieldset>
                        <button x-show="!savedRound" @click="startGame()" :disabled="!canPlay" class="sl-primary">يلا نبدأ الحكاية <span>←</span></button>
                        <template x-if="savedRound"><div class="sl-resume"><button @click="resumeGame()" class="sl-primary">كمّل جولتنا ←</button><button @click="$refs.restart.showModal()" class="sl-quiet">ابدأوا من جديد</button><small>في جولة محفوظة على الجهاز ده</small></div></template>
                        <p class="sl-note">كل تحدٍ باختياركم، والتخطي متاح دائمًا.</p>
                    </div>
                </div>
                <div class="sl-preview" aria-hidden="true">
                    <div class="sl-preview-top"><span>THE COUPLE EDITION</span><span>✧</span></div>
                    <div class="sl-preview-art">
                        <svg viewBox="0 0 360 340" fill="none">
                            <rect x="40" y="32" width="280" height="280" rx="18" fill="#f5e7d7"/>
                            <path d="M110 32V312M180 32V312M250 32V312M40 102H320M40 172H320M40 242H320" stroke="#cfb7a1"/>
                            <path d="M95 256L229 65M112 268L246 77M109 247L126 259M125 224L142 236M142 201L159 213M159 177L176 189M175 154L192 166M192 130L209 142M209 107L226 119M225 84L242 96" stroke="#ae7b39" stroke-width="6" stroke-linecap="round"/>
                            <path d="M268 129C187 113 163 150 229 189C295 228 227 278 172 267" stroke="#617968" stroke-width="17" stroke-linecap="round"/>
                            <ellipse cx="270" cy="130" rx="19" ry="14" fill="#617968"/><circle cx="277" cy="126" r="3" fill="white"/><circle cx="267" cy="121" r="3" fill="white"/>
                            <circle cx="73" cy="210" r="19" fill="#be5e78" stroke="white" stroke-width="4"/><circle cx="282" cy="279" r="19" fill="#578aac" stroke="white" stroke-width="4"/>
                            <text x="72" y="81" fill="#ba6a77" font-size="32">♥</text><text x="270" y="75" fill="#ba6a77" font-size="28">✦</text><text x="67" y="293" fill="#ad824c" font-size="24">✧</text>
                        </svg>
                        <div class="sl-floating-die">⚄</div>
                    </div>
                    <p>الحظ يغيّر مكانكم.<br><strong>واللحظات تقرّبكم.</strong></p>
                    <div class="sl-preview-footer"><span>♥ رومانسية</span><span>✧ مرح</span><span>✦ تقارب</span></div>
                </div>
            </section>

            <section x-show="screen === 'game'" x-cloak>
                <div class="sl-game-heading"><div><span class="sl-eyebrow">كل رمية… حكاية</span><h1>{{ $game->name }}</h1></div><button @click="$refs.restart.showModal()" :disabled="busy" class="sl-quiet">جولة جديدة ↻</button></div>
                <div class="sl-play-layout">
                    <div class="sl-board-column" x-ref="boardArea" style="scroll-margin-top:80px">
                        <div class="sl-players">
                            <template x-for="(player, index) in players" :key="index"><div class="sl-player" :class="{'sl-player-current': currentPlayer === index, 'sl-player-blue': index === 0, 'sl-player-rose': index === 1}"><span class="sl-player-number" x-text="index + 1"></span><div><strong x-text="player.name"></strong><small x-text="phase === 'won' ? (player.position === 100 ? 'الفائز 👑' : 'شريك الحكاية') : (currentPlayer === index ? 'الدور عليك' : 'استنى دورك')"></small></div><b x-text="displayPositions[index] || 'البداية'"></b></div></template>
                        </div>
                        <div class="sl-board-frame">
                            <div class="sl-board-caption"><span>رحلتكم إلى الخانة ١٠٠</span><button @click="$refs.largeBoard.showModal()" class="sl-quiet" aria-label="تكبير اللوحة">تكبير ⛶</button></div>
                            <x-snakes-board />
                            <div class="sl-board-key"><span>↗ سلم</span><span>↘ تعبان</span><span>♥ رومانسية</span><label><input type="checkbox" x-model="showPaths"> المسارات</label></div>
                        </div>
                        <p class="sl-note">البداية أسفل اليمين ← المسار متعرّج · اضغط أي خانة لقراءتها</p>
                    </div>

                    <aside class="sl-play-panel" x-ref="turnArea" style="scroll-margin-top:80px">
                        <div x-show="phase !== 'won'" class="sl-turn-panel">
                            <span class="sl-eyebrow" x-text="'دور ' + activePlayer.name"></span>
                            <h2 x-text="phase === 'challenge' ? 'لحظتكم هنا' : busy ? 'يا ترى الحظ مخبّي إيه؟' : 'جاهز للمفاجأة؟'"></h2>
                            <div class="sl-dice-row"><div class="sl-dice" :class="busy && 'sl-dice-rolling'" role="img" :aria-label="'النرد: ' + displayDice"><template x-for="pip in 9" :key="pip"><i :class="dicePips.includes(pip) && 'sl-pip-visible'"></i></template></div><div><b x-text="busy ? 'بنحرّك القطعة…' : 'آخر رمية: ' + (turns ? dice : '—')"></b><small>من ١ إلى ٦ · دور لكل لاعب</small></div></div>
                            <p class="sl-notice" role="status" x-text="notice"></p>
                            <button x-show="phase !== 'challenge'" @click="roll()" :disabled="phase !== 'ready'" class="sl-primary"><span x-text="busy ? 'استنى المفاجأة…' : 'ارمِ النرد'"></span><span aria-hidden="true">⚄</span></button>
                            <div x-show="phase === 'challenge'" x-cloak class="sl-task">
                                <div class="sl-task-label"><span x-text="'خانة ' + (task?.number ?? '')"></span><span x-text="task?.active ? ({warm: '✦ تقارب', playful: '✧ مرح', romantic: '♥ رومانسية'}[task?.mood]) : '☾ استراحة'"></span></div>
                                <h3 x-text="task?.title"></h3><p x-text="task?.content"></p>
                                <div class="sl-task-actions"><button @click="completeTask(true)" class="sl-primary" x-text="task?.active ? 'عملناها ✓' : 'كمّلوا اللعب ←'"></button><button x-show="task?.active" @click="completeTask(false)" class="sl-secondary">تخطي</button></div>
                                <small>التخطي لا يغيّر مكانك على اللوحة.</small>
                            </div>
                        </div>
                        <div x-show="phase === 'won'" x-cloak class="sl-win" role="status"><div class="sl-win-crown">♛</div><span class="sl-eyebrow">وصلنا للقمة</span><h2><span x-text="winner?.name"></span><br>كسب الحكاية!</h2><p>بعد <b x-text="turns"></b> رمية ولحظات حلوة كتير.</p><div class="sl-prize"><span>هدية الفوز</span><strong x-text="prize"></strong></div><template x-for="(player, index) in players" :key="index"><p class="sl-win-stat"><span x-text="player.name"></span><span><b x-text="player.completed"></b> تحدي · <b x-text="player.skipped"></b> تخطي</span></p></template><button @click="$refs.restart.showModal()" class="sl-primary">نلعب تاني؟ ↻</button></div>
                        <div class="sl-prize-mini" x-show="phase !== 'won'"><span>♛</span><div><small>اللي يوصل الأول يختار</small><p x-text="prize"></p></div></div>
                        <details class="sl-history" x-show="history.length"><summary>حكاية آخر الرميات <span x-text="'(' + turns + ')'"></span></summary><template x-for="(item, index) in history" :key="index"><p><strong x-text="item.name"></strong><span x-text="'⚄ ' + item.die + ' · خانة ' + item.position + ' · ' + item.action"></span></p></template></details>
                        <p class="sl-note" x-text="storageAvailable ? 'تقدمكم بيتحفظ تلقائيًا على نفس المتصفح' : 'الحفظ غير متاح في المتصفح ده؛ خليكوا في الصفحة علشان تكمّلوا.'"></p>
                    </aside>
                </div>
            </section>
        </div>

        <dialog x-ref="rules" class="sl-dialog"><div class="sl-dialog-body"><button @click="$refs.rules.close()" class="sl-dialog-close" autofocus aria-label="إغلاق القواعد">×</button><span class="sl-eyebrow">قبل أول رمية</span><h2>حكاية اللعبة</h2><ol><li>ابدأوا من خارج اللوحة، وارموا النرد بالتبادل؛ رقم ٦ لا يعطي دورًا إضافيًا.</li><li>السلم يطلعكم والتعبان ينزّلكم. التحدي هو تحدي الخانة النهائية بعد الحركة.</li><li>نفذوا التحدي أو تخطّوه بدون عقوبة؛ الاختيار دايمًا ليكم.</li><li>الفوز عند ١٠٠ بالضبط. لو الرمية أكبر من المطلوب تفضل مكانك ويتبدّل الدور.</li><li>التحديات والأسامي والجائزة تظهر على جهازكم. تقدروا توقفوا وتكملوا الجولة المحفوظة.</li></ol><button @click="$refs.rules.close()" class="sl-primary">تمام، فهمنا ♥</button></div></dialog>
        <dialog x-ref="peek" class="sl-dialog"><div class="sl-dialog-body"><button @click="$refs.peek.close()" class="sl-dialog-close" autofocus aria-label="إغلاق تفاصيل الخانة">×</button><span class="sl-eyebrow" x-text="'استكشاف الخانة ' + peekNumber"></span><h2 x-text="peekCell?.title"></h2><p class="sl-peek-content" x-text="peekCell?.content"></p><template x-for="link in links.filter(item => item.from === peekNumber)" :key="link.from"><p class="sl-peek-link" x-text="(link.to > link.from ? '↗ سلم يصعد بك إلى ' : '↘ تعبان ينزل بك إلى ') + link.to + '، ثم يظهر تحدي خانة الوصول.'"></p></template><p class="sl-note">دي معاينة فقط؛ مكان القطع والدور مش بيتغيّروا.</p></div></dialog>
        <dialog x-ref="largeBoard" class="sl-dialog sl-large-board"><div class="sl-dialog-body"><button @click="$refs.largeBoard.close()" class="sl-dialog-close" autofocus aria-label="إغلاق اللوحة المكبرة">×</button><h2>اللوحة كاملة</h2><p class="sl-note">حرّك اللوحة يمين وشمال على الموبايل، واضغط أي خانة لقراءة التحدي.</p><div class="sl-board-scroll"><div class="sl-board-zoom"><x-snakes-board /></div></div></div></dialog>
        <dialog x-ref="restart" class="sl-dialog"><div class="sl-dialog-body"><button @click="$refs.restart.close()" class="sl-dialog-close" autofocus aria-label="إغلاق تأكيد الجولة الجديدة">×</button><h2>نبدأ حكاية جديدة؟</h2><p class="sl-peek-content">هنمسح تقدم الجولة المحفوظة ونرجع القطعتين للبداية.</p><div class="sl-task-actions"><button @click="$refs.restart.close(); newGame()" class="sl-primary">أيوه، جولة جديدة</button><button @click="$refs.restart.close()" class="sl-secondary">كمّل الحالية</button></div></div></dialog>
    </div>

    @push('scripts')
    <script>window.snakesGameData = {cells: @json($cells), links: @json($boardLinks), storageKey: 'couples-snakes-{{ $game->id }}'};</script>
    @endpush
</x-app-layout>
