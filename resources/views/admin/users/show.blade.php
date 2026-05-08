<x-admin-layout title="تفاصيل المستخدم">
    <div class="max-w-2xl">
        <div class="mb-6"><a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة</a></div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xl font-black">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                    <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                    @if($user->phone)<p class="text-gray-500 text-sm">📞 {{ $user->phone }}</p>@endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">تاريخ التسجيل:</span><div class="mt-1">{{ $user->created_at->format('Y/m/d') }}</div></div>
                <div><span class="text-gray-500">الحالة:</span>
                    <div class="mt-1 {{ $user->is_active ? 'text-green-400' : 'text-red-400' }}">
                        {{ $user->is_active ? 'مفعّل' : 'معطّل' }}
                    </div>
                </div>
            </div>
        </div>

        @if($user->subscriptions->isNotEmpty())
            <h3 class="font-bold mb-4">الاشتراكات ({{ $user->subscriptions->count() }})</h3>
            <div class="space-y-3">
                @foreach($user->subscriptions as $sub)
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-sm">{{ $sub->game->name }}</div>
                            <div class="text-xs text-gray-500">{{ $sub->created_at->format('Y/m/d') }}</div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $sub->status === 'approved' ? 'bg-green-500/20 text-green-400' :
                               ($sub->status === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                            {{ $sub->status_label }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>
