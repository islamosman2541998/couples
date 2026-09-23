<x-app-layout>
    <x-slot name="title">سياسة الخصوصية</x-slot>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <div class="text-6xl mb-4">🔒</div>
            <h1 class="text-4xl font-black">سياسة الخصوصية</h1>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <div class="text-gray-300 leading-relaxed text-lg whitespace-pre-wrap">{{ $text }}</div>
        </div>
        <section class="bg-gray-900 border border-gray-800 rounded-2xl p-8 mt-6 text-gray-300 leading-relaxed">
            <h2 class="text-xl font-bold text-white mb-4">إحصائيات استخدام الموقع</h2>
            <p>نستخدم معرّفًا عشوائيًا في المتصفح لمدة تصل إلى 180 يومًا لقياس الزيارات ومصادرها والصفحات والأزرار المستخدمة ومدة التفاعل التقريبية. نسجل نوع الجهاز والمتصفح والنظام واللغة ودقة الشاشة والمنطقة الزمنية، وجزءًا مخفيًا من عنوان الشبكة. قد نربط النشاط بحسابك عندما تسجّل أو تدخل إليه لتحسين تجربة الاشتراك.</p>
            <p class="mt-3">البيانات محفوظة داخل المنصة ومتاحة للإدارة فقط. لا نسجل كلمات المرور أو محتوى الكتابة أو صور الإيصالات ضمن سجل النشاط، ولا نتتبع نشاطك خارج الموقع أو موقعك الجغرافي الدقيق. نحترم إشارة عدم التتبّع وإشارة الخصوصية العامة من المتصفح.</p>
            <form action="{{ route('analytics.preference') }}" method="POST" class="mt-5">@csrf<input type="hidden" name="disabled" value="{{ request()->cookie('analytics_optout') === '1' ? '0' : '1' }}"><button class="px-5 py-3 rounded-xl bg-purple-700 text-white">{{ request()->cookie('analytics_optout') === '1' ? 'السماح بإحصائيات الزيارة' : 'إيقاف إحصائيات الزيارة في هذا المتصفح' }}</button></form>
        </section>
    </div>
</x-app-layout>
