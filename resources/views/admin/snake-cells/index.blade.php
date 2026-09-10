<x-admin-layout title="خانات السلم والتعبان">
    <p class="text-gray-400 mb-6">{{ $cells->total() }} خانة · تعديل التحدي لا يغيّر مسار السلالم والثعابين. تعطيل التحدي يحوّل الخانة لاستراحة.</p>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="text-gray-400 border-b border-gray-800"><tr><th class="p-4">الخانة</th><th class="p-4">التحدي</th><th class="p-4">النوع</th><th class="p-4">الحالة</th><th class="p-4">تعديل</th></tr></thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($cells as $cell)
                    <tr><td class="p-4 font-bold text-yellow-300">{{ $cell->number }}</td><td class="p-4 min-w-64"><strong>{{ $cell->title }}</strong><p class="text-gray-400 mt-1 line-clamp-2">{{ $cell->content }}</p></td><td class="p-4 whitespace-nowrap">{{ \App\Models\SnakeCell::MOODS[$cell->mood] }}</td><td class="p-4 whitespace-nowrap">{{ $cell->is_active ? 'مفعّل' : 'استراحة' }}</td><td class="p-4"><a class="text-purple-300" href="{{ route('admin.snake-cells.edit', $cell) }}">تعديل</a></td></tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400">لم تتم إضافة خانات اللعبة بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $cells->links() }}</div>
    </div>
</x-admin-layout>
