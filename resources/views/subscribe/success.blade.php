<x-app-layout>
    <x-slot name="title">تم إرسال طلب الاشتراك</x-slot>

    <div class="max-w-lg mx-auto px-4 py-20 text-center">
        <div class="text-8xl mb-6 animate-bounce">🎉</div>
        <h1 class="text-3xl font-black mb-4 text-green-400">تم إرسال طلبك بنجاح!</h1>
        <p class="text-gray-400 text-lg mb-8">
            سيتم مراجعة طلب اشتراكك وإيصال التحويل من قِبل الإدارة.
            ستتلقى إشعاراً عند قبول طلبك.
        </p>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-8 text-right">
            <h3 class="font-bold text-white mb-4">ماذا يحدث بعد ذلك؟</h3>
            <ul class="space-y-3 text-sm text-gray-400">
                <li class="flex items-center gap-3"><span class="text-yellow-400 text-lg">1️⃣</span> سيراجع فريقنا إيصال التحويل الخاص بك</li>
                <li class="flex items-center gap-3"><span class="text-yellow-400 text-lg">2️⃣</span> سيتم تفعيل اشتراكك خلال 24 ساعة</li>
                <li class="flex items-center gap-3"><span class="text-yellow-400 text-lg">3️⃣</span> ستتمكن من الوصول للعبة في ملفك الشخصي</li>
            </ul>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white px-8 py-3 rounded-2xl font-bold transition-all">
                العودة للرئيسية
            </a>
            @auth
                <a href="{{ route('profile.index') }}" class="border border-gray-700 hover:border-gray-500 text-gray-400 hover:text-white px-8 py-3 rounded-2xl font-bold transition-colors">
                    ملفي الشخصي
                </a>
            @endauth
        </div>
    </div>
</x-app-layout>
