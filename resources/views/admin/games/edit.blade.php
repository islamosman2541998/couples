<x-admin-layout title="تعديل اللعبة">

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.games.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة للألعاب</a>
        </div>

        <form method="POST" action="{{ route('admin.games.update', $game) }}" enctype="multipart/form-data"
              class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">اسم اللعبة *</label>
                    <input type="text" name="name" value="{{ old('name', $game->name) }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">نوع اللعبة *</label>
                    <select name="type" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <x-game-type-options :selected="old('type', $game->type)" />
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">الترتيب</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $game->sort_order) }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">الوصف</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none">{{ old('description', $game->description) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">طريقة اللعب</label>
                    <p class="text-xs text-gray-500 mb-2">اكتب كل خطوة في سطر منفصل؛ ستظهر للعميل كقائمة مرتبة.</p>
                    <textarea name="how_to_play" rows="7"
                              class="w-full bg-gray-800 border @error('how_to_play') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 resize-y">{{ old('how_to_play', $game->how_to_play) }}</textarea>
                    @error('how_to_play')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div x-data="{ isFree: {{ old('is_free', $game->is_free ? '1' : '0') ? 'true' : 'false' }} }">
                    <label class="flex items-center gap-3 cursor-pointer mb-4">
                        <input type="checkbox" name="is_free" value="1" x-model="isFree"
                               {{ old('is_free', $game->is_free) ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-purple-600">
                        <span class="text-sm font-medium text-gray-300">لعبة مجانية</span>
                    </label>
                    <div x-show="!isFree">
                        <label class="block text-sm font-medium text-gray-300 mb-2">السعر</label>
                        <input type="number" name="price" value="{{ old('price', $game->price) }}" step="0.01"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">تغيير الصورة</label>
                    @if($game->image)
                        <img src="{{ $game->image_url }}" alt="" class="w-20 h-20 object-cover rounded-xl mb-3">
                    @endif
                    <input type="file" name="image" accept="image/*"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white text-sm">
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $game->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-purple-600">
                        <span class="text-sm font-medium text-gray-300">مفعّلة</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                    حفظ التغييرات
                </button>
                <a href="{{ route('admin.games.index') }}" class="border border-gray-700 text-gray-400 hover:text-white px-6 py-3 rounded-xl font-medium transition-colors">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

</x-admin-layout>
