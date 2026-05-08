<x-admin-layout title="إدارة كروت الأحكام">

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="level" onchange="this.form.submit()" class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none">
                <option value="">جميع المستويات</option>
                @foreach($levels as $level)
                    <option value="{{ $level->id }}" {{ request('level') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                @endforeach
            </select>
            <select name="target" onchange="this.form.submit()" class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none">
                <option value="">جميع الأنواع</option>
                <option value="male" {{ request('target') === 'male' ? 'selected' : '' }}>راجل</option>
                <option value="female" {{ request('target') === 'female' ? 'selected' : '' }}>ست</option>
                <option value="both" {{ request('target') === 'both' ? 'selected' : '' }}>مشترك</option>
            </select>
        </form>
        <a href="{{ route('admin.cards.create') }}" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
            <span>+</span> إضافة كارت
        </a>
    </div>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-800">
                        <th class="text-right p-4">المستوى</th>
                        <th class="text-right p-4">النص</th>
                        <th class="text-right p-4">الموجّه لـ</th>
                        <th class="text-right p-4">الحالة</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($cards as $card)
                        <tr class="hover:bg-gray-800/40 transition-colors">
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded-full font-bold"
                                      style="background-color: {{ $card->level->color }}20; color: {{ $card->level->color }}">
                                    {{ $card->level->name }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-300 max-w-xs">
                                <div class="line-clamp-2">{{ $card->content }}</div>
                            </td>
                            <td class="p-4">
                                <span class="text-sm {{ $card->target === 'male' ? 'text-blue-400' : ($card->target === 'female' ? 'text-pink-400' : 'text-purple-400') }}">
                                    {{ $card->target_label }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $card->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/20 text-gray-400' }}">
                                    {{ $card->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('admin.cards.edit', $card) }}" class="text-xs text-purple-400 hover:text-purple-300 px-2 py-1 bg-purple-900/20 rounded-lg">تعديل</a>
                                    <form method="POST" action="{{ route('admin.cards.destroy', $card) }}"
                                          onsubmit="return confirm('حذف هذا الكارت؟')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-300 px-2 py-1 bg-red-900/20 rounded-lg">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-gray-500">لا توجد كروت مضافة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cards->hasPages())
            <div class="p-4 border-t border-gray-800">{{ $cards->links() }}</div>
        @endif
    </div>

</x-admin-layout>
