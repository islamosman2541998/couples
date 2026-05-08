<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
    @stack('styles')
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-gray-900/95 backdrop-blur-sm border-b border-purple-900/50 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="text-xl font-black bg-gradient-to-l from-pink-500 to-purple-600 bg-clip-text text-transparent">
                    🎮 {{ \App\Models\Setting::get('site_name', 'Funny Couples ') }}
                </a>

                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition-colors text-sm">الرئيسية</a>
                    <a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors text-sm">من نحن</a>
                    <a href="{{ route('contact') }}" class="text-gray-300 hover:text-white transition-colors text-sm">تواصل معنا</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="text-sm bg-purple-700 hover:bg-purple-600 text-white px-3 py-1.5 rounded-lg transition-colors">لوحة التحكم</a>
                        @endif
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="hidden sm:block text-sm">{{ auth()->user()->name }}</span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute left-0 mt-2 w-48 bg-gray-800 rounded-xl border border-gray-700 shadow-2xl py-1 z-50">
                                <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">ملفي الشخصي</a>
                                <hr class="border-gray-700 my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-right px-4 py-2 text-sm text-red-400 hover:bg-gray-700">تسجيل الخروج</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-white text-sm">تسجيل الدخول</a>
                        <a href="{{ route('register') }}" class="bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white text-sm px-4 py-2 rounded-xl font-medium transition-all">إنشاء حساب</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @foreach(['success' => 'bg-green-600', 'error' => 'bg-red-600', 'warning' => 'bg-yellow-600', 'info' => 'bg-blue-600'] as $type => $color)
        @if(session($type))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] {{ $color }} text-white px-6 py-3 rounded-xl shadow-2xl max-w-md w-[90%] text-sm">
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <main class="flex-1">{{ $slot }}</main>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="text-xl font-black bg-gradient-to-l from-pink-500 to-purple-600 bg-clip-text text-transparent mb-3">
                        🎮 {{ \App\Models\Setting::get('site_name', 'Funny Couples ') }}
                    </div>
                    <p class="text-gray-400 text-sm">{{ \App\Models\Setting::get('site_description', '') }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-white mb-4">روابط سريعة</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white">الرئيسية</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white">من نحن</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-white">سياسة الخصوصية</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">تواصل معنا</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-white mb-4">تواصل معنا</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        @if(\App\Models\Setting::get('contact_phone'))<li>📞 {{ \App\Models\Setting::get('contact_phone') }}</li>@endif
                        @if(\App\Models\Setting::get('contact_email'))<li>📧 {{ \App\Models\Setting::get('contact_email') }}</li>@endif
                        @if(\App\Models\Setting::get('contact_whatsapp'))<li>💬 {{ \App\Models\Setting::get('contact_whatsapp') }}</li>@endif
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm text-gray-500">
                {{ \App\Models\Setting::get('footer_text', 'جميع الحقوق محفوظة') }}
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
