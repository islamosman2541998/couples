<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>
    <x-game-comfort-note />

    <div class="min-h-screen flex flex-col" x-data="knowMeGame()">

        <!-- ===== شاشة البداية ===== -->
        <div x-show="screen === 'intro'" class="flex-1 flex flex-col items-center justify-center px-6 py-10 text-center">
            <div class="text-7xl mb-5">💍</div>
            <h1 class="text-3xl font-black mb-2">{{ $game->name }}</h1>
            <p class="text-gray-400 mb-8 max-w-sm leading-relaxed">
                لعبة تساعدكم تتعرفوا على بعض أكتر<br>
                سؤال عن واحد منكم — التاني يتخمن الإجابة
            </p>

            <!-- How to play -->
            <div class="w-full max-w-sm bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-8 text-right space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-purple-700/40 rounded-full flex items-center justify-center text-purple-300 font-black text-xs flex-shrink-0">1</span>
                    <span class="text-gray-300">سؤال يظهر عن <span class="text-blue-400 font-bold">الراجل</span> أو <span class="text-pink-400 font-bold">الست</span></span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-purple-700/40 rounded-full flex items-center justify-center text-purple-300 font-black text-xs flex-shrink-0">2</span>
                    <span class="text-gray-300">المعني يكتب إجابته <strong>سراً</strong></span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-purple-700/40 rounded-full flex items-center justify-center text-purple-300 font-black text-xs flex-shrink-0">3</span>
                    <span class="text-gray-300">التاني يتخمن الإجابة</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-green-700/40 rounded-full flex items-center justify-center text-green-300 font-black text-xs flex-shrink-0">✓</span>
                    <span class="text-gray-300">الكشف والنقاش مع بعض ❤️</span>
                </div>
            </div>

            <!-- Category filter -->
            <div class="flex flex-wrap justify-center gap-2 mb-8 w-full max-w-sm">
                <button @click="toggleCat(null)"
                        :class="selectedCats.length === 0 ? 'bg-purple-700 text-white' : 'bg-gray-800 text-gray-400'"
                        class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all">
                    الكل
                </button>
                @foreach(\App\Models\KnowMeQuestion::$categories as $key => $cat)
                <button @click="toggleCat('{{ $key }}')"
                        :class="selectedCats.includes('{{ $key }}') ? 'text-white ring-2 ring-white/20' : 'bg-gray-800 text-gray-400'"
                        class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all"
                        :style="selectedCats.includes('{{ $key }}') ? 'background-color: {{ $cat['color'] }}' : ''">
                    {{ $cat['emoji'] }} {{ $cat['label'] }}
                </button>
                @endforeach
            </div>

            <!-- Who starts -->
            <div class="w-full max-w-sm mb-6">
                <p class="text-sm text-gray-400 mb-3 text-center">من يبدأ أولاً؟</p>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="startingPlayer = 'male'"
                            :class="startingPlayer === 'male' ? 'ring-2 ring-blue-500 bg-blue-900/40' : 'bg-gray-800'"
                            class="py-3 rounded-2xl text-center transition-all">
                        <div class="text-2xl">👨</div>
                        <div class="text-sm font-bold text-blue-300 mt-1">الراجل</div>
                    </button>
                    <button @click="startingPlayer = 'female'"
                            :class="startingPlayer === 'female' ? 'ring-2 ring-pink-500 bg-pink-900/40' : 'bg-gray-800'"
                            class="py-3 rounded-2xl text-center transition-all">
                        <div class="text-2xl">👩</div>
                        <div class="text-sm font-bold text-pink-300 mt-1">الست</div>
                    </button>
                </div>
            </div>

            <button @click="startGame()"
                    :disabled="filteredQuestions.length === 0"
                    class="w-full max-w-sm bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 disabled:opacity-40 text-white py-4 rounded-2xl font-black text-xl transition-all transform hover:scale-105">
                ابدأ اللعبة! 💍
            </button>
            <p class="text-xs text-gray-600 mt-3" x-text="`${filteredQuestions.length} سؤال`"></p>
        </div>

        <!-- ===== شاشة اللعبة ===== -->
        <div x-show="screen === 'game'" x-cloak class="flex-1 flex flex-col max-w-lg mx-auto w-full px-4 py-6">
            <button @click="nextQuestion()" class="mb-4 text-sm text-gray-400 hover:text-white">تخطي السؤال</button>

            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <button @click="screen = 'intro'" class="text-gray-500 hover:text-white text-sm">← رجوع</button>
                <div class="text-xs text-gray-500">
                    <span x-text="currentIndex + 1"></span> / <span x-text="deck.length + currentIndex + (currentQ ? 1 : 0)"></span>
                </div>
                <div class="flex gap-2 text-xs">
                    <span class="px-2 py-1 bg-green-900/30 text-green-400 rounded-full">✅ <span x-text="score.correct"></span></span>
                    <span class="px-2 py-1 bg-red-900/30 text-red-400 rounded-full">❌ <span x-text="score.wrong"></span></span>
                </div>
            </div>

            <!-- About whom -->
            <div class="flex justify-center mb-5">
                <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold"
                     :class="currentQ?.about === 'male'
                         ? 'bg-blue-900/40 border border-blue-700/50 text-blue-300'
                         : 'bg-pink-900/40 border border-pink-700/50 text-pink-300'">
                    <span x-text="currentQ?.about === 'male' ? '👨' : '👩'"></span>
                    <span>سؤال عن <span x-text="currentQ?.about === 'male' ? 'الراجل' : 'الست'"></span></span>
                </div>
            </div>

            <!-- Question Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 mb-5 min-h-28 flex flex-col items-center justify-center text-center">
                <div class="text-2xl mb-3" x-text="currentQ?.category_emoji"></div>
                <p class="text-xl font-black text-white leading-relaxed" x-text="currentQ?.question"></p>
                <div x-show="currentQ?.hint" class="mt-3 text-xs text-gray-500 bg-gray-800/60 px-3 py-1.5 rounded-full">
                    💡 <span x-text="currentQ?.hint"></span>
                </div>
                <span class="mt-3 text-xs px-3 py-1 rounded-full"
                      :style="`background-color: ${currentQ?.category_color}20; color: ${currentQ?.category_color}`"
                      x-text="currentQ?.category_label"></span>
            </div>

            <!-- Step 1: Subject writes their answer secretly -->
            <div x-show="step === 1">
                <div class="text-center mb-4">
                    <p class="text-sm text-gray-400">
                        <span :class="currentQ?.about === 'male' ? 'text-blue-400' : 'text-pink-400'" class="font-bold"
                              x-text="currentQ?.about === 'male' ? '👨 الراجل' : '👩 الست'"></span>
                        يكتب إجابته سراً 🤫
                    </p>
                </div>

                <!-- Open answer -->
                <template x-if="currentQ?.answer_type === 'open'">
                    <div class="space-y-3">
                        <textarea x-model="subjectAnswer" rows="3" placeholder="اكتب إجابتك هنا..."
                                  class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 resize-none text-center text-lg"></textarea>
                        <button @click="step = 2" :disabled="!subjectAnswer.trim()"
                                class="w-full bg-blue-700 hover:bg-blue-600 disabled:opacity-40 text-white py-4 rounded-2xl font-bold transition-all">
                            تم — <span x-text="currentQ?.about === 'male' ? 'الست' : 'الراجل'"></span> تخمّن دلوقتي
                        </button>
                    </div>
                </template>

                <!-- Choice answer -->
                <template x-if="currentQ?.answer_type === 'choice'">
                    <div class="space-y-3">
                        <template x-for="choice in currentQ.choices" :key="choice">
                            <button @click="subjectAnswer = choice; step = 2"
                                    class="w-full bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-purple-500 rounded-2xl py-4 px-5 text-right font-medium text-gray-200 transition-all"
                                    x-text="choice"></button>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Step 2: Other person guesses -->
            <div x-show="step === 2" x-cloak>
                <div class="text-center mb-4">
                    <div class="bg-gray-800 border border-gray-700 rounded-xl p-3 mb-3 text-sm text-gray-500">
                        <span :class="currentQ?.about === 'male' ? 'text-blue-400' : 'text-pink-400'" class="font-bold"
                              x-text="currentQ?.about === 'male' ? '👨 الراجل' : '👩 الست'"></span>
                        كتب إجابته... الإجابة مخفية 🙈
                    </div>
                    <p class="text-sm text-gray-400">
                        <span :class="currentQ?.about === 'male' ? 'text-pink-400' : 'text-blue-400'" class="font-bold"
                              x-text="currentQ?.about === 'male' ? '👩 الست' : '👨 الراجل'"></span>
                        تتخمن الإجابة دلوقتي
                    </p>
                </div>

                <!-- Open guess -->
                <template x-if="currentQ?.answer_type === 'open'">
                    <div class="space-y-3">
                        <textarea x-model="guessAnswer" rows="3" placeholder="اكتبي تخمينك هنا..."
                                  class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-pink-500 resize-none text-center text-lg"></textarea>
                        <button @click="showReveal()" :disabled="!guessAnswer.trim()"
                                class="w-full bg-pink-700 hover:bg-pink-600 disabled:opacity-40 text-white py-4 rounded-2xl font-bold transition-all">
                            اكشف الإجابة! 🎭
                        </button>
                    </div>
                </template>

                <!-- Choice guess -->
                <template x-if="currentQ?.answer_type === 'choice'">
                    <div class="space-y-3">
                        <template x-for="choice in currentQ.choices" :key="choice">
                            <button @click="guessAnswer = choice; showReveal()"
                                    class="w-full bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-pink-500 rounded-2xl py-4 px-5 text-right font-medium text-gray-200 transition-all"
                                    x-text="choice"></button>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Step 3: Reveal -->
            <div x-show="step === 3" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <!-- Answers comparison -->
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 text-center">
                        <div class="text-2xl mb-1" x-text="currentQ?.about === 'male' ? '👨' : '👩'"></div>
                        <div class="text-xs text-gray-500 mb-2">الإجابة الحقيقية</div>
                        <div class="text-sm font-bold text-white bg-gray-900/60 rounded-xl px-3 py-2" x-text="subjectAnswer"></div>
                    </div>
                    <div class="border-2 rounded-2xl p-4 text-center"
                         :class="isCorrect ? 'bg-green-900/20 border-green-600/50' : 'bg-red-900/20 border-red-700/40'">
                        <div class="text-2xl mb-1" x-text="currentQ?.about === 'male' ? '👩' : '👨'"></div>
                        <div class="text-xs text-gray-500 mb-2">التخمين</div>
                        <div class="text-sm font-bold bg-gray-900/60 rounded-xl px-3 py-2"
                             :class="isCorrect ? 'text-green-300' : 'text-red-300'"
                             x-text="guessAnswer"></div>
                    </div>
                </div>

                <!-- Result -->
                <div x-show="isCorrect"
                     class="bg-green-900/30 border-2 border-green-600/50 rounded-2xl p-4 text-center mb-4">
                    <div class="text-3xl mb-1">🎉</div>
                    <div class="font-black text-green-400 text-lg">صح! عارفاه كويس ❤️</div>
                </div>
                <div x-show="!isCorrect"
                     class="bg-gray-900 border border-gray-700 rounded-2xl p-4 mb-4">
                    <div class="text-center mb-2">
                        <span class="text-orange-400 font-bold">🤔 إجابة مختلفة — ناقشوا مع بعض!</span>
                    </div>
                    <p class="text-xs text-gray-500 text-center">هذه فرصة تعرفوا رأي بعض أكتر</p>
                </div>

                <button @click="nextQuestion()"
                        class="w-full bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-4 rounded-2xl font-bold text-lg transition-all">
                    <span x-text="deck.length > 0 ? 'السؤال التالي ➡️' : '🏁 النتيجة النهائية'"></span>
                </button>
            </div>

        </div>

        <!-- ===== شاشة النتيجة ===== -->
        <div x-show="screen === 'result'" x-cloak class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
            <div class="text-7xl mb-4"
                 x-text="score.correct >= Math.ceil(totalQ * 0.7) ? '💑' : score.correct >= Math.ceil(totalQ * 0.4) ? '❤️' : '🌱'"></div>
            <h2 class="text-2xl font-black mb-2">انتهت الجلسة!</h2>

            <div class="grid grid-cols-2 gap-4 w-full max-w-xs my-6">
                <div class="bg-green-900/30 border border-green-700/40 rounded-2xl p-4">
                    <div class="text-3xl font-black text-green-400" x-text="score.correct"></div>
                    <div class="text-xs text-gray-400 mt-1">إجابة صحيحة ✅</div>
                </div>
                <div class="bg-orange-900/30 border border-orange-700/40 rounded-2xl p-4">
                    <div class="text-3xl font-black text-orange-400" x-text="score.wrong"></div>
                    <div class="text-xs text-gray-400 mt-1">للنقاش 🤔</div>
                </div>
            </div>

            <!-- Compatibility meter -->
            <div class="w-full max-w-xs mb-6">
                <div class="flex justify-between text-xs text-gray-500 mb-2">
                    <span>مستوى التعارف</span>
                    <span x-text="`${Math.round((score.correct / totalQ) * 100)}%`"></span>
                </div>
                <div class="bg-gray-800 rounded-full h-4 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-l from-pink-500 to-purple-500 transition-all duration-1000"
                         :style="`width: ${(score.correct / totalQ) * 100}%`"></div>
                </div>
            </div>

            <p class="text-gray-300 mb-8 max-w-xs text-sm leading-relaxed"
               x-text="score.correct >= Math.ceil(totalQ * 0.7)
                   ? 'ماشاءالله! تعرفوا على بعض كويس جداً 💑'
                   : score.correct >= Math.ceil(totalQ * 0.4)
                   ? 'كويس! في كتير مواضيع تحتاج تتكلموا فيها أكتر 😊'
                   : 'جلسة التعارف دي مهمة جداً، اتكلموا أكتر عن بعض 🌱'">
            </p>

            <div class="flex flex-col gap-3 w-full max-w-xs">
                <button @click="resetGame()"
                        class="w-full bg-gradient-to-l from-pink-600 to-purple-600 text-white py-4 rounded-2xl font-bold text-lg">
                    جلسة جديدة 🔄
                </button>
                <button @click="screen = 'intro'"
                        class="w-full bg-gray-800 hover:bg-gray-700 text-gray-300 py-3 rounded-2xl text-sm">
                    تغيير التصنيفات
                </button>
            </div>
        </div>

    </div>

    @push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush

    @push('scripts')
    <script>
        const allQuestions = @json($questions);

        function knowMeGame() {
            return {
                screen: 'intro',
                selectedCats: [],
                startingPlayer: 'male',
                deck: [],
                currentQ: null,
                currentIndex: 0,
                totalQ: 0,
                step: 1,           // 1=subject writes, 2=other guesses, 3=reveal
                subjectAnswer: '',
                guessAnswer: '',
                isCorrect: false,
                score: { correct: 0, wrong: 0 },
                currentAbout: 'male',

                get filteredQuestions() {
                    if (this.selectedCats.length === 0) return allQuestions;
                    return allQuestions.filter(q => this.selectedCats.includes(q.category));
                },

                init() {},

                toggleCat(cat) {
                    if (cat === null) { this.selectedCats = []; return; }
                    const i = this.selectedCats.indexOf(cat);
                    if (i >= 0) this.selectedCats.splice(i, 1);
                    else this.selectedCats.push(cat);
                },

                startGame() {
                    const qs     = this.shuffle([...this.filteredQuestions]);
                    this.totalQ  = qs.length;
                    // Assign "about" alternating, starting with chosen player
                    this.deck    = qs.map((q, i) => ({
                        ...q,
                        about: (i % 2 === 0) ? this.startingPlayer : (this.startingPlayer === 'male' ? 'female' : 'male'),
                    }));
                    this.currentIndex = 0;
                    this.score        = { correct: 0, wrong: 0 };
                    this.currentQ     = this.deck.shift() || null;
                    this.step         = 1;
                    this.subjectAnswer = '';
                    this.guessAnswer   = '';
                    this.screen        = 'game';
                },

                showReveal() {
                    if (this.step !== 2 || !this.subjectAnswer.trim() || !this.guessAnswer.trim()) return;
                    this.isCorrect = this.subjectAnswer.trim().toLowerCase() === this.guessAnswer.trim().toLowerCase();
                    if (this.isCorrect) this.score.correct++;
                    else this.score.wrong++;
                    this.step = 3;
                },

                nextQuestion() {
                    if (this.deck.length === 0) {
                        this.screen = 'result';
                        return;
                    }
                    this.currentIndex++;
                    this.step          = 1;
                    this.subjectAnswer = '';
                    this.guessAnswer   = '';
                    this.currentQ      = this.deck.shift() || null;
                },

                resetGame() {
                    this.deck          = [];
                    this.currentQ      = null;
                    this.currentIndex  = 0;
                    this.step          = 1;
                    this.subjectAnswer = '';
                    this.guessAnswer   = '';
                    this.score         = { correct: 0, wrong: 0 };
                    this.startGame();
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
