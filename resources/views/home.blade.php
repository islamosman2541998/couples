<x-app-layout>
    <x-slot name="title">{{ \App\Models\Setting::get('site_name', 'Funny Couples ') }} - الصفحة الرئيسية</x-slot>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-900/40 via-gray-950 to-pink-900/30"></div>
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 70% 50%, rgba(139,92,246,0.15) 0%, transparent 60%);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <div class="inline-flex items-center gap-2 bg-purple-900/50 border border-purple-700/50 text-purple-300 px-4 py-2 rounded-full text-sm mb-6">
                <span>🎮</span> منصة الألعاب الترفيهية
            </div>
            <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight">
                العب <span class="bg-gradient-to-l from-pink-400 to-purple-400 bg-clip-text text-transparent">وتمتع</span>
                <br>مع من تحب
            </h1>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto mb-10">
                ألعاب ترفيهية مميزة تجمعك مع شريكك في لحظات لا تُنسى. اكتشف تحديات ممتعة وذكريات جميلة.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#games" class="bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all transform hover:scale-105">
                    ابدأ اللعب الآن 🚀
                </a>
                @guest
                    <a href="{{ route('register') }}" class="border border-purple-500/50 text-purple-300 hover:bg-purple-900/30 px-8 py-4 rounded-2xl font-bold text-lg transition-all">
                        إنشاء حساب مجاني
                    </a>
                @endguest
            </div>
        </div>
    </section>

    <!-- Free Games -->
    @if($freeGames->isNotEmpty())
    <section id="games" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-8 bg-gradient-to-b from-green-400 to-green-600 rounded-full"></div>
            <h2 class="text-2xl font-black">الألعاب المجانية</h2>
            <span class="bg-green-500/20 text-green-400 text-xs px-2 py-1 rounded-full border border-green-500/30">مجاني</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($freeGames as $game)
                <div class="group bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden hover:border-green-500/50 transition-all hover:transform hover:-translate-y-1">
                    <div class="h-48 bg-gradient-to-br from-green-900/40 to-teal-900/40 flex items-center justify-center text-6xl relative">
                        @if($game->image)
                            <img src="{{ $game->image_url }}" alt="{{ $game->name }}" class="w-full h-full object-cover absolute inset-0">
                        @else
                            {{ $game->type === 'spinner' ? '🎡' : '🃏' }}
                        @endif
                        <span class="absolute top-3 left-3 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-bold">مجاني</span>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold mb-2">{{ $game->name }}</h3>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $game->description }}</p>
                        <div class="flex gap-2">
                            <a href="{{ route('games.play', $game->slug) }}"
                               class="flex-1 text-center bg-green-600 hover:bg-green-500 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">
                                العب الآن 🎮
                            </a>
                            <a href="{{ route('games.show', $game->slug) }}"
                               class="px-4 border border-gray-700 hover:border-gray-500 text-gray-400 hover:text-white py-2.5 rounded-xl text-sm transition-colors">
                                تفاصيل
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Paid Games -->
    @if($paidGames->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-20">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-8 bg-gradient-to-b from-yellow-400 to-orange-500 rounded-full"></div>
            <h2 class="text-2xl font-black">الألعاب المميزة</h2>
            <span class="bg-yellow-500/20 text-yellow-400 text-xs px-2 py-1 rounded-full border border-yellow-500/30">⭐ مدفوع</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($paidGames as $game)
                <div class="group bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden hover:border-yellow-500/50 transition-all hover:transform hover:-translate-y-1 relative">
                    <!-- Premium badge -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-l from-yellow-400 to-orange-500"></div>

                    <div class="h-48 bg-gradient-to-br from-yellow-900/30 to-orange-900/30 flex items-center justify-center text-6xl relative">
                        @if($game->image)
                            <img src="{{ $game->image_url }}" alt="{{ $game->name }}" class="w-full h-full object-cover absolute inset-0">
                        @else
                            {{ $game->type === 'spinner' ? '🎡' : '🃏' }}
                        @endif
                        <span class="absolute top-3 left-3 bg-gradient-to-l from-yellow-500 to-orange-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                            {{ number_format($game->price, 0) }} ر.س
                        </span>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold mb-2">{{ $game->name }}</h3>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $game->description }}</p>
                        <div class="flex gap-2">
                            @auth
                                @if(auth()->user()->hasActiveSubscription($game->id))
                                    <a href="{{ route('games.play', $game->slug) }}"
                                       class="flex-1 text-center bg-gradient-to-l from-yellow-600 to-orange-600 hover:from-yellow-500 hover:to-orange-500 text-white py-2.5 rounded-xl text-sm font-bold transition-all">
                                        العب الآن 🎮
                                    </a>
                                @else
                                    <a href="{{ route('subscribe.create', $game->id) }}"
                                       class="flex-1 text-center bg-gradient-to-l from-yellow-600 to-orange-600 hover:from-yellow-500 hover:to-orange-500 text-white py-2.5 rounded-xl text-sm font-bold transition-all">
                                        اشترك الآن ⭐
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('subscribe.create', $game->id) }}"
                                   class="flex-1 text-center bg-gradient-to-l from-yellow-600 to-orange-600 hover:from-yellow-500 hover:to-orange-500 text-white py-2.5 rounded-xl text-sm font-bold transition-all">
                                    اشترك الآن ⭐
                                </a>
                            @endauth
                            <a href="{{ route('games.show', $game->slug) }}"
                               class="px-4 border border-gray-700 hover:border-gray-500 text-gray-400 hover:text-white py-2.5 rounded-xl text-sm transition-colors">
                                تفاصيل
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- How it works -->
    <section class="bg-gray-900/50 border-y border-gray-800 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-black mb-12">كيف تبدأ؟</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach([
                    ['icon' => '1️⃣', 'title' => 'أنشئ حسابك',    'desc' => 'سجّل حساباً مجانياً في ثوانٍ'],
                    ['icon' => '2️⃣', 'title' => 'اختر لعبتك',     'desc' => 'تصفّح الألعاب المتاحة واختر ما يناسبك'],
                    ['icon' => '3️⃣', 'title' => 'ابدأ الاستمتاع', 'desc' => 'العب مع شريكك وعش لحظات لا تُنسى'],
                ] as $step)
                    <div class="text-center">
                        <div class="text-4xl mb-4">{{ $step['icon'] }}</div>
                        <h3 class="text-xl font-bold mb-2">{{ $step['title'] }}</h3>
                        <p class="text-gray-400">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</x-app-layout>
<style>
    .h-48 {
    height: 22rem !important;
}
</style>