<label class="flex items-start gap-3 cursor-pointer mb-4">
    <input type="checkbox" name="trial_enabled" value="1" {{ $checked ? 'checked' : '' }}
           class="w-4 h-4 mt-1 rounded accent-purple-600">
    <span>
        <span class="block text-sm font-medium text-gray-300">متاحة كتجربة مجانية (أول {{ \App\Support\GameTrial::size() }})</span>
        <span class="block text-xs text-gray-500 mt-1">لو شلت العلامة، اللعبة تبقى مقفولة واللي مش مشترك يتحوّل لصفحة الاشتراك.</span>
    </span>
</label>
