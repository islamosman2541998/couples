<x-admin-layout title="إضافة كارت تحدي">

    <form method="POST" action="{{ route('admin.challenge-cards.store') }}"
          enctype="multipart/form-data" class="max-w-2xl space-y-6">
        @csrf

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    عنوان التحدي <span class="text-red-400">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500"
                       placeholder="مثال: وضعية الفراشة">
                @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الوصف / التعليمات</label>
                <textarea name="description" rows="3"
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 resize-none"
                          placeholder="اكتب وصف أو تعليمات التحدي هنا...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    الصورة
                    <span class="text-gray-500 font-normal text-xs mr-1">(تظهر على الكارت)</span>
                </label>
                <input type="file" name="image" accept="image/*" id="imageInput"
                       onchange="previewImage(this)"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-gray-400 focus:outline-none focus:border-purple-500
                              file:ml-3 file:bg-purple-700 file:text-white file:border-0 file:rounded-lg file:px-3 file:py-1 file:text-sm cursor-pointer">
                <div id="imagePreview" class="mt-3 hidden">
                    <img id="previewImg" class="w-32 h-32 rounded-xl object-cover border border-gray-700">
                </div>
                @error('image')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">التصنيف <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($categories as $key => $cat)
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="{{ $key }}"
                                       {{ old('category', 'general') === $key ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="peer-checked:ring-2 peer-checked:ring-purple-500 bg-gray-800 border border-gray-700 rounded-xl p-2 flex items-center gap-2 transition-all text-sm">
                                    <span>{{ $cat['emoji'] }}</span>
                                    <span class="text-gray-300">{{ $cat['label'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        مدة التايمر (ثانية)
                        <span class="text-gray-500 font-normal text-xs block">0 = بدون تايمر</span>
                    </label>
                    <input type="number" name="timer" value="{{ old('timer', 0) }}" min="0" max="300"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                    <p class="text-xs text-gray-600 mt-1">مثال: 60 ثانية = دقيقة</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">الترتيب</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                </div>
                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-purple-600">
                        <span class="text-sm text-gray-300">مفعّل</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                حفظ الكارت
            </button>
            <a href="{{ route('admin.challenge-cards.index') }}"
               class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-6 py-3 rounded-xl font-medium transition-colors">
                إلغاء
            </a>
        </div>
    </form>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</x-admin-layout>
