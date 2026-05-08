<x-admin-layout title="إدارة الألعاب">

    <div class="flex items-center justify-between mb-6">
        <p class="text-gray-400 text-sm">{{ $games->count() }} لعبة مسجلة</p>
        <a href="{{ route('admin.games.create') }}" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
            <span>+</span> إضافة لعبة
        </a>
    </div>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-800 bg-gray-900/50">
                        <th class="text-right p-4">اللعبة</th>
                        <th class="text-right p-4">النوع</th>
                        <th class="text-right p-4">السعر</th>
                        <th class="text-right p-4">الحالة</th>
                        <th class="text-right p-4">الترتيب</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($games as $game)
                        <tr class="hover:bg-gray-800/40 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
                                        {{ $game->type === 'spinner' ? '🎡' : '🃏' }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm">{{ $game->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $game->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 bg-gray-800 text-gray-300 text-xs rounded-lg">
                                    {{ $game->type === 'spinner' ? 'سبينر' : 'كروت' }}
                                </span>
                            </td>
                            <td class="p-4 text-sm">
                                @if($game->is_free)
                                    <span class="text-green-400">مجاني</span>
                                @else
                                    <span class="text-yellow-400">{{ number_format($game->price, 0) }} ر.س</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <form method="POST" action="{{ route('admin.games.toggle', $game) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2 py-1 text-xs rounded-full transition-colors
                                        {{ $game->is_active ? 'bg-green-500/20 text-green-400 border border-green-500/30 hover:bg-green-500/30' : 'bg-gray-600/20 text-gray-400 border border-gray-600/30 hover:bg-gray-600/30' }}">
                                        {{ $game->is_active ? 'مفعّل' : 'معطّل' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-4 text-sm text-gray-500">{{ $game->sort_order }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('admin.games.edit', $game) }}" class="text-xs text-purple-400 hover:text-purple-300 px-2 py-1 bg-purple-900/20 rounded-lg">تعديل</a>
                                    <form method="POST" action="{{ route('admin.games.destroy', $game) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه اللعبة؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-300 px-2 py-1 bg-red-900/20 rounded-lg">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">لا توجد ألعاب مضافة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin-layout>
