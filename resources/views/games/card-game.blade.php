<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>
    <x-game-comfort-note />

    <div class="min-h-screen" x-data="cardGame()">

        <!-- Level Selection Screen -->
        <div x-show="screen === 'levels'" class="max-w-2xl mx-auto px-4 py-12">
            <div class="text-center mb-10">
                <div class="text-6xl mb-4">🃏</div>
                <h1 class="text-3xl font-black mb-2">{{ $game->name }}</h1>
                <p class="text-gray-400">اختاروا جوّكم الليلة وابدأوا</p>
            </div>

            <div class="grid gap-4">
                @foreach($levels as $level)
                    <button @click="selectLevel(@js($level->slug), @js($level->name), @js($level->color))" :disabled="loading || {{ $level->cards_count }} === 0"
                            class="group relative bg-gray-900 border border-gray-800 hover:border-purple-500/50 rounded-2xl p-6 text-right transition-all transform hover:scale-105 hover:-translate-y-1">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black text-white"
                                 style="background-color: {{ $level->color }}20; border: 2px solid {{ $level->color }}40">
                                {{ match($level->slug) { 'easy' => '✨', 'medium' => '💕', 'hard' => '🔥', default => '🃏' } }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-black" style="color: {{ $level->color }}">{{ $level->name }}</h3>
                                <p class="text-gray-500 text-sm mt-1">
                                    {{ $level->cards_count }} كارت متاح
                                    @if($level->slug === 'easy') · غزل وذكريات وبداية لطيفة
                                    @elseif($level->slug === 'medium') · مفاجآت ولحظات تقارب
                                    @else · اعترافات رومانسية وجرأة في التعبير
                                    @endif
                                </p>
                            </div>
                            <svg class="w-6 h-6 text-gray-600 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="mt-8 bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <h3 class="font-bold mb-3 text-sm text-gray-400">طريقة اللعب</h3>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li>• اختاروا مستوى يناسب مزاجكم</li>
                    <li>• يتناوب اللاعبان على سحب الكروت</li>
                    <li>• اضغط على الكارت لتقلبه</li>
                    <li>• نفّذ التحدي المكتوب عليه</li>
                </ul>
            </div>
        </div>

        <!-- Game Screen -->
        <div x-show="screen === 'game'" x-cloak class="max-w-2xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <button @click="screen = 'levels'; resetGame()"
                        class="text-gray-400 hover:text-white flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    تغيير المستوى
                </button>

                <div class="text-center">
                    <div class="text-sm font-bold" :style="`color: ${levelColor}`" x-text="levelName"></div>
                    <div class="text-xs text-gray-500" x-text="`${cards.length} كارت متبقي`"></div>
                </div>

                <div class="text-sm text-gray-500">
                    كارت <span x-text="cardIndex + 1"></span> / <span x-text="totalCards"></span>
                </div>
            </div>

            <!-- Current Player -->
            <div class="flex justify-center gap-4 mb-8">
                <div :class="currentPlayer === 'male' ? 'ring-2 ring-blue-500 bg-blue-900/30' : 'bg-gray-800/50 opacity-50'"
                     class="flex-1 max-w-36 text-center py-3 px-4 rounded-2xl transition-all">
                    <div class="text-2xl">👨</div>
                    <div class="text-sm font-bold mt-1">راجل</div>
                </div>
                <div class="flex items-center text-gray-600 text-xl">⚡</div>
                <div :class="currentPlayer === 'female' ? 'ring-2 ring-pink-500 bg-pink-900/30' : 'bg-gray-800/50 opacity-50'"
                     class="flex-1 max-w-36 text-center py-3 px-4 rounded-2xl transition-all">
                    <div class="text-2xl">👩</div>
                    <div class="text-sm font-bold mt-1">ست</div>
                </div>
            </div>

            <!-- Turn Indicator -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium"
                     :class="currentPlayer === 'male'
                         ? 'bg-blue-900/30 text-blue-300 border border-blue-700/50'
                         : 'bg-pink-900/30 text-pink-300 border border-pink-700/50'">
                    <span x-text="currentPlayer === 'male' ? '👨' : '👩'"></span>
                    <span>دور <span x-text="currentPlayer === 'male' ? 'الراجل' : 'الست'"></span></span>
                </div>
            </div>

            <!-- Card -->
            <div class="relative h-72 mb-8 cursor-pointer" @click="flipCard()">

                    <!-- Front -->
                    <div x-show="!isFlipped"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-90"
                         class="absolute inset-0 bg-gradient-to-br from-purple-900 to-pink-900 rounded-3xl border-2 border-purple-700/50 flex flex-col items-center justify-center shadow-2xl">
                        <div class="text-6xl mb-4">🃏</div>
                        <div class="text-xl font-bold text-white/80">اضغط للكشف</div>
                        <div class="text-gray-400 text-sm mt-2">دور <span x-text="currentPlayer === 'male' ? 'الراجل' : 'الست'"></span></div>
                        <div class="absolute top-4 right-4 w-8 h-8 border-t-2 border-r-2 border-purple-500/30 rounded-tr-lg"></div>
                        <div class="absolute bottom-4 left-4 w-8 h-8 border-b-2 border-l-2 border-purple-500/30 rounded-bl-lg"></div>
                    </div>

                    <!-- Back -->
                    <div x-show="isFlipped"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute inset-0 rounded-3xl border-2 flex flex-col items-center justify-center p-8 shadow-2xl"
                         :class="currentCard?.target === 'male'
                             ? 'bg-gradient-to-br from-blue-900 to-blue-950 border-blue-600/50'
                             : currentCard?.target === 'female'
                             ? 'bg-gradient-to-br from-pink-900 to-pink-950 border-pink-600/50'
                             : 'bg-gradient-to-br from-purple-900 to-indigo-950 border-purple-600/50'">
                        <div class="text-4xl mb-6" x-text="currentCard?.target === 'male' ? '👨' : currentCard?.target === 'female' ? '👩' : '👫'"></div>
                        <div class="text-2xl text-center text-white leading-relaxed font-bold" x-text="currentCard?.content || ''"></div>
                        <div class="absolute top-4 right-4 w-8 h-8 border-t-2 border-r-2 border-white/10 rounded-tr-lg"></div>
                        <div class="absolute bottom-4 left-4 w-8 h-8 border-b-2 border-l-2 border-white/10 rounded-bl-lg"></div>
                    </div>
            </div>

            <!-- Actions -->
            <button @click="skipCard()" class="block mx-auto mb-4 text-sm text-gray-400 hover:text-white">تخطي الكارت</button>
            <div x-show="!isFlipped" class="text-center text-gray-500 text-sm mb-4">
                اضغط على الكارت لتقلبه
            </div>

            <div x-show="isFlipped" x-cloak class="flex gap-4">
                <button @click="nextCard()"
                        :disabled="cards.length === 0"
                        class="flex-1 bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white py-4 rounded-2xl font-bold text-lg transition-all">
                    <span x-text="cards.length > 0 ? 'الكارت التالي ➡️' : '🎉 انتهت الكروت!'"></span>
                </button>
            </div>

            <!-- Empty State -->
            <div x-show="cards.length === 0 && isFlipped" x-cloak class="text-center mt-6">
                <p class="text-gray-400 text-sm mb-4">تم الانتهاء من جميع كروت هذا المستوى</p>
                <button @click="screen = 'levels'; resetGame()"
                        class="text-purple-400 hover:text-purple-300 text-sm underline">
                    اختر مستوى آخر
                </button>
            </div>
        </div>

    </div>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    @push('scripts')
    <script>
        function cardGame() {
            return {
                screen: 'levels',
                currentPlayer: 'male',
                levelSlug: null,
                levelName: null,
                levelColor: null,
                cards: [],
                currentCard: null,
                cardIndex: 0,
                totalCards: 0,
                isFlipped: false,
                loading: false,

                init() {},

                async selectLevel(slug, name, color) {
                    if (this.loading) return;
                    this.levelSlug  = slug;
                    this.levelName  = name;
                    this.levelColor = color;
                    this.loading    = true;

                    try {
                        const res = await fetch(`{{ url('/api/cards') }}/${encodeURIComponent(slug)}?target=all&game_id={{ $game->id }}`, { headers: { Accept: 'application/json' } });
                        if (!res.ok) throw new Error('Unable to load cards');
                        const data = await res.json();
                        this.cards      = this.shuffle(data);
                        this.totalCards = this.cards.length;
                        this.cardIndex  = 0;
                        this.currentCard = this.cards.shift() || null;
                        this.currentPlayer = this.currentCard?.target === 'female' ? 'female' : 'male';
                        this.isFlipped   = false;
                        this.screen      = 'game';
                    } catch(e) {
                        alert('حدث خطأ في تحميل الكروت');
                    }

                    this.loading = false;
                },

                flipCard() {
                    if (!this.currentCard) return;
                    this.isFlipped = true;
                },

                nextCard() {
                    if (this.cards.length === 0 || !this.isFlipped) return;

                    // Switch player
                    this.currentPlayer = this.currentPlayer === 'male' ? 'female' : 'male';
                    this.cardIndex++;
                    this.isFlipped   = false;

                    this.currentCard = this.cards.shift() || null;
                    if (['male', 'female'].includes(this.currentCard?.target)) {
                        this.currentPlayer = this.currentCard.target;
                    }
                },

                resetGame() {
                    this.cards       = [];
                    this.currentCard = null;
                    this.cardIndex   = 0;
                    this.totalCards  = 0;
                    this.isFlipped   = false;
                    this.currentPlayer = 'male';
                },

                skipCard() {
                    if (!this.cards.length) {
                        this.resetGame();
                        this.screen = 'levels';
                        return;
                    }
                    this.isFlipped = true;
                    this.nextCard();
                },

                shuffle(arr) {
                    const a = [...arr];
                    for (let i = a.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [a[i], a[j]] = [a[j], a[i]];
                    }
                    return a;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
