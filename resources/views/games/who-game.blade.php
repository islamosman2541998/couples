<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>
    <x-game-comfort-note />

    <div class="min-h-screen flex flex-col" x-data="whoGame()">

        <!-- ===== شاشة البداية ===== -->
        <div x-show="screen === 'intro'" class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
            <div class="text-7xl mb-6 animate-bounce">🤔</div>
            <h1 class="text-3xl font-black mb-3">{{ $game->name }}</h1>
            <p class="text-gray-400 mb-10 leading-relaxed">
                سؤال يظهر لكم الاتنين<br>
                كل واحد يختار <span class="text-purple-400 font-bold">أنا</span> أو <span class="text-pink-400 font-bold">شريكي</span> بسرية<br>
                ولما الاتنين يختاروا تنكشف الإجابتين!
            </p>

            <div class="grid grid-cols-2 gap-4 w-full max-w-xs mb-10">
                <div class="bg-blue-900/30 border border-blue-700/40 rounded-2xl p-4 text-center">
                    <div class="text-3xl mb-2">👨</div>
                    <div class="text-sm font-bold text-blue-300">راجل</div>
                </div>
                <div class="bg-pink-900/30 border border-pink-700/40 rounded-2xl p-4 text-center">
                    <div class="text-3xl mb-2">👩</div>
                    <div class="text-sm font-bold text-pink-300">ست</div>
                </div>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 w-full max-w-sm text-sm text-gray-400 mb-8 text-right space-y-2">
                <p>✅ الإجابتين متطابقتين → ❤️ نقطة لكم</p>
                <p>❌ الإجابتين مختلفتين → 😈 تحدي للاثنين</p>
            </div>

            <button @click="screen = 'game'"
                    class="w-full max-w-sm bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-4 rounded-2xl font-black text-xl transition-all transform hover:scale-105">
                ابدأ اللعبة! 🚀
            </button>
        </div>

        <!-- ===== شاشة اللعبة ===== -->
        <div x-show="screen === 'game'" x-cloak class="flex-1 flex flex-col max-w-lg mx-auto w-full px-4 py-6">

            <!-- Progress & Score -->
            <div class="flex items-center justify-between mb-6">
                <div class="text-sm text-gray-400">
                    سؤال <span class="font-bold text-white" x-text="currentIndex + 1"></span> / <span x-text="questions.length"></span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-3 py-1 bg-green-900/40 border border-green-700/40 text-green-400 rounded-full">
                        ❤️ <span x-text="score"></span> نقطة
                    </span>
                    <span class="text-xs px-3 py-1 bg-red-900/40 border border-red-700/40 text-red-400 rounded-full">
                        😈 <span x-text="challenges"></span> تحدي
                    </span>
                </div>
            </div>

            <!-- Question Card -->
            <button @click="nextQuestion()" class="mb-4 text-sm text-gray-400 hover:text-white">تخطي السؤال</button>
            <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 mb-6 min-h-36 flex flex-col items-center justify-center text-center">
                <div class="text-3xl mb-3" x-text="currentQuestion?.category_emoji"></div>
                <p class="text-xl font-black text-white leading-relaxed" x-text="currentQuestion?.question"></p>
                <span class="mt-3 text-xs px-3 py-1 rounded-full font-medium"
                      :style="`background-color: ${currentQuestion?.category_color}20; color: ${currentQuestion?.category_color}`"
                      x-text="currentQuestion?.category_label"></span>
            </div>

            <!-- Step 1: راجل يختار -->
            <div x-show="step === 1">
                <div class="text-center mb-5">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-900/30 border border-blue-700/40 rounded-full text-blue-300 text-sm font-bold">
                        👨 دور الراجل — اختر سراً
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button @click="chooseMale('me')"
                            class="bg-blue-900/20 hover:bg-blue-900/50 border-2 border-blue-700/40 hover:border-blue-500 rounded-2xl py-8 text-center transition-all transform hover:scale-105 active:scale-95">
                        <div class="text-4xl mb-2">🙋</div>
                        <div class="text-lg font-black text-blue-300">أنا</div>
                    </button>
                    <button @click="chooseMale('partner')"
                            class="bg-pink-900/20 hover:bg-pink-900/50 border-2 border-pink-700/40 hover:border-pink-500 rounded-2xl py-8 text-center transition-all transform hover:scale-105 active:scale-95">
                        <div class="text-4xl mb-2">💑</div>
                        <div class="text-lg font-black text-pink-300">شريكتي</div>
                    </button>
                </div>
                <p class="text-center text-xs text-gray-600 mt-4">الست تبعد عن الشاشة دلوقتي 👀</p>
            </div>

            <!-- Step 2: ست تختار -->
            <div x-show="step === 2" x-cloak>
                <div class="text-center mb-5">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-pink-900/30 border border-pink-700/40 rounded-full text-pink-300 text-sm font-bold">
                        👩 دور الست — اختري سراً
                    </div>
                </div>
                <!-- Cover screen for male -->
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 mb-4 text-center text-sm text-gray-500">
                    👨 الراجل اختار... الإجابة مخفية لحد ما الست تختار
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button @click="chooseFemale('me')"
                            class="bg-pink-900/20 hover:bg-pink-900/50 border-2 border-pink-700/40 hover:border-pink-500 rounded-2xl py-8 text-center transition-all transform hover:scale-105 active:scale-95">
                        <div class="text-4xl mb-2">🙋</div>
                        <div class="text-lg font-black text-pink-300">أنا</div>
                    </button>
                    <button @click="chooseFemale('partner')"
                            class="bg-blue-900/20 hover:bg-blue-900/50 border-2 border-blue-700/40 hover:border-blue-500 rounded-2xl py-8 text-center transition-all transform hover:scale-105 active:scale-95">
                        <div class="text-4xl mb-2">💑</div>
                        <div class="text-lg font-black text-blue-300">شريكي</div>
                    </button>
                </div>
                <p class="text-center text-xs text-gray-600 mt-4">الراجل ما يبصش دلوقتي 🙈</p>
            </div>

            <!-- Step 3: Reveal -->
            <div x-show="step === 3" x-cloak>
                <div class="text-center mb-4 text-2xl font-black animate-pulse">
                    3... 2... 1... 🥁
                </div>

                <!-- Countdown then Reveal -->
                <div x-show="!revealed">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-900/20 border-2 border-blue-700/40 rounded-2xl py-8 text-center">
                            <div class="text-3xl mb-1">👨</div>
                            <div class="text-2xl font-black text-blue-300">???</div>
                        </div>
                        <div class="bg-pink-900/20 border-2 border-pink-700/40 rounded-2xl py-8 text-center">
                            <div class="text-3xl mb-1">👩</div>
                            <div class="text-2xl font-black text-pink-300">???</div>
                        </div>
                    </div>
                    <button @click="reveal()"
                            class="w-full mt-4 bg-gradient-to-l from-yellow-500 to-orange-500 hover:from-yellow-400 hover:to-orange-400 text-white py-4 rounded-2xl font-black text-lg transition-all transform hover:scale-105 animate-pulse">
                        اكشف الإجابتين! 🎭
                    </button>
                </div>

                <!-- Revealed -->
                <div x-show="revealed" x-cloak>
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div :class="maleChoice === 'me'
                                ? 'bg-blue-900/40 border-blue-500'
                                : 'bg-pink-900/20 border-pink-700/40'"
                             class="border-2 rounded-2xl py-6 text-center transition-all">
                            <div class="text-3xl mb-2">👨</div>
                            <div class="text-sm text-gray-400 mb-1">الراجل</div>
                            <div class="text-xl font-black" :class="maleChoice === 'me' ? 'text-blue-300' : 'text-pink-300'"
                                 x-text="maleChoice === 'me' ? '🙋 أنا' : '💑 شريكتي'"></div>
                        </div>
                        <div :class="femaleChoice === 'me'
                                ? 'bg-pink-900/40 border-pink-500'
                                : 'bg-blue-900/20 border-blue-700/40'"
                             class="border-2 rounded-2xl py-6 text-center transition-all">
                            <div class="text-3xl mb-2">👩</div>
                            <div class="text-sm text-gray-400 mb-1">الست</div>
                            <div class="text-xl font-black" :class="femaleChoice === 'me' ? 'text-pink-300' : 'text-blue-300'"
                                 x-text="femaleChoice === 'me' ? '🙋 أنا' : '💑 شريكي'"></div>
                        </div>
                    </div>

                    <!-- Result -->
                    <div x-show="isMatch"
                         class="bg-green-900/30 border-2 border-green-600/50 rounded-2xl p-5 text-center mb-5">
                        <div class="text-4xl mb-2">🎉</div>
                        <div class="text-xl font-black text-green-400">إجابتكم متطابقة!</div>
                        <div class="text-sm text-green-300 mt-1">❤️ نقطة لكم معاً</div>
                    </div>

                    <div x-show="!isMatch"
                         class="bg-red-900/20 border-2 border-red-700/40 rounded-2xl p-5 text-center mb-5">
                        <div class="text-4xl mb-2">😈</div>
                        <div class="text-xl font-black text-red-400">إجابتكم مختلفة!</div>
                        <template x-if="currentQuestion?.challenge">
                            <div class="mt-3 bg-gray-900/60 rounded-xl p-3">
                                <div class="text-xs text-gray-400 mb-1">التحدي:</div>
                                <div class="text-sm text-white font-medium" x-text="currentQuestion.challenge"></div>
                            </div>
                        </template>
                        <template x-if="!currentQuestion?.challenge">
                            <div class="text-sm text-gray-400 mt-2">كل واحد يشرح إجابته للثاني 😏</div>
                        </template>
                    </div>

                    <button @click="nextQuestion()"
                            class="w-full bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-4 rounded-2xl font-bold text-lg transition-all">
                        <span x-text="currentIndex + 1 < questions.length ? 'السؤال التالي ➡️' : '🏁 النتيجة النهائية'"></span>
                    </button>
                </div>
            </div>

        </div>

        <!-- ===== شاشة النتيجة ===== -->
        <div x-show="screen === 'result'" x-cloak class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
            <div class="text-7xl mb-4" x-text="score >= Math.floor(questions.length * 0.7) ? '🏆' : score >= Math.floor(questions.length * 0.4) ? '❤️' : '🤗'"></div>
            <h2 class="text-2xl font-black mb-2">انتهت اللعبة!</h2>

            <div class="grid grid-cols-2 gap-4 w-full max-w-xs my-6">
                <div class="bg-green-900/30 border border-green-700/40 rounded-2xl p-4">
                    <div class="text-3xl font-black text-green-400" x-text="score"></div>
                    <div class="text-xs text-gray-400 mt-1">إجابة متطابقة ❤️</div>
                </div>
                <div class="bg-red-900/30 border border-red-700/40 rounded-2xl p-4">
                    <div class="text-3xl font-black text-red-400" x-text="challenges"></div>
                    <div class="text-xs text-gray-400 mt-1">تحدي 😈</div>
                </div>
            </div>

            <p class="text-gray-300 mb-8 max-w-xs"
               x-text="score >= Math.floor(questions.length * 0.7)
                   ? 'ماشاءالله! متفاهمين زي الفل 🌟'
                   : score >= Math.floor(questions.length * 0.4)
                   ? 'كويس! بس في كتير تاني تتعرفوه عن بعض 😊'
                   : 'في كتير تاني تتعرفوه عن بعض! العبوا تاني 😄'">
            </p>

            <button @click="resetGame()"
                    class="w-full max-w-xs bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-4 rounded-2xl font-bold text-lg transition-all">
                العب مرة أخرى 🔄
            </button>
        </div>

    </div>

    @push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush

    @push('scripts')
    <script>
        const allQuestions = @json($questions);

        function whoGame() {
            return {
                screen: 'intro',
                questions: [],
                currentIndex: 0,
                step: 1,          // 1=male picks, 2=female picks, 3=reveal
                maleChoice: null,
                femaleChoice: null,
                revealed: false,
                isMatch: false,
                score: 0,
                challenges: 0,

                get currentQuestion() {
                    return this.questions[this.currentIndex] ?? null;
                },

                init() {
                    this.questions = this.shuffle([...allQuestions]);
                },

                chooseMale(choice) {
                    this.maleChoice = choice;
                    this.step = 2;
                },

                chooseFemale(choice) {
                    this.femaleChoice = choice;
                    this.step = 3;
                    this.revealed = false;
                },

                reveal() {
                    if (this.revealed || this.step !== 3) return;
                    this.revealed = true;
                    // Each player's "me" refers to a different person.
                    this.isMatch = this.maleChoice !== this.femaleChoice;
                    if (this.isMatch) {
                        this.score++;
                    } else {
                        this.challenges++;
                    }
                },

                nextQuestion() {
                    if (this.currentIndex + 1 >= this.questions.length) {
                        this.screen = 'result';
                        return;
                    }
                    this.currentIndex++;
                    this.step = 1;
                    this.maleChoice = null;
                    this.femaleChoice = null;
                    this.revealed = false;
                },

                resetGame() {
                    this.questions = this.shuffle([...allQuestions]);
                    this.currentIndex = 0;
                    this.step = 1;
                    this.maleChoice = null;
                    this.femaleChoice = null;
                    this.revealed = false;
                    this.isMatch = false;
                    this.score = 0;
                    this.challenges = 0;
                    this.screen = 'game';
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
