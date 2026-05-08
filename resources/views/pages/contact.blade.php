<x-app-layout>
    <x-slot name="title">تواصل معنا</x-slot>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <div class="text-6xl mb-4">📞</div>
            <h1 class="text-4xl font-black mb-4">تواصل معنا</h1>
            <p class="text-gray-400">نحن هنا لمساعدتك في أي استفسار</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if(\App\Models\Setting::get('contact_phone'))
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-3">📞</div>
                    <h3 class="font-bold mb-2">الهاتف</h3>
                    <p class="text-gray-400 font-mono">{{ \App\Models\Setting::get('contact_phone') }}</p>
                </div>
            @endif
            @if(\App\Models\Setting::get('contact_email'))
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-3">📧</div>
                    <h3 class="font-bold mb-2">البريد الإلكتروني</h3>
                    <a href="mailto:{{ \App\Models\Setting::get('contact_email') }}" class="text-purple-400 hover:text-purple-300">
                        {{ \App\Models\Setting::get('contact_email') }}
                    </a>
                </div>
            @endif
            @if(\App\Models\Setting::get('contact_whatsapp'))
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-3">💬</div>
                    <h3 class="font-bold mb-2">واتساب</h3>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\Setting::get('contact_whatsapp')) }}"
                       class="text-green-400 hover:text-green-300">
                        {{ \App\Models\Setting::get('contact_whatsapp') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
