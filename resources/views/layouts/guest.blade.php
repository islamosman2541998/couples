<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col items-center justify-center">
    <div class="w-full max-w-md px-4">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <div class="text-3xl font-black bg-gradient-to-l from-pink-500 to-purple-600 bg-clip-text text-transparent">
                    🎮 {{ \App\Models\Setting::get('site_name', 'Funny Couples ') }}
                </div>
            </a>
        </div>

        <!-- Card -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-gray-600">
            <a href="{{ route('home') }}" class="hover:text-gray-400 transition-colors">العودة للرئيسية</a>
        </div>
    </div>
</body>
</html>
