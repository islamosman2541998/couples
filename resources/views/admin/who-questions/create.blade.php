<x-admin-layout title="إضافة سؤال جديد">

    <form method="POST" action="{{ route('admin.who-questions.store') }}" class="max-w-2xl space-y-6">
        @csrf

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    السؤال <span class="text-red-400">*</span>
                    <span class="text-gray-500 font-normal text-xs mr-2">مثال: مين فيكم أكثر غيرة؟</span>
                </label>
                <textarea name="question" rows="2" required
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 resize-none"
                          placeholder="اكتب السؤال هنا...">{{ old('question') }}</textarea>
                @error('question')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-3">التصنيف <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($categories as $key => $cat)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $key }}"
                                   {{ old('category', 'funny') === $key ? 'checked' : '' }}
                                   class="peer sr-only">
                            <div class="peer-checked:ring-2 peer-checked:ring-purple-500 bg-gray-800 hover:bg-gray-750 border border-gray-700 rounded-xl p-3 flex items-center gap-3 transition-all">
                                <span class="text-2xl">{{ $cat['emoji'] }}</span>
                                <span class="font-medium text-gray-200">{{ $cat['label'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('category')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    التحدي عند الاختلاف
                    <span class="text-gray-500 font-normal text-xs mr-2">يظهر لو الإجابتين مختلفتين</span>
                </label>
                <textarea name="challenge" rows="2"
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 resize-none"
                          placeholder="مثال: الي اختار شريكه يعمله تدليك لمدة دقيقة...">{{ old('challenge') }}</textarea>
                @error('challenge')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded accent-purple-600">
                <label for="is_active" class="text-sm text-gray-300">مفعّل</label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                حفظ السؤال
            </button>
            <a href="{{ route('admin.who-questions.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-6 py-3 rounded-xl font-medium transition-colors">
                إلغاء
            </a>
        </div>
    </form>

</x-admin-layout>
