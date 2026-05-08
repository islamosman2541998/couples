<x-guest-layout>
    <h2 class="text-2xl font-black text-center mb-6">إنشاء حساب جديد</h2>

    @if($errors->any())
        <div class="mb-4 bg-red-900/20 border border-red-700/40 text-red-400 px-4 py-3 rounded-xl text-sm">
            @foreach($errors->all() as $error) <div>• {{ $error }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">الاسم الكامل</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full bg-gray-800 border @error('name') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
            <input type="email" name="email" value="{{ old('email') }}" dir="ltr" required
                   class="w-full bg-gray-800 border @error('email') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور</label>
            <input type="password" name="password" dir="ltr" required autocomplete="new-password"
                   class="w-full bg-gray-800 border @error('password') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" dir="ltr" required autocomplete="new-password"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <button type="submit" class="w-full bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-3 rounded-xl font-bold transition-all">
            إنشاء الحساب
        </button>

        <div class="text-center text-sm text-gray-500">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="text-purple-400 hover:text-purple-300 font-medium">تسجيل الدخول</a>
        </div>
    </form>
</x-guest-layout>
