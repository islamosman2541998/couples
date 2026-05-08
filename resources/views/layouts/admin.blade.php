<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'لوحة التحكم' }} | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
    @stack('styles')
</head>
<body class="bg-gray-950 text-white min-h-screen" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 border-l border-gray-800 flex-shrink-0 flex flex-col"
               :class="sidebarOpen ? 'fixed inset-y-0 right-0 z-50' : 'hidden lg:flex'">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-800">
                <a href="{{ route('home') }}" class="text-lg font-black bg-gradient-to-l from-pink-500 to-purple-600 bg-clip-text text-transparent">
                    🎮 {{ \App\Models\Setting::get('site_name', 'Funny Couples ') }}
                </a>
            </div>

            <!-- Admin Badge -->
            <div class="px-4 py-3 border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-purple-400">مدير النظام</div>
                    </div>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @php
                    $nav = [
                        ['route' => 'admin.dashboard',          'label' => 'لوحة التحكم',    'icon' => '📊'],
                        ['route' => 'admin.games.index',        'label' => 'الألعاب',          'icon' => '🎮'],
                        ['route' => 'admin.cards.index',        'label' => 'كروت الأحكام',    'icon' => '🃏'],
                        ['route' => 'admin.card-levels.index',  'label' => 'مستويات الكروت',  'icon' => '📈'],
                        ['route' => 'admin.spinner-images.index','label' => 'صور السبينر',    'icon' => '🎡'],
                        ['route' => 'admin.subscriptions.index','label' => 'الاشتراكات',      'icon' => '💳'],
                        ['route' => 'admin.users.index',        'label' => 'المستخدمين',      'icon' => '👥'],
                        ['route' => 'admin.settings.index',     'label' => 'الإعدادات',       'icon' => '⚙️'],
                    ];
                @endphp

                @foreach($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all
                              {{ request()->routeIs($item['route']) || (str_starts_with(request()->route()->getName() ?? '', str_replace('.index','.',$item['route'])))
                                 ? 'bg-purple-700 text-white font-medium'
                                 : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <span class="text-base">{{ $item['icon'] }}</span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-gray-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-400 hover:bg-red-900/30 hover:text-red-400 transition-all">
                        <span>🚪</span> تسجيل الخروج
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
             class="fixed inset-0 bg-black/60 z-40 lg:hidden"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Bar -->
            <header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-bold">{{ $title ?? 'لوحة التحكم' }}</h1>
                </div>
                <a href="{{ route('home') }}" class="text-sm text-gray-400 hover:text-white flex items-center gap-1">
                    <span>🌐</span> عرض الموقع
                </a>
            </header>

            <!-- Flash Messages -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    class="mx-6 mt-4 bg-green-600/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-xl text-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    class="mx-6 mt-4 bg-red-600/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl text-sm">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
