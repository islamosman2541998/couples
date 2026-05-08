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
                <span class="relative">{{ $game->type === 'spinner' ? '🎡' : '🃏' }}</span>
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

                @if($game->type === 'card')
                    <div class="bg-gray-800/50 rounded-2xl p-6 mb-8">
                        <h3 class="font-bold text-white mb-4 flex items-center gap-2"><span>🃏</span> كيف تلعب؟</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span> اختر مستوى الصعوبة (سهل / متوسط / صعب)</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span> يتناوب اللاعبان (راجل وست) على سحب الكروت</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span> اضغط على الكارت لتقلبه وتظهر التحدي</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span> نفّذ التحدي أو الحكم المكتوب على الكارت</li>
                        </ul>
                    </div>
                @elseif($game->type === 'spinner')
                    <div class="bg-gray-800/50 rounded-2xl p-6 mb-8">
                        <h3 class="font-bold text-white mb-4 flex items-center gap-2"><span>🎡</span> كيف تلعب؟</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span> اضغط زر "لف" لتدوير العجلة</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span> انتظر حتى تتوقف العجلة على نتيجة عشوائية</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span> نفّذ ما تشير إليه العجلة</li>
                        </ul>
                    </div>
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
