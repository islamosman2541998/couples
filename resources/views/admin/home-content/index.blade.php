<x-admin-layout title="محتوى الصفحة الرئيسية">
<div class="home-admin">
    <div class="mb-6"><h1 class="text-2xl font-black mb-2">واجهة تحكي حكايتكم ✨</h1><p>السلايدر، رسائل العملاء، العرض والإشعارات… كل تفاصيل الرئيسية من مكان واحد.</p><a class="text-purple-300 text-sm" href="{{ route('home') }}" target="_blank" rel="noopener">معاينة الصفحة الرئيسية ↗</a></div>
    @if($errors->any())<div role="alert" class="mb-6 rounded-xl bg-red-900/40 border border-red-500 p-4"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('admin.home-content.settings') }}">
        @csrf
        <section><h2>01 / إعدادات السلايدر</h2><p>السحب باللمس متاح دائماً. ارفع البانر بنسبة 900 × 470، ويمكن تخصيص صورة للموبايل.</p>
            <div class="home-admin-checks">@foreach(['slider_enabled' => 'عرض السلايدر', 'autoplay' => 'تقليب تلقائي', 'arrows' => 'إظهار الأسهم', 'dots' => 'إظهار النقاط'] as $key => $label)<label><input type="hidden" name="{{ $key }}" value="0"><input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $settings[$key]))>{{ $label }}</label>@endforeach</div>
            <label>وقت كل شريحة بالمللي ثانية (5000 = 5 ثوانٍ)<input type="number" name="delay" min="2000" max="20000" value="{{ old('delay', $settings['delay']) }}" required></label>
        </section>
        <section><h2>02 / آراء العملاء</h2><div class="home-admin-checks"><label><input type="hidden" name="reviews_enabled" value="0"><input type="checkbox" name="reviews_enabled" value="1" @checked(old('reviews_enabled', $settings['reviews_enabled']))>عرض القسم</label></div><label>عنوان القسم<input name="reviews_title" maxlength="150" value="{{ old('reviews_title', $settings['reviews_title']) }}" required></label><p class="mt-3">أضف الاسم وصورة المحادثة بالأسفل. أخفِ رقم الهاتف وبيانات الدفع الخاصة قبل رفع الصورة.</p></section>
        <section><h2>03 / العد التنازلي للعرض</h2><div class="home-admin-checks"><label><input type="hidden" name="countdown_enabled" value="0"><input type="checkbox" name="countdown_enabled" value="1" @checked(old('countdown_enabled', $settings['countdown_enabled']))>عرض العد التنازلي</label></div>
            <div class="home-admin-grid"><label>عنوان العرض<input name="countdown_title" maxlength="150" value="{{ old('countdown_title', $settings['countdown_title']) }}" required></label><label>نظام الوقت<select name="countdown_mode"><option value="visitor" @selected(old('countdown_mode', $settings['countdown_mode']) === 'visitor')>مدة لكل زائر (محفوظة في متصفحه)</option><option value="fixed" @selected(old('countdown_mode', $settings['countdown_mode']) === 'fixed')>موعد ثابت لكل الزوار</option></select></label><label>مدة العرض بالساعات<input name="hours" type="number" min="1" max="720" value="{{ old('hours', $settings['hours']) }}" required></label><label>موعد الانتهاء الثابت ({{ config('app.timezone') }})<input name="ends_at" type="datetime-local" value="{{ old('ends_at', $settings['ends_at']) }}"></label></div>
            <label>نص العرض<textarea name="countdown_text" rows="2" maxlength="500">{{ old('countdown_text', $settings['countdown_text']) }}</textarea></label>
            <div class="home-admin-grid"><label>نص الزر<input name="offer_label" maxlength="80" value="{{ old('offer_label', $settings['offer_label']) }}" required></label><label>رابط الزر<input name="offer_url" dir="ltr" value="{{ old('offer_url', $settings['offer_url']) }}"></label></div>
            <label><input type="checkbox" name="restart_countdown" value="1">بدء مدة جديدة للزوار عند الحفظ</label><p class="mt-3">يختفي العرض عند انتهاء الوقت. هذا القسم للعرض التسويقي؛ عدّل أسعار الألعاب من إدارة الألعاب لتطبيق الخصم.</p>
        </section>
        <section><h2>04 / إشعارات الشراء الترويجية</h2><p>أسماء تجريبية عشوائية وليست طلبات فعلية، وتظهر بعلامة «نشاط ترويجي». يتم تبديل الأسماء دون تكرار حتى تنتهي القائمة.</p>
            <div class="home-admin-checks"><label><input type="hidden" name="notifications_enabled" value="0"><input type="checkbox" name="notifications_enabled" value="1" @checked(old('notifications_enabled', $settings['notifications_enabled']))>تشغيل الإشعارات</label></div>
            <div class="home-admin-grid"><label>مدة ظهور الإشعار بالثواني<input name="duration" type="number" min="3" max="10" value="{{ old('duration', $settings['duration']) }}" required></label><label>الفاصل بين الإشعارات بالثواني<input name="interval" type="number" min="2" max="120" value="{{ old('interval', $settings['interval']) }}" required></label><label>مكان الإشعار<select name="position"><option value="left" @selected(old('position', $settings['position']) === 'left')>أسفل اليسار</option><option value="right" @selected(old('position', $settings['position']) === 'right')>أسفل اليمين</option></select></label></div>
            <label>الأسماء (اسم في كل سطر — {{ count($settings['names']) }} اسم محفوظ)<textarea name="names" rows="6" required>{{ old('names', implode("\n", $settings['names'])) }}</textarea></label>
            <p class="mt-3">الألعاب المقترحة للإشعارات (اترك الاختيارات فارغة لعرض جميع الألعاب النشطة).</p><div class="home-admin-checks">@foreach($games as $game)<label><input type="checkbox" name="game_ids[]" value="{{ $game->id }}" @checked(in_array($game->id, old('game_ids', $settings['game_ids'])))>{{ $game->name }}</label>@endforeach</div>
        </section>
        <button type="submit" class="save-settings mb-8">حفظ كل الإعدادات ✓</button>
    </form>
    @foreach(['slides' => ['السلايدر', $slides], 'reviews' => ['آراء العملاء', $reviews]] as $type => [$label, $items])
    <section id="{{ $type }}"><h2>إدارة {{ $label }}</h2><p>الترتيب الأصغر يظهر أولاً. افتح العنصر لتعديله أو أضف عنصراً جديداً.</p>
        @foreach(collect($items)->sortBy('sort_order') as $item)
            <details><summary>@if($item['image'])<img src="{{ $item['image'] }}" alt="">@endif<span>{{ $item['title'] }} <small class="text-gray-400"> · {{ $item['enabled'] ? 'ظاهر' : 'مخفي' }} · ترتيب {{ $item['sort_order'] }}</small></span></summary>
                @include('admin.home-content.item-form', ['item' => $item])
                <form method="POST" action="{{ route('admin.home-content.destroy', [$type, $item['id']]) }}" x-data @submit="if (!confirm('حذف هذا العنصر؟')) $event.preventDefault()" class="mt-3">@csrf @method('DELETE')<button class="delete-button" type="submit">حذف العنصر</button></form>
            </details>
        @endforeach
        <details class="mt-4"><summary>＋ إضافة {{ $type === 'slides' ? 'شريحة جديدة' : 'رأي عميل' }}</summary>@include('admin.home-content.item-form', ['item' => null])</details>
    </section>
    @endforeach
</div>
</x-admin-layout>
