<x-admin-layout title="مستويات الكروت">

    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.card-levels.create') }}" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium">+ إضافة مستوى</a>
    </div>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="text-xs text-gray-500 border-b border-gray-800">
                    <th class="text-right p-4">المستوى</th>
                    <th class="text-right p-4">اللون</th>
                    <th class="text-right p-4">عدد الكروت</th>
                    <th class="text-right p-4">الترتيب</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($levels as $level)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-sm font-bold"
                                  style="background-color: {{ $level->color }}20; color: {{ $level->color }}">
                                {{ $level->name }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded" style="background-color: {{ $level->color }}"></div>
                                <span class="text-sm text-gray-400 font-mono">{{ $level->color }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-sm text-gray-300">{{ $level->cards_count }} كارت</td>
                        <td class="p-4 text-sm text-gray-500">{{ $level->sort_order }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('admin.card-levels.edit', $level) }}" class="text-xs text-purple-400 hover:text-purple-300 px-2 py-1 bg-purple-900/20 rounded-lg">تعديل</a>
                                <form method="POST" action="{{ route('admin.card-levels.destroy', $level) }}"
                                      onsubmit="return confirm('حذف هذا المستوى وجميع كروته؟')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-400 hover:text-red-300 px-2 py-1 bg-red-900/20 rounded-lg">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-admin-layout>
