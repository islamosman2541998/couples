@props(['value' => 1])
<div>
    <label for="level" class="block text-sm text-gray-300 mb-2">المستوى والنقاط</label>
    <select id="level" name="level" class="w-full bg-gray-800 rounded-xl border-gray-700">
        @foreach([1 => 'الأول · نقطة', 2 => 'الثاني · نقطتان', 3 => 'الثالث · 3 نقاط'] as $level => $label)
            <option value="{{ $level }}" @selected($value == $level)>{{ $label }}</option>
        @endforeach
    </select>
    @error('level')<p class="text-red-400 text-sm">{{ $message }}</p>@enderror
</div>
