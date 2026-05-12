<x-admin-layout title="الاشتراكات">

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label' => 'الكل',          'value' => $stats['total'],    'color' => 'text-white',       'bg' => 'bg-gray-800'],
            ['label' => 'قيد المراجعة',  'value' => $stats['pending'],  'color' => 'text-yellow-400',  'bg' => 'bg-yellow-900/20'],
            ['label' => 'مقبولة',        'value' => $stats['approved'], 'color' => 'text-green-400',   'bg' => 'bg-green-900/20'],
            ['label' => 'مرفوضة',        'value' => $stats['rejected'], 'color' => 'text-red-400',     'bg' => 'bg-red-900/20'],
        ] as $stat)
            <div class="{{ $stat['bg'] }} border border-gray-800 rounded-2xl p-4 text-center">
                <div class="text-2xl font-black {{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="status" onchange="this.form.submit()" class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
            <option value="">جميع الحالات</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>مقبولة</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوضة</option>
        </select>
    </form>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-800">
                        <th class="text-right p-4">المشترك</th>
                        <th class="text-right p-4">اللعبة</th>
                        <th class="text-right p-4">الجوال</th>
                        <th class="text-right p-4">الحالة</th>
                        <th class="text-right p-4">التاريخ</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-gray-800/40 transition-colors">
                            <td class="p-4">
                                <div class="font-medium text-sm">{{ $sub->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $sub->email }}</div>
                            </td>
                            <td class="p-4 text-sm text-gray-300">{{ $sub->game->name }}</td>
                            <td class="p-4 text-sm font-mono text-gray-400">{{ $sub->phone }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $sub->status === 'approved' ? 'bg-green-500/20 text-green-400' :
                                       ($sub->status === 'rejected' ? 'bg-red-500/20 text-red-400' :
                                        'bg-yellow-500/20 text-yellow-400') }}">
                                    {{ $sub->status_label }}
                                </span>
                            </td>
                            <td class="p-4 text-xs text-gray-500">{{ optional($sub->created_at)->format('Y/m/d') ?? '-' }}</td>
                            <td class="p-4">
                                <a href="{{ route('admin.subscriptions.show', $sub) }}" class="text-xs text-purple-400 hover:text-purple-300 px-2 py-1 bg-purple-900/20 rounded-lg">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">لا توجد اشتراكات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="p-4 border-t border-gray-800">{{ $subscriptions->links() }}</div>
        @endif
    </div>

</x-admin-layout>
