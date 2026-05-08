<x-admin-layout title="تعديل المستخدم">
    <div class="max-w-md">
        <div class="mb-6"><a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة</a></div>
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الاسم</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" dir="ltr"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الجوال</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" dir="ltr"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium">حفظ</button>
                <a href="{{ route('admin.users.index') }}" class="border border-gray-700 text-gray-400 hover:text-white px-6 py-3 rounded-xl">إلغاء</a>
            </div>
        </form>
    </div>
</x-admin-layout>
