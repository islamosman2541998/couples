<x-admin-layout :title="$card->exists ? 'تعديل كارت السيطرة' : 'إضافة كارت السيطرة'">
    <form method="POST" action="{{ $card->exists ? route('admin.control-cards.update', $card) : route('admin.control-cards.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
        @csrf
        @if($card->exists) @method('PUT') @endif
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
            <p class="text-gray-400 text-sm">الكروت تظهر عشوائيًا، ودور الزوج والزوجة يتحدد تلقائيًا بالتبادل.</p>
            <div>
                <label for="title" class="block mb-2">عنوان الكارت</label>
                <input id="title" name="title" value="{{ old('title', $card->title) }}" required maxlength="200" class="w-full bg-gray-800 border-gray-700 rounded-xl">
                @error('title')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block mb-2">تعليمات التحدي</label>
                <textarea id="description" name="description" rows="5" required maxlength="2000" class="w-full bg-gray-800 border-gray-700 rounded-xl">{{ old('description', $card->description) }}</textarea>
                @error('description')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="image" class="block mb-2">صورة اختيارية (حتى ٤ ميجابايت)</label>
                <input id="image" type="file" name="image" accept="image/*" class="w-full text-gray-300">
                @error('image')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
                @if($card->image)
                    <img src="{{ $card->image_url }}" alt="صورة الكارت الحالية" class="max-h-40 rounded-xl mt-3">
                    <label class="block mt-3"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))> إزالة الصورة الحالية</label>
                @endif
            </div>
            <div>
                <label for="sort_order" class="block mb-2">الترتيب في لوحة الإدارة</label>
                <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $card->sort_order) }}" min="0" max="1000000" required class="w-full bg-gray-800 border-gray-700 rounded-xl">
                @error('sort_order')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $card->is_active))> الكارت مفعّل</label>
            @error('is_active')<p class="text-red-400 text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-3">
            <button class="bg-purple-700 hover:bg-purple-600 px-6 py-3 rounded-xl">حفظ الكارت</button>
            <a href="{{ route('admin.control-cards.index') }}" class="bg-gray-800 px-6 py-3 rounded-xl">إلغاء</a>
        </div>
    </form>
</x-admin-layout>
