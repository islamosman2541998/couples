<x-admin-layout title="صور السبينر">

    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.spinner-images.create') }}" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium">+ إضافة صورة</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @forelse($images as $image)
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden group">
                <div class="h-32 relative" style="background-color: {{ $image->color }}20">
                    @if($image->image !== 'spinner/placeholder.png')
                        <img src="{{ asset('storage/' . $image->image) }}" alt="{{ $image->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl">🎡</div>
                    @endif
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all"></div>
                </div>
                <div class="p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $image->color }}"></div>
                        <div class="font-medium text-sm truncate">{{ $image->name }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <form method="POST" action="{{ route('admin.spinner.toggle', $image) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs px-2 py-0.5 rounded-full {{ $image->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/20 text-gray-500' }}">
                                {{ $image->is_active ? 'مفعّل' : 'معطّل' }}
                            </button>
                        </form>
                        <div class="flex gap-1">
                            <a href="{{ route('admin.spinner-images.edit', $image) }}" class="text-xs text-purple-400 hover:text-purple-300">تعديل</a>
                            <span class="text-gray-700">|</span>
                            <form method="POST" action="{{ route('admin.spinner-images.destroy', $image) }}"
                                  onsubmit="return confirm('حذف هذه الصورة؟')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-300">حذف</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center text-gray-500">
                <div class="text-5xl mb-3">🎡</div>
                <p>لا توجد صور في العجلة</p>
            </div>
        @endforelse
    </div>

</x-admin-layout>
