<x-admin-layout title="إعدادات الموقع">

    <div x-data="{ tab: 'general' }">
        <!-- Tabs -->
        <div class="flex gap-2 mb-8 flex-wrap">
            @foreach([
                ['id' => 'general', 'label' => 'عام',          'icon' => '🌐'],
                ['id' => 'contact', 'label' => 'التواصل',      'icon' => '📞'],
                ['id' => 'social',  'label' => 'السوشيال',     'icon' => '📱'],
                ['id' => 'pages',   'label' => 'الصفحات',      'icon' => '📄'],
                ['id' => 'logo',    'label' => 'الشعار',       'icon' => '🖼️'],
            ] as $t)
                <button @click="tab = '{{ $t['id'] }}'"
                        :class="tab === '{{ $t['id'] }}' ? 'bg-purple-700 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                        class="px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
                    <span>{{ $t['icon'] }}</span> {{ $t['label'] }}
                </button>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl">
            @csrf

            <!-- General Settings -->
            <div x-show="tab === 'general'" class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
                <h2 class="font-bold text-lg border-b border-gray-800 pb-4">الإعدادات العامة</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">اسم الموقع</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">وصف الموقع</label>
                    <input type="text" name="site_description" value="{{ $settings['site_description'] ?? '' }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">نص الـ Footer</label>
                    <input type="text" name="footer_text" value="{{ $settings['footer_text'] ?? '' }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">اللون الأساسي</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#7c3aed' }}" class="h-10 w-14 rounded-lg cursor-pointer">
                            <span class="text-gray-400 text-sm font-mono">{{ $settings['primary_color'] ?? '#7c3aed' }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">اللون الثانوي</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="secondary_color" value="{{ $settings['secondary_color'] ?? '#ec4899' }}" class="h-10 w-14 rounded-lg cursor-pointer">
                            <span class="text-gray-400 text-sm font-mono">{{ $settings['secondary_color'] ?? '#ec4899' }}</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium w-full">حفظ الإعدادات</button>
            </div>

            <!-- Contact Settings -->
            <div x-show="tab === 'contact'" x-cloak class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
                <h2 class="font-bold text-lg border-b border-gray-800 pb-4">بيانات التواصل والبنك</h2>
                @foreach([
                    ['name' => 'contact_phone',    'label' => 'رقم الهاتف',      'dir' => 'ltr'],
                    ['name' => 'contact_email',    'label' => 'البريد الإلكتروني', 'dir' => 'ltr'],
                    ['name' => 'contact_whatsapp', 'label' => 'رقم الواتساب',    'dir' => 'ltr'],
                    ['name' => 'bank_name',        'label' => 'اسم البنك',        'dir' => 'rtl'],
                    ['name' => 'bank_holder',      'label' => 'اسم صاحب الحساب', 'dir' => 'rtl'],
                    ['name' => 'bank_account',     'label' => 'رقم IBAN',         'dir' => 'ltr'],
                ] as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ $field['label'] }}</label>
                        <input type="text" name="{{ $field['name'] }}" value="{{ $settings[$field['name']] ?? '' }}" dir="{{ $field['dir'] }}"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                @endforeach
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium w-full">حفظ</button>
            </div>

            <!-- Social Settings -->
            <div x-show="tab === 'social'" x-cloak class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
                <h2 class="font-bold text-lg border-b border-gray-800 pb-4">روابط السوشيال ميديا</h2>
                @foreach([
                    ['name' => 'social_twitter',   'label' => '𝕏 تويتر/X'],
                    ['name' => 'social_instagram',  'label' => '📸 إنستغرام'],
                    ['name' => 'social_snapchat',   'label' => '👻 سناب شات'],
                    ['name' => 'social_tiktok',     'label' => '🎵 تيك توك'],
                ] as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ $field['label'] }}</label>
                        <input type="url" name="{{ $field['name'] }}" value="{{ $settings[$field['name']] ?? '' }}" dir="ltr" placeholder="https://"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                @endforeach
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium w-full">حفظ</button>
            </div>

            <!-- Pages Settings -->
            <div x-show="tab === 'pages'" x-cloak class="bg-gray-900 border border-gray-800 rounded-2xl p-8 space-y-5">
                <h2 class="font-bold text-lg border-b border-gray-800 pb-4">محتوى الصفحات</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">نص صفحة "من نحن"</label>
                    <textarea name="about_text" rows="6"
                              class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none">{{ $settings['about_text'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">نص "سياسة الخصوصية"</label>
                    <textarea name="privacy_text" rows="6"
                              class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none">{{ $settings['privacy_text'] ?? '' }}</textarea>
                </div>
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium w-full">حفظ</button>
            </div>
        </form>

        <!-- Logo Upload (separate form) -->
        <div x-show="tab === 'logo'" x-cloak class="max-w-2xl bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <h2 class="font-bold text-lg border-b border-gray-800 pb-4 mb-5">شعار الموقع</h2>

            @if($settings['site_logo'] ?? null)
                <div class="mb-5">
                    <p class="text-sm text-gray-400 mb-2">الشعار الحالي:</p>
                    <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo" class="h-16 bg-gray-800 p-2 rounded-xl">
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.logo') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">رفع شعار جديد</label>
                    <input type="file" name="logo" accept="image/*"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none">
                </div>
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium">رفع الشعار</button>
            </form>
        </div>
    </div>

</x-admin-layout>
