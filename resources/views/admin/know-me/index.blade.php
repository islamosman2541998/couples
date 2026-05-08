<x-admin-layout title="أسئلة اعرفني 💍">

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach($categories as $key => $cat)
                <a href="{{ request('cat') === $key ? route('admin.know-me.index') : route('admin.know-me.index', ['cat' => $key]) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ request('cat') === $key ? 'text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}"
                   @if(request('cat') === $key) style="background-color: {{ $cat['color'] }}" @endif>
                    {{ $cat['emoji'] }} {{ $cat['label'] }}
                </a>
            @endforeach
        </div>
        <a href="{{ route('admin.know-me.create') }}"
           class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
            <span>+</span> إضافة سؤال
        </a>
    </div>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-800">
                        <th class="text-right p-4">السؤال</th>
                        <th class="text-right p-4 w-28">التصنيف</th>
                        <th class="text-right p-4 w-28">نوع الإجابة</th>
                        <th class="text-right p-4 w-20">الحالة</th>
                        <th class="p-4 w-28"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($questions as $q)
                        <tr class="hover:bg-gray-800/40 transition-colors">
                            <td class="p-4">
                                <div class="text-sm text-gray-200 line-clamp-2">{{ $q->question }}</div>
                                @if($q->hint)
                                    <div class="text-xs text-gray-600 mt-1">💡 {{ $q->hint }}</div>
                                @endif
                                @if($q->choices)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($q->choices as $choice)
                                            <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full">{{ $choice }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold"
                                      style="background-color: {{ $q->category_color }}20; color: {{ $q->category_color }}">
                                    {{ $q->category_emoji }} {{ $q->category_label }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="text-xs px-2 py-1 rounded-full {{ $q->answer_type === 'choice' ? 'bg-blue-900/30 text-blue-400' : 'bg-gray-700/50 text-gray-400' }}">
                                    {{ $q->answer_type === 'choice' ? '🔘 اختياري' : '✏️ مفتوح' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $q->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/20 text-gray-400' }}">
                                    {{ $q->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('admin.know-me.edit', $q) }}"
                                       class="text-xs text-purple-400 hover:text-purple-300 px-2 py-1 bg-purple-900/20 rounded-lg">تعديل</a>
                                    <form method="POST" action="{{ route('admin.know-me.destroy', $q) }}"
                                          onsubmit="return confirm('حذف هذا السؤال؟')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-300 px-2 py-1 bg-red-900/20 rounded-lg">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center">
                                <div class="text-4xl mb-3">💍</div>
                                <p class="text-gray-500">لا توجد أسئلة مضافة</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($questions->hasPages())
            <div class="p-4 border-t border-gray-800">{{ $questions->links() }}</div>
        @endif
    </div>

</x-admin-layout>
