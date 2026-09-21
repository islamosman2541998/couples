<x-admin-layout title="كروت لعبة السيطرة">
    <div class="flex items-center justify-between gap-3 mb-6">
        <p class="text-gray-400">{{ $cards->total() }} كارت</p>
        <a href="{{ route('admin.control-cards.create') }}" class="bg-purple-700 hover:bg-purple-600 px-4 py-3 rounded-xl">+ إضافة كارت</a>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="text-gray-400 border-b border-gray-800"><tr><th class="p-4">الكارت</th><th class="p-4">الترتيب</th><th class="p-4">الحالة</th><th class="p-4">الإجراءات</th></tr></thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($cards as $card)
                    <tr>
                        <td class="p-4 min-w-64">
                            @if($card->image)<img src="{{ $card->image_url }}" alt="{{ $card->title }}" class="w-16 h-16 object-cover rounded-lg mb-2">@endif
                            <strong>{{ $card->title }}</strong><p class="text-gray-400 mt-1 line-clamp-2">{{ $card->description }}</p>
                        </td>
                        <td class="p-4">{{ $card->sort_order }}</td>
                        <td class="p-4 {{ $card->is_active ? 'text-green-400' : 'text-gray-400' }}">{{ $card->is_active ? 'مفعّل' : 'معطّل' }}</td>
                        <td class="p-4">
                            <div class="flex gap-3">
                                <a href="{{ route('admin.control-cards.edit', $card) }}" class="text-purple-300">تعديل</a>
                                <form method="POST" action="{{ route('admin.control-cards.destroy', $card) }}" onsubmit="return confirm('حذف هذا الكارت؟')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-10 text-center text-gray-400">لا توجد كروت بعد. أضف أول كارت للعبة السيطرة.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($cards->hasPages())<div class="p-4">{{ $cards->links() }}</div>@endif
    </div>
</x-admin-layout>
