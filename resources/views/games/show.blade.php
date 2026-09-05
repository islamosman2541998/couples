<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-8 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white">الرئيسية</a>
            <span>›</span>
            <span class="text-white">{{ $game->name }}</span>
        </nav>

        <div class="bg-gray-900 rounded-3xl border border-gray-800 overflow-hidden">
            <!-- Game Header -->
            <div class="h-64 bg-gradient-to-br
                {{ $game->is_free ? 'from-green-900/50 to-teal-900/50' : 'from-yellow-900/30 to-orange-900/30' }}
                flex items-center justify-center text-8xl relative">
                @if($game->image)
                    <img src="{{ $game->image_url }}" alt="{{ $game->name }}" class="w-full h-full object-cover absolute inset-0">
                    <div class="absolute inset-0 bg-black/30"></div>
                @endif
                @php
                    $gameIcons = ['card' => '🃏', 'spinner' => '🎡', 'scratch' => '✨', 'who' => '🤔', 'challenge' => '🎯', 'know_me' => '💍'];
                @endphp
                <span class="relative">{{ $gameIcons[$game->type] ?? '🎮' }}</span>
                <div class="absolute top-4 left-4">
                    @if($game->is_free)
                        <span class="bg-green-500 text-white text-sm px-3 py-1 rounded-full font-bold">مجاني</span>
                    @else
                        <span class="bg-gradient-to-l from-yellow-500 to-orange-500 text-white text-sm px-3 py-1 rounded-full font-bold">
                            {{ number_format($game->price, 0) }} 
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-8">
                <h1 class="text-3xl font-black mb-4">{{ $game->name }}</h1>
                <p class="text-gray-400 text-lg leading-relaxed mb-8">{{ $game->description }}</p>

                @php
                    $playSteps = collect(preg_split('/\R/u', trim((string) $game->how_to_play)))
                        ->map(fn ($step) => trim($step))
                        ->filter();
                @endphp
                @if($playSteps->isNotEmpty())
                    <section class="bg-gradient-to-br from-gray-800/80 to-gray-800/40 border border-gray-700 rounded-2xl p-6 mb-8" aria-labelledby="how-to-play-title">
                        <h2 id="how-to-play-title" class="text-xl font-black text-white mb-5 flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-purple-600/20 flex items-center justify-center" aria-hidden="true">🎮</span>
                            طريقة اللعب
                        </h2>
                        <ol class="space-y-4">
                            @foreach($playSteps as $step)
                                <li class="flex items-start gap-3 text-gray-300 leading-relaxed">
                                    <span class="shrink-0 w-7 h-7 rounded-full bg-purple-600 text-white text-sm font-bold flex items-center justify-center mt-0.5">{{ $loop->iteration }}</span>
                                    <span>{{ $step }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <p class="mt-6 pt-4 border-t border-gray-700 text-sm text-gray-400 leading-relaxed">
                            💗 أي سؤال أو مهمة ممكن تتخطّوها من غير تبرير؛ اختاروا دائمًا ما يناسبكم أنتم الاتنين.
                        </p>
                    </section>
                @endif

                <div class="flex gap-4">
                    @if($game->is_free)
                        <a href="{{ route('games.play', $game->slug) }}"
                           class="flex-1 text-center bg-gradient-to-l from-green-600 to-teal-600 hover:from-green-500 hover:to-teal-500 text-white py-4 rounded-2xl font-bold text-lg transition-all transform hover:scale-105">
                            العب الآن 🎮
                        </a>
                    @else
                        @auth
                            @if(auth()->user()->hasActiveSubscription($game->id))
                                <a href="{{ route('games.play', $game->slug) }}"
                                   class="flex-1 text-center bg-gradient-to-l from-yellow-600 to-orange-600 hover:from-yellow-500 hover:to-orange-500 text-white py-4 rounded-2xl font-bold text-lg transition-all transform hover:scale-105">
                                    العب الآن 🎮
                                </a>
                            @else
                                <a href="{{ route('subscribe.create', $game->id) }}"
                                   class="flex-1 text-center bg-gradient-to-l from-yellow-600 to-orange-600 hover:from-yellow-500 hover:to-orange-500 text-white py-4 rounded-2xl font-bold text-lg transition-all transform hover:scale-105">
                                    اشترك الآن ⭐
                                </a>
                            @endif
                        @else
                            <a href="{{ route('subscribe.create', $game->id) }}"
                               class="flex-1 text-center bg-gradient-to-l from-yellow-600 to-orange-600 hover:from-yellow-500 hover:to-orange-500 text-white py-4 rounded-2xl font-bold text-lg transition-all transform hover:scale-105">
                                اشترك الآن ⭐
                            </a>
                        @endauth
                    @endif

                    <a href="{{ route('home') }}" class="px-6 border border-gray-700 hover:border-gray-500 text-gray-400 hover:text-white py-4 rounded-2xl font-bold transition-colors">
                        عودة
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
