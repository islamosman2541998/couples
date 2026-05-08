<x-admin-layout title="إضافة كارت سكراتش">

    <form method="POST" action="{{ route('admin.scratch-cards.store') }}" enctype="multipart/form-data"
          class="max-w-2xl space-y-6">
        @csrf

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">رقم الحكم <span class="text-red-400">*</span></label>
                    <input type="number" name="number" value="{{ old('number', $nextNumber) }}" min="1"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500"
                           required>
                    @error('number')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">الترتيب</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">نص الحكم / التحدي <span class="text-red-400">*</span></label>
                <textarea name="content" rows="3" required
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 resize-none"
                          placeholder="اكتب الحكم أو التحدي هنا...">{{ old('content') }}</textarea>
                @error('content')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الصورة (تظهر تحت السكراتش)</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-gray-400 focus:outline-none focus:border-purple-500 file:mr-3 file:bg-purple-700 file:text-white file:border-0 file:rounded-lg file:px-3 file:py-1 file:text-sm">
                <p class="text-xs text-gray-500 mt-1">اختياري - إذا لم تُرفع صورة سيظهر رقم الحكم فقط</p>
                @error('image')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded accent-purple-600">
                <label for="is_active" class="text-sm text-gray-300">مفعّل</label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                حفظ الكارت
            </button>
            <a href="{{ route('admin.scratch-cards.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-6 py-3 rounded-xl font-medium transition-colors">
                إلغاء
            </a>
        </div>
    </form>

</x-admin-layout>
