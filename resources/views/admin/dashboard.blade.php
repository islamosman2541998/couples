<x-admin-layout title="لوحة التحكم">

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        @php
            $statItems = [
                ['label' => 'المستخدمين',    'value' => $stats['users'],                'icon' => '👥', 'color' => 'from-blue-600 to-blue-800'],
                ['label' => 'الألعاب',       'value' => $stats['games'],               'icon' => '🎮', 'color' => 'from-purple-600 to-purple-800'],
                ['label' => 'الاشتراكات',    'value' => $stats['subscriptions'],        'icon' => '💳', 'color' => 'from-green-600 to-green-800'],
                ['label' => 'قيد المراجعة',  'value' => $stats['pending_subscriptions'],'icon' => '⏳', 'color' => 'from-yellow-600 to-yellow-800'],
                ['label' => 'مقبولة',        'value' => $stats['approved_subscriptions'],'icon' => '✅', 'color' => 'from-teal-600 to-teal-800'],
                ['label' => 'الكروت',        'value' => $stats['cards'],               'icon' => '🃏', 'color' => 'from-pink-600 to-pink-800'],
            ];
        @endphp

        @foreach($statItems as $stat)
            <div class="bg-gradient-to-br {{ $stat['color'] }} rounded-2xl p-5">
                <div class="text-3xl mb-2">{{ $stat['icon'] }}</div>
                <div class="text-2xl font-black">{{ $stat['value'] }}</div>
                <div class="text-sm opacity-80 mt-1">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    <!-- Recent Subscriptions -->
    <div class="bg-gray-900 rounded-2xl border border-gray-800">
        <div class="flex items-center justify-between p-6 border-b border-gray-800">
            <h2 class="text-lg font-bold">آخر طلبات الاشتراك</h2>
            <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-purple-400 hover:text-purple-300">عرض الكل</a>
        </div>

        @if($recentSubscriptions->isEmpty())
            <div class="p-8 text-center text-gray-500">لا توجد اشتراكات بعد</div>
        @else
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
                        @foreach($recentSubscriptions as $sub)
                            <tr class="hover:bg-gray-800/50 transition-colors">
                                <td class="p-4">
                                    <div class="font-medium text-sm">{{ $sub->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $sub->email }}</div>
                                </td>
                                <td class="p-4 text-sm text-gray-300">{{ $sub->game->name }}</td>
                                <td class="p-4 text-sm text-gray-300 font-mono">{{ $sub->phone }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $sub->status === 'approved' ? 'bg-green-500/20 text-green-400' :
                                           ($sub->status === 'rejected' ? 'bg-red-500/20 text-red-400' :
                                            'bg-yellow-500/20 text-yellow-400') }}">
                                        {{ $sub->status_label }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-gray-500">{{ $sub->created_at->format('Y/m/d') }}</td>
                                <td class="p-4">
                                    <a href="{{ route('admin.subscriptions.show', $sub) }}" class="text-xs text-purple-400 hover:text-purple-300">عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-admin-layout>
