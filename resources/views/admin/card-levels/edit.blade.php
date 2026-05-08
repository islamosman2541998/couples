<x-admin-layout title="تعديل المستوى">
    <div class="max-w-md">
        <div class="mb-6"><a href="{{ route('admin.card-levels.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة</a></div>
        <form method="POST" action="{{ route('admin.card-levels.update', $cardLevel) }}" class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">اسم المستوى</label>
                <input type="text" name="name" value="{{ old('name', $cardLevel->name) }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">اللون</label>
                <input type="color" name="color" value="{{ old('color', $cardLevel->color) }}" class="h-10 w-16 rounded-lg cursor-pointer bg-gray-800 border border-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الترتيب</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $cardLevel->sort_order) }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none">
            </div>
            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium">حفظ</button>
                <a href="{{ route('admin.card-levels.index') }}" class="border border-gray-700 text-gray-400 hover:text-white px-6 py-3 rounded-xl">إلغاء</a>
            </div>
        </form>
    </div>
</x-admin-layout>
