<x-admin-layout title="إضافة لعبة جديدة">

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.games.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة للألعاب</a>
        </div>

        <form method="POST" action="{{ route('admin.games.store') }}" enctype="multipart/form-data"
              class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">اسم اللعبة *</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full bg-gray-800 border @error('name') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">نوع اللعبة *</label>
                    <select name="type" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="card" {{ old('type') === 'card' ? 'selected' : '' }}>🃏 لعبة كروت</option>
                        <option value="spinner" {{ old('type') === 'spinner' ? 'selected' : '' }}>🎡 سبينر</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">الترتيب</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">الوصف</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_free" value="1" {{ old('is_free', '1') ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-purple-600"
                               x-model="isFree">
                        <span class="text-sm font-medium text-gray-300">لعبة مجانية</span>
                    </label>
                </div>

                <div x-data="{ isFree: {{ old('is_free', '1') ? 'true' : 'false' }} }">
                    <div x-show="!isFree">
                        <label class="block text-sm font-medium text-gray-300 mb-2">السعر </label>
                        <input type="number" name="price" value="{{ old('price', 0) }}" step="0.01" min="0"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">صورة اللعبة</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none text-sm">
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="w-4 h-4 rounded accent-purple-600">
                        <span class="text-sm font-medium text-gray-300">مفعّلة</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                    إضافة اللعبة
                </button>
                <a href="{{ route('admin.games.index') }}" class="border border-gray-700 hover:border-gray-600 text-gray-400 hover:text-white px-6 py-3 rounded-xl font-medium transition-colors">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

</x-admin-layout>
