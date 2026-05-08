<x-admin-layout title="إضافة صورة للسبينر">
    <div class="max-w-md">
        <div class="mb-6"><a href="{{ route('admin.spinner-images.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة</a></div>
        <form method="POST" action="{{ route('admin.spinner-images.store') }}" enctype="multipart/form-data"
              class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الاسم *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الصورة *</label>
                <input type="file" name="image" accept="image/*" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white text-sm">
                @error('image')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">اللون</label>
                <input type="color" name="color" value="{{ old('color', '#6366f1') }}" class="h-10 w-16 rounded-lg cursor-pointer">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الترتيب</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none">
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded accent-purple-600">
                <label class="text-sm text-gray-300">مفعّلة</label>
            </div>
            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium">إضافة</button>
                <a href="{{ route('admin.spinner-images.index') }}" class="border border-gray-700 text-gray-400 hover:text-white px-6 py-3 rounded-xl">إلغاء</a>
            </div>
        </form>
    </div>
</x-admin-layout>
