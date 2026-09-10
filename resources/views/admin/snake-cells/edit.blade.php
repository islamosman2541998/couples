<x-admin-layout :title="'تعديل الخانة '.$cell->number">
    <form method="POST" action="{{ route('admin.snake-cells.update', $cell) }}" class="max-w-2xl space-y-5 bg-gray-900 border border-gray-800 p-6 rounded-2xl">
        @csrf @method('PUT')
        <div><label for="title" class="block mb-2">عنوان التحدي</label><input id="title" name="title" value="{{ old('title', $cell->title) }}" maxlength="100" required class="w-full bg-gray-800 border-gray-700 rounded-xl">@error('title')<p class="text-red-400">{{ $message }}</p>@enderror</div>
        <div><label for="content" class="block mb-2">التعليمات</label><textarea id="content" name="content" rows="5" maxlength="1500" required class="w-full bg-gray-800 border-gray-700 rounded-xl">{{ old('content', $cell->content) }}</textarea>@error('content')<p class="text-red-400">{{ $message }}</p>@enderror</div>
        <div><label for="mood" class="block mb-2">نوع التحدي</label><select id="mood" name="mood" class="w-full bg-gray-800 border-gray-700 rounded-xl">@foreach(\App\Models\SnakeCell::MOODS as $key => $label)<option value="{{ $key }}" @selected(old('mood', $cell->mood) === $key)>{{ $label }}</option>@endforeach</select>@error('mood')<p class="text-red-400">{{ $message }}</p>@enderror</div>
        <input type="hidden" name="is_active" value="0"><label class="flex gap-2 items-center"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $cell->is_active))> تفعيل التحدي</label>
        @error('is_active')<p class="text-red-400">{{ $message }}</p>@enderror
        <p class="text-gray-400 text-sm">عند التعطيل تظل الخانة ومسارات الحركة موجودة، ويظهر للاعب وقت استراحة.</p>
        <div class="flex gap-3"><button class="bg-purple-700 hover:bg-purple-600 px-5 py-3 rounded-xl">حفظ التحدي</button><a href="{{ route('admin.snake-cells.index') }}" class="bg-gray-800 px-5 py-3 rounded-xl">رجوع</a></div>
    </form>
</x-admin-layout>
