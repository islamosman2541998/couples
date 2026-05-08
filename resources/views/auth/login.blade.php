<x-guest-layout>
    <h2 class="text-2xl font-black text-center mb-6">تسجيل الدخول</h2>

    <x-auth-session-status class="mb-4 bg-blue-900/20 border border-blue-700/40 text-blue-300 px-4 py-3 rounded-xl text-sm" :status="session('status')" />

    @if($errors->any())
        <div class="mb-4 bg-red-900/20 border border-red-700/40 text-red-400 px-4 py-3 rounded-xl text-sm">
            @foreach($errors->all() as $error) <div>• {{ $error }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
            <input type="email" name="email" value="{{ old('email') }}" dir="ltr" required autofocus autocomplete="username"
                   class="w-full bg-gray-800 border @error('email') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور</label>
            <input type="password" name="password" dir="ltr" required autocomplete="current-password"
                   class="w-full bg-gray-800 border @error('password') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded accent-purple-600">
                <span class="text-sm text-gray-400">تذكرني</span>
            </label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-purple-400 hover:text-purple-300">نسيت كلمة المرور؟</a>
            @endif
        </div>

        <button type="submit" class="w-full bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-3 rounded-xl font-bold transition-all">
            دخول
        </button>

        <div class="text-center text-sm text-gray-500">
            ليس لديك حساب؟
            <a href="{{ route('register') }}" class="text-purple-400 hover:text-purple-300 font-medium">إنشاء حساب جديد</a>
        </div>
    </form>
</x-guest-layout>
