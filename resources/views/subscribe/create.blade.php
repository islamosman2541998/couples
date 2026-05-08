<x-app-layout>
    <x-slot name="title">الاشتراك في {{ $game->name }}</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="text-6xl mb-4">💳</div>
            <h1 class="text-3xl font-black mb-2">الاشتراك في {{ $game->name }}</h1>
            <p class="text-gray-400">أكمل بيانات الاشتراك وارفع إيصال التحويل </p>
        </div>

        <!-- Bank Info -->
        <div class="bg-blue-900/20 border border-blue-700/40 rounded-2xl p-6 mb-8">
            <h3 class="font-bold text-blue-300 mb-4 flex items-center gap-2">🏦 بيانات التحويل </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                {{-- <div>
                    <span class="text-gray-500">اسم البنك:</span>
                    <div class="text-white font-medium mt-1">{{ \App\Models\Setting::get('bank_name', 'البنك الأهلي') }}</div>
                </div>
                <div>
                    <span class="text-gray-500">اسم الحساب:</span>
                    <div class="text-white font-medium mt-1">{{ \App\Models\Setting::get('bank_holder', 'صاحب الحساب') }}</div>
                </div> --}}
                <div class="sm:col-span-2">
                    <span class="text-gray-500">رقم التحويل فودافون كاش</span>
                    <div class="text-white font-mono font-bold mt-1 bg-gray-800 px-3 py-2 rounded-lg text-sm tracking-wider">
                        {{ \App\Models\Setting::get('bank_account', 'SA00 0000 0000 0000 0000 0000') }}
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-yellow-400 font-bold">المبلغ المطلوب: {{ number_format($game->price, 2) }}  </span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('subscribe.store') }}" enctype="multipart/form-data"
              class="bg-gray-900 rounded-2xl border border-gray-800 p-8 space-y-6">
            @csrf

            <input type="hidden" name="game_id" value="{{ $game->id }}">

            <!-- Game Select -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">اللعبة المراد الاشتراك فيها</label>
                <select name="game_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @foreach($paidGames as $g)
                        <option value="{{ $g->id }}" {{ $g->id == $game->id ? 'selected' : '' }}>
                            {{ $g->name }} - {{ number_format($g->price, 0) }} ر
                        </option>
                    @endforeach
                </select>
                @error('game_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">الاسم الكامل *</label>
                <input type="text" name="full_name" value="{{ old('full_name', auth()->user()?->name) }}"
                       placeholder="أدخل اسمك الكامل"
                       class="w-full bg-gray-800 border @error('full_name') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                @error('full_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">رقم الجوال *</label>
                <input type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}"
                       placeholder="05xxxxxxxx" dir="ltr"
                       class="w-full bg-gray-800 border @error('phone') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني *</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                       placeholder="example@email.com" dir="ltr"
                       class="w-full bg-gray-800 border @error('email') border-red-500 @else border-gray-700 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Receipt Image -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">صورة إيصال التحويل البنكي *</label>
                <div x-data="{ preview: null }"
                     class="border-2 border-dashed @error('receipt_image') border-red-500 @else border-gray-700 @enderror rounded-xl p-6 text-center hover:border-purple-500 transition-colors cursor-pointer"
                     @click="$refs.fileInput.click()">
                    <input type="file" name="receipt_image" accept="image/*" x-ref="fileInput" class="hidden"
                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = e => preview = e.target.result; reader.readAsDataURL(file); }">
                    <div x-show="!preview">
                        <div class="text-4xl mb-2">📸</div>
                        <p class="text-gray-400 text-sm">اضغط لرفع صورة الإيصال</p>
                        <p class="text-gray-600 text-xs mt-1">JPG, PNG, WEBP - حتى 5 ميجابايت</p>
                    </div>
                    <div x-show="preview" x-cloak>
                        <img :src="preview" class="max-h-48 mx-auto rounded-lg">
                        <p class="text-green-400 text-xs mt-2">✅ تم اختيار الصورة</p>
                    </div>
                </div>
                @error('receipt_image')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white py-4 rounded-2xl font-bold text-lg transition-all transform hover:scale-105">
                إرسال طلب الاشتراك 🚀
            </button>
        </form>
    </div>
</x-app-layout>
