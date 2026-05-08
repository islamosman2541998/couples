<x-admin-layout title="تعديل سؤال اعرفني">

    <form method="POST" action="{{ route('admin.know-me.update', $knowMe) }}" class="max-w-2xl space-y-6"
          x-data="{ answerType: '{{ old('answer_type', $knowMe->answer_type) }}', choices: {{ json_encode(old('choices', $knowMe->choices ?? ['', '', '', ''])) }} }">
        @csrf @method('PUT')

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">السؤال <span class="text-red-400">*</span></label>
                <textarea name="question" rows="2" required
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 resize-none">{{ old('question', $knowMe->question) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-3">التصنيف <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($categories as $key => $cat)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $key }}"
                                   {{ old('category', $knowMe->category) === $key ? 'checked' : '' }}
                                   class="peer sr-only">
                            <div class="peer-checked:ring-2 peer-checked:ring-purple-500 bg-gray-800 border border-gray-700 rounded-xl p-2.5 flex items-center gap-2 transition-all">
                                <span class="text-lg">{{ $cat['emoji'] }}</span>
                                <span class="text-sm text-gray-300">{{ $cat['label'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-3">نوع الإجابة <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="answer_type" value="open" x-model="answerType" class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-purple-500 bg-gray-800 border border-gray-700 rounded-xl p-3 flex items-center gap-3 transition-all">
                            <span class="text-xl">✏️</span>
                            <div>
                                <div class="text-sm font-medium text-gray-200">إجابة مفتوحة</div>
                                <div class="text-xs text-gray-500">الشخص يكتب إجابته بحرية</div>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="answer_type" value="choice" x-model="answerType" class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-purple-500 bg-gray-800 border border-gray-700 rounded-xl p-3 flex items-center gap-3 transition-all">
                            <span class="text-xl">🔘</span>
                            <div>
                                <div class="text-sm font-medium text-gray-200">اختياري</div>
                                <div class="text-xs text-gray-500">تحديد خيارات للإجابة</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div x-show="answerType === 'choice'" class="space-y-3">
                <label class="block text-sm font-medium text-gray-300">الخيارات</label>
                <template x-for="(choice, i) in choices" :key="i">
                    <div class="flex gap-2">
                        <input type="text" :name="`choices[${i}]`" x-model="choices[i]"
                               :placeholder="`الخيار ${i + 1}`"
                               class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-purple-500">
                        <button type="button" @click="choices.splice(i, 1)" x-show="choices.length > 2"
                                class="text-red-400 hover:text-red-300 px-3 py-2 bg-red-900/20 rounded-xl text-sm">✕</button>
                    </div>
                </template>
                <button type="button" @click="choices.push('')"
                        class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1">
                    + إضافة خيار
                </button>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">تلميح للإجابة</label>
                <input type="text" name="hint" value="{{ old('hint', $knowMe->hint) }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
            </div>

            <div class="flex items-center gap-3 pt-1">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                       {{ old('is_active', $knowMe->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded accent-purple-600">
                <label for="is_active" class="text-sm text-gray-300">مفعّل</label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                حفظ التعديلات
            </button>
            <a href="{{ route('admin.know-me.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-6 py-3 rounded-xl font-medium transition-colors">
                إلغاء
            </a>
        </div>
    </form>

</x-admin-layout>
