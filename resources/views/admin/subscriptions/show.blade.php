<x-admin-layout title="تفاصيل الاشتراك">

    <div class="max-w-2xl">
        <div class="mb-6"><a href="{{ route('admin.subscriptions.index') }}" class="text-gray-400 hover:text-white text-sm">← العودة للاشتراكات</a></div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 mb-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold">{{ $subscription->full_name }}</h2>
                    <p class="text-gray-400 text-sm mt-1">{{ $subscription->email }}</p>
                    <p class="text-gray-500 text-sm">📞 {{ $subscription->phone }}</p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full
                    {{ $subscription->status === 'approved' ? 'bg-green-500/20 text-green-400 border border-green-500/30' :
                       ($subscription->status === 'rejected' ? 'bg-red-500/20 text-red-400 border border-red-500/30' :
                        'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30') }}">
                    {{ $subscription->status_label }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                <div>
                    <span class="text-gray-500">اللعبة:</span>
                    <div class="text-white font-medium mt-1">{{ $subscription->game->name }}</div>
                </div>
                <div>
                    <span class="text-gray-500">تاريخ الطلب:</span>
                    <div class="text-white font-medium mt-1">{{ optional($subscription->created_at)->format('Y/m/d H:i') ?? '-' }}</div>
                </div>
                @if($subscription->approved_at)
                    <div>
                        <span class="text-gray-500">تاريخ القبول:</span>
                        <div class="text-green-400 font-medium mt-1">{{ optional($subscription->approved_at)->format('Y/m/d H:i') ?? '-' }}</div>
                    </div>
                @endif
                @if($subscription->admin_notes)
                    <div class="col-span-2">
                        <span class="text-gray-500">ملاحظات الإدارة:</span>
                        <div class="text-white mt-1">{{ optional($subscription->admin_notes) ?? '-' }}</div>
                    </div>
                @endif
            </div>

            <!-- Receipt Image -->
            <div>
                <h3 class="text-sm text-gray-500 mb-3">صورة إيصال التحويل</h3>
                <a href="{{ @$subscription->receipt_url }}" target="_blank" class="block">
                    <img src="{{ @$subscription->receipt_url }}" alt="إيصال التحويل"
                         class="max-w-full max-h-80 object-contain rounded-xl border border-gray-700 hover:border-purple-500 transition-colors">
                </a>
            </div>
        </div>

        <!-- Actions -->
        @if($subscription->status === 'pending')
            <div class="grid grid-cols-2 gap-4">
                <form method="POST" action="{{ route('admin.subscriptions.approve', $subscription) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <textarea name="admin_notes" rows="2" placeholder="ملاحظة (اختياري)"
                                  class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-green-700 hover:bg-green-600 text-white py-3 rounded-xl font-bold transition-colors">
                        ✅ قبول الاشتراك
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.subscriptions.reject', $subscription) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <textarea name="admin_notes" rows="2" placeholder="سبب الرفض (مهم)"
                                  class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white py-3 rounded-xl font-bold transition-colors">
                        ❌ رفض الاشتراك
                    </button>
                </form>
            </div>
        @elseif($subscription->status === 'rejected')
            <form method="POST" action="{{ route('admin.subscriptions.approve', $subscription) }}">
                @csrf @method('PATCH')
                <button type="submit" class="w-full bg-green-700 hover:bg-green-600 text-white py-3 rounded-xl font-bold">
                    إعادة قبول الاشتراك
                </button>
            </form>
        @endif
    </div>

</x-admin-layout>
