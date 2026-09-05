<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>
    <x-game-comfort-note />

    <div class="min-h-screen flex flex-col" x-data="challengeGame()">

        <!-- ===== شاشة البداية ===== -->
        <div x-show="screen === 'intro'" class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
            <div class="text-7xl mb-6">🎯</div>
            <h1 class="text-3xl font-black mb-3">{{ $game->name }}</h1>
            <p class="text-gray-400 mb-10 leading-relaxed max-w-sm">
                اسحب كارت عشوائي واكتشف التحدي<br>
                نفّذوه مع بعض وانتقلوا للتالي!
            </p>

            <div class="w-full max-w-xs mb-8 bg-gray-900 border border-gray-800 rounded-2xl p-4 text-sm text-gray-400 text-right space-y-2">
                <p>🎯 {{ count($cards) }} تحدي في انتظارك</p>
                <p>⏱️ بعض التحديات عليها تايمر</p>
                <p>🔀 الكروت تظهر بشكل عشوائي</p>
            </div>

            <!-- Category filter -->
            <div class="flex flex-wrap justify-center gap-2 mb-8 w-full max-w-sm">
                <button @click="filterCat = null; applyFilter()"
                        :class="filterCat === null ? 'ring-2 ring-purple-500 bg-purple-900/40' : 'bg-gray-800'"
                        class="px-4 py-2 rounded-xl text-sm font-medium text-gray-300 transition-all">
                    الكل
                </button>
                @foreach(\App\Models\ChallengeCard::$categories as $key => $cat)
                <button @click="filterCat = '{{ $key }}'; applyFilter()"
                        :class="filterCat === '{{ $key }}' ? 'ring-2 ring-purple-500 bg-purple-900/40' : 'bg-gray-800'"
                        class="px-4 py-2 rounded-xl text-sm font-medium text-gray-300 transition-all">
                    {{ $cat['emoji'] }} {{ $cat['label'] }}
                </button>
                @endforeach
            </div>

            <button @click="startGame()"
                    :disabled="filtered.length === 0"
                    class="w-full max-w-xs bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 disabled:opacity-40 text-white py-4 rounded-2xl font-black text-xl transition-all transform hover:scale-105">
                ابدأ! 🚀
            </button>
            <p class="text-xs text-gray-600 mt-3" x-text="`${filtered.length} تحدي`"></p>
        </div>

        <!-- ===== شاشة اللعبة ===== -->
        <div x-show="screen === 'game'" x-cloak class="flex-1 flex flex-col max-w-lg mx-auto w-full px-4 py-6">

            <!-- Header -->
            <div class="flex items-center justify-between mb-5">
                <button @click="resetGame(); screen = 'intro'"
                        class="text-gray-500 hover:text-white text-sm flex items-center gap-1">
                    ← رجوع
                </button>
                <div class="text-center">
                    <span class="text-xs text-gray-500">
                        <span x-text="currentIndex + 1"></span> / <span x-text="totalCards"></span>
                    </span>
                </div>
                <div class="text-sm text-gray-400">
                    ✅ <span x-text="done"></span>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="w-full bg-gray-800 rounded-full h-1.5 mb-6">
                <div class="bg-gradient-to-l from-pink-500 to-purple-500 h-1.5 rounded-full transition-all duration-500"
                     :style="`width: ${totalCards ? (currentIndex / totalCards) * 100 : 0}%`"></div>
            </div>

            <!-- Card -->
            <div class="flex-1 flex flex-col">

                <!-- Card Face -->
                <div x-show="!showCard"
                     @click="flipCard()"
                     class="flex-1 bg-gradient-to-br from-purple-900 to-pink-950 border-2 border-purple-700/40 rounded-3xl flex flex-col items-center justify-center cursor-pointer shadow-2xl transition-all hover:scale-[1.02] active:scale-[0.98] min-h-72 mb-5">
                    <div class="text-7xl mb-4 animate-pulse">🎯</div>
                    <div class="text-xl font-bold text-white/80">اضغط لكشف التحدي</div>
                    <div class="text-gray-500 text-sm mt-2">تحدي <span x-text="currentIndex + 1"></span></div>
                </div>

                <!-- Card Content -->
                <div x-show="showCard" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="flex-1 flex flex-col rounded-3xl overflow-hidden border-2 shadow-2xl min-h-72 mb-5"
                     :style="`border-color: ${currentCard?.category_color}40`">

                    <!-- Image -->
                    <template x-if="currentCard?.image">
                        <div class="relative flex-1 min-h-52">
                            <img :src="currentCard.image"
                                 class="w-full h-full object-cover"
                                 style="max-height: 320px;">
                            <!-- Category badge -->
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold backdrop-blur-sm"
                                      :style="`background-color: ${currentCard.category_color}30; color: ${currentCard.category_color}; border: 1px solid ${currentCard.category_color}50`"
                                      x-text="`${currentCard.category_emoji} ${currentCard.category_label}`"></span>
                            </div>
                        </div>
                    </template>

                    <!-- No image - show big text -->
                    <template x-if="!currentCard?.image">
                        <div class="flex-1 flex items-center justify-center p-8 min-h-52"
                             :style="`background: linear-gradient(135deg, ${currentCard?.category_color}15, ${currentCard?.category_color}05)`">
                            <div class="text-6xl" x-text="currentCard?.category_emoji"></div>
                        </div>
                    </template>

                    <!-- Text content -->
                    <div class="p-5" :style="`background: linear-gradient(to top, #111827, #111827ee)`">
                        <h2 class="text-xl font-black text-white mb-2" x-text="currentCard?.title"></h2>
                        <p x-show="currentCard?.description"
                           class="text-gray-300 text-sm leading-relaxed" x-text="currentCard?.description"></p>

                        <!-- Timer -->
                        <div x-show="currentCard?.timer > 0" class="mt-4">
                            <div x-show="!timerRunning && !timerDone"
                                 class="flex items-center gap-3">
                                <button @click="startTimer()"
                                        class="flex items-center gap-2 px-4 py-2 bg-orange-500/20 border border-orange-500/40 text-orange-300 rounded-xl text-sm font-bold hover:bg-orange-500/30 transition-all">
                                    ⏱️ ابدأ التايمر (<span x-text="currentCard?.timer"></span> ث)
                                </button>
                            </div>
                            <div x-show="timerRunning" class="flex items-center gap-3">
                                <div class="flex-1 bg-gray-800 rounded-full h-3 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-1000"
                                         :class="timerLeft <= 10 ? 'bg-red-500' : 'bg-orange-500'"
                                         :style="`width: ${(timerLeft / currentCard?.timer) * 100}%`"></div>
                                </div>
                                <span class="text-2xl font-black w-10 text-center"
                                      :class="timerLeft <= 10 ? 'text-red-400' : 'text-orange-300'"
                                      x-text="timerLeft"></span>
                            </div>
                            <div x-show="timerDone" class="text-center py-2">
                                <span class="text-green-400 font-bold text-lg">⏰ انتهى الوقت!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div x-show="showCard" x-cloak class="flex gap-3">
                    <button @click="nextCard()"
                            class="flex-1 bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 disabled:opacity-50 text-white py-4 rounded-2xl font-bold text-lg transition-all">
                        <span x-text="deck.length > 0 ? 'التحدي التالي ➡️' : 'إنهاء وعرض النتيجة 🏆'"></span>
                    </button>
                    <button @click="skipCard()"
                            class="px-4 py-4 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-2xl text-sm transition-all">
                        تخطي
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== شاشة النهاية ===== -->
        <div x-show="screen === 'done'" x-cloak class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
            <div class="text-7xl mb-4">🏆</div>
            <h2 class="text-2xl font-black mb-2">أحسنتم!</h2>
            <p class="text-gray-400 mb-2">اتحديتم جميع التحديات</p>
            <div class="text-4xl font-black text-purple-400 my-4" x-text="`${done} ✅`"></div>
            <p class="text-gray-500 text-sm mb-8">تحدي نُفِّذ بنجاح</p>
            <button @click="screen = 'intro'; resetGame()"
                    class="w-full max-w-xs bg-gradient-to-l from-pink-600 to-purple-600 text-white py-4 rounded-2xl font-bold text-lg">
                العب مرة أخرى 🔄
            </button>
        </div>

    </div>

    @push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush

    @push('scripts')
    <script>
        const allCards = @json($cards);

        function challengeGame() {
            return {
                screen: 'intro',
                allCards: allCards,
                filtered: allCards,
                filterCat: null,
                deck: [],
                currentCard: null,
                currentIndex: 0,
                totalCards: 0,
                showCard: false,
                done: 0,
                timerRunning: false,
                timerDone: false,
                timerLeft: 0,
                timerInterval: null,

                init() {
                    this.filtered = [...allCards];
                },

                applyFilter() {
                    this.filtered = this.filterCat
                        ? allCards.filter(c => c.category === this.filterCat)
                        : [...allCards];
                },

                startGame() {
                    this.resetGame();
                    this.deck         = this.shuffle([...this.filtered]);
                    this.totalCards   = this.deck.length;
                    this.currentIndex = 0;
                    this.done         = 0;
                    this.showCard     = false;
                    this.currentCard  = this.deck.shift() || null;
                    this.screen       = 'game';
                },

                flipCard() {
                    if (!this.currentCard) return;
                    this.showCard    = true;
                    this.timerRunning = false;
                    this.timerDone   = false;
                    this.timerLeft   = this.currentCard.timer || 0;
                    clearInterval(this.timerInterval);
                },

                startTimer() {
                    if (this.timerRunning || this.timerLeft <= 0 || !this.showCard) return;
                    this.timerRunning = true;
                    this.timerInterval = setInterval(() => {
                        this.timerLeft--;
                        if (this.timerLeft <= 0) {
                            clearInterval(this.timerInterval);
                            this.timerRunning = false;
                            this.timerDone    = true;
                        }
                    }, 1000);
                },

                nextCard() {
                    if (!this.showCard || this.screen !== 'game') return;
                    clearInterval(this.timerInterval);
                    this.done++;
                    this.advance();
                },

                skipCard() {
                    if (!this.showCard || this.screen !== 'game') return;
                    clearInterval(this.timerInterval);
                    this.advance();
                },

                advance() {
                    if (this.deck.length === 0) {
                        this.screen = 'done';
                        return;
                    }
                    this.currentIndex++;
                    this.showCard    = false;
                    this.timerRunning = false;
                    this.timerDone   = false;
                    this.currentCard = this.deck.shift() || null;
                    this.timerLeft   = this.currentCard?.timer || 0;
                },

                resetGame() {
                    clearInterval(this.timerInterval);
                    this.deck        = [];
                    this.currentCard = null;
                    this.currentIndex = 0;
                    this.showCard    = false;
                    this.done        = 0;
                    this.timerRunning = false;
                    this.timerDone   = false;
                },

                shuffle(arr) {
                    for (let i = arr.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [arr[i], arr[j]] = [arr[j], arr[i]];
                    }
                    return arr;
                }
            }
        }
    </script>
    @endpush

</x-app-layout>
