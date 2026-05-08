<x-admin-layout title="تعديل الكارت">
    <div class="max-w-xl">
        <div class="mb-6"><a href="{{ route('admin.cards.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة للكروت</a></div>

        <form method="POST" action="{{ route('admin.cards.update', $card) }}" class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">المستوى *</label>
                <select name="card_level_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white">
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}" {{ old('card_level_id', $card->card_level_id) == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">نص التحدي/الحكم *</label>
                <textarea name="content" rows="4"
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none">{{ old('content', $card->content) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">موجّه لـ</label>
                <select name="target" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white">
                    <option value="both" {{ old('target', $card->target) === 'both' ? 'selected' : '' }}>مشترك</option>
                    <option value="male" {{ old('target', $card->target) === 'male' ? 'selected' : '' }}>راجل</option>
                    <option value="female" {{ old('target', $card->target) === 'female' ? 'selected' : '' }}>ست</option>
                </select>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $card->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded accent-purple-600">
                <label class="text-sm font-medium text-gray-300">مفعّل</label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium">حفظ</button>
                <a href="{{ route('admin.cards.index') }}" class="border border-gray-700 text-gray-400 hover:text-white px-6 py-3 rounded-xl font-medium">إلغاء</a>
            </div>
        </form>
    </div>
</x-admin-layout>
