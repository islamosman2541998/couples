<x-app-layout>
    <x-slot name="title">تعديل بياناتي</x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('profile.index') }}" class="text-gray-400 hover:text-white text-sm">← ملفي الشخصي</a>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <h1 class="text-xl font-bold mb-6">تعديل البيانات</h1>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full bg-gray-800 border @error('name') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">رقم الجوال</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" dir="ltr"
                           placeholder="05xxxxxxxx"
                           class="w-full bg-gray-800 border @error('phone') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" dir="ltr"
                           class="w-full bg-gray-800 border @error('email') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-gray-800 pt-5">
                    <h3 class="text-sm font-medium text-gray-400 mb-4">تغيير كلمة المرور (اختياري)</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور الجديدة</label>
                            <input type="password" name="password" dir="ltr"
                                   class="w-full bg-gray-800 border @error('password') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" dir="ltr"
                                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-3 rounded-xl font-bold transition-all">
                    حفظ التغييرات
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
