<x-app-layout>
    <x-slot name="title">ملفي الشخصي</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Profile Header -->
        <div class="bg-gradient-to-br from-purple-900/40 to-pink-900/30 rounded-3xl border border-purple-800/30 p-8 mb-8">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-3xl font-black flex-shrink-0">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-black">{{ $user->name }}</h1>
                    <p class="text-gray-400">{{ $user->email }}</p>
                    @if($user->phone)<p class="text-gray-500 text-sm mt-1">📞 {{ $user->phone }}</p>@endif
                    <p class="text-gray-600 text-xs mt-2">عضو منذ {{ optional($user->created_at)->format('Y/m/d') ?? '-' }}</p>
                </div>
                <div class="mr-auto">
                    <a href="{{ route('profile.edit') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                        تعديل البيانات ✏️
                    </a>
                </div>
            </div>
        </div>

        <!-- Subscriptions -->
        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
            <span>💳</span> اشتراكاتي
        </h2>

        @if($user->subscriptions->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
                <div class="text-5xl mb-4">🎮</div>
                <h3 class="text-lg font-bold mb-2">لا توجد اشتراكات بعد</h3>
                <p class="text-gray-400 text-sm mb-6">اشترك في الألعاب المميزة واستمتع بتجربة لا تُنسى</p>
                <a href="{{ route('home') }}" class="bg-gradient-to-l from-pink-600 to-purple-600 text-white px-6 py-3 rounded-xl font-bold inline-block">
                    استعرض الألعاب
                </a>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($user->subscriptions as $sub)
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-2xl">
                                {{ $sub->game->type === 'spinner' ? '🎡' : '🃏' }}
                            </div>
                            <div>
                                <div class="font-bold">{{ $sub->game->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ optional($sub->created_at)->format('Y/m/d') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 text-xs rounded-full
                                {{ $sub->status === 'approved' ? 'bg-green-500/20 text-green-400 border border-green-500/30' :
                                   ($sub->status === 'rejected' ? 'bg-red-500/20 text-red-400 border border-red-500/30' :
                                    'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30') }}">
                                {{ $sub->status_label }}
                            </span>
                            @if($sub->status === 'approved')
                                <a href="{{ route('games.play', $sub->game->slug) }}"
                                   class="bg-green-600 hover:bg-green-500 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                    العب
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
