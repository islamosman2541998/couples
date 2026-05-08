<x-admin-layout title="إدارة كروت السكراتش">

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-400">{{ $cards->total() }} كارت مضاف</p>
        <a href="{{ route('admin.scratch-cards.create') }}" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
            <span>+</span> إضافة كارت
        </a>
    </div>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-800">
                        <th class="text-right p-4 w-16">رقم</th>
                        <th class="text-right p-4">الحكم / التحدي</th>
                        <th class="text-right p-4 w-24">الصورة</th>
                        <th class="text-right p-4 w-20">الترتيب</th>
                        <th class="text-right p-4 w-20">الحالة</th>
                        <th class="p-4 w-28"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($cards as $card)
                        <tr class="hover:bg-gray-800/40 transition-colors">
                            <td class="p-4">
                                <span class="w-9 h-9 rounded-full bg-purple-900/50 border border-purple-700/50 flex items-center justify-center text-purple-300 font-black text-sm">
                                    {{ $card->number }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-300 max-w-xs">
                                <div class="line-clamp-2">{{ $card->content }}</div>
                            </td>
                            <td class="p-4">
                                @if($card->image)
                                    <img src="{{ asset('storage/' . $card->image) }}" class="w-12 h-12 rounded-lg object-cover border border-gray-700">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-800 flex items-center justify-center text-gray-600 text-xs">لا يوجد</div>
                                @endif
                            </td>
                            <td class="p-4 text-sm text-gray-400">{{ $card->sort_order }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $card->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/20 text-gray-400' }}">
                                    {{ $card->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('admin.scratch-cards.edit', $card) }}" class="text-xs text-purple-400 hover:text-purple-300 px-2 py-1 bg-purple-900/20 rounded-lg">تعديل</a>
                                    <form method="POST" action="{{ route('admin.scratch-cards.destroy', $card) }}"
                                          onsubmit="return confirm('حذف هذا الكارت؟')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-300 px-2 py-1 bg-red-900/20 rounded-lg">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">لا توجد كروت مضافة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cards->hasPages())
            <div class="p-4 border-t border-gray-800">{{ $cards->links() }}</div>
        @endif
    </div>

</x-admin-layout>
