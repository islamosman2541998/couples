<x-admin-layout title="إدارة المستخدمين">

    <form method="GET" class="flex gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو الإيميل..."
               class="flex-1 max-w-xs bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl text-sm">بحث</button>
    </form>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500 border-b border-gray-800">
                        <th class="text-right p-4">المستخدم</th>
                        <th class="text-right p-4">الجوال</th>
                        <th class="text-right p-4">الاشتراكات</th>
                        <th class="text-right p-4">الحالة</th>
                        <th class="text-right p-4">التسجيل</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-800/40 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-sm font-mono text-gray-400">{{ $user->phone ?? '-' }}</td>
                            <td class="p-4 text-sm text-gray-300">{{ $user->subscriptions_count }}</td>
                            <td class="p-4">
                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2 py-1 text-xs rounded-full transition-colors
                                        {{ $user->is_active ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-gray-600/20 text-gray-400 border border-gray-600/30' }}">
                                        {{ $user->is_active ? 'مفعّل' : 'معطّل' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-4 text-xs text-gray-500">{{ $user->created_at->format('Y/m/d') }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-xs text-blue-400 hover:text-blue-300 px-2 py-1 bg-blue-900/20 rounded-lg">عرض</a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-purple-400 hover:text-purple-300 px-2 py-1 bg-purple-900/20 rounded-lg">تعديل</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('حذف هذا المستخدم؟')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-300 px-2 py-1 bg-red-900/20 rounded-lg">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">لا يوجد مستخدمون</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-gray-800">{{ $users->links() }}</div>
        @endif
    </div>

</x-admin-layout>
