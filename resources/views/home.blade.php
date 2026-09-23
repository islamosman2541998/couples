<x-app-layout>
<x-slot name="title">{{ \App\Models\Setting::get('site_name', 'Funny Couples') }} — سهرة مختلفة ليكم أنتم الاتنين</x-slot>
@php
    $games = $freeGames->concat($paidGames)->sortBy('sort_order');
    $bundlePrice = \App\Support\Membership::price();
@endphp
<div class="discover-page">
<section class="discover-hero discover-wrap">
    <div class="hero-copy"><span class="discover-eyebrow">وقت ليكم. حكايات بينكم.</span><h1>لسّه في حاجات<br>حلوة <em>تكتشفوها.</em></h1><p>اقفلوا زحمة اليوم وافتحوا باب للضحك والتحديات.<br>ألعاب لشخصين… وكل لعبة بداية لسهرة مختلفة.</p><div class="discover-actions"><a class="discover-button" href="#games">اكتشف ألعابكم <span>↙</span></a><a class="discover-link" href="#plans">شوف الاشتراكات ←</a></div><div class="hero-facts"><span>♡ لشخصين</span><span>◈ من المتصفح</span><span>✦ محتوى للمشتركين</span></div></div>
    <div class="hero-art"><img src="{{ asset('images/home/date-night-hero.png') }}" alt="كروت غامضة ونرد وقلب زجاجي لأجواء سهرة ألعاب" width="1536" height="1024" fetchpriority="high"><div class="art-caption"><span>THE NEXT CARD IS YOURS</span><strong>يا ترى الكارت الجاي مخبّي إيه؟</strong></div></div>
</section>
<section class="mood-strip discover-wrap" x-data="{ mood: 'الكل' }" id="games">
    <div class="section-intro"><div><span class="discover-eyebrow">اختاروا بداية الحكاية</span><h2>مودكم إيه النهارده؟</h2></div><p>شوفوا الفكرة وطريقة اللعب، وافتحوا التجربة بالاشتراك.</p></div>
    <div class="mood-options" role="group" aria-label="اختار نوع التجربة">@foreach(['الكل' => 'كل الألعاب', 'ضحك' => 'نضحك سوا', 'تعارف' => 'نعرف بعض أكتر', 'تحدي' => 'ندخل تحدي', 'مفاجأة' => 'نفاجئ بعض'] as $value => $label)<button type="button" data-analytics-mood="{{ $value }}" @click="mood = '{{ $value }}'" :aria-pressed="mood === '{{ $value }}'" :class="{ 'selected': mood === '{{ $value }}' }">{{ $label }}</button>@endforeach</div>
    <div class="discover-grid">
    @forelse($games as $game)
        @php($teaser = \App\Support\Membership::teaser($game->type))
        @php($unlocked = auth()->user()?->hasActiveSubscription($game->id))
        <article class="discover-card" x-show="mood === 'الكل' || mood === '{{ $teaser[1] }}'">
            <a href="{{ route('games.show', $game->slug) }}" class="game-cover cover-{{ $game->type }}" aria-label="اكتشف {{ $game->name }}">
                @if($game->image)<img src="{{ $game->image_url }}" alt="{{ $game->name }}" loading="lazy">@else<span class="cover-symbol" aria-hidden="true">{{ $teaser[0] }}</span>@endif
                <span class="access-pill">{{ $unlocked ? '✓ متاحة ليك' : '♧ للمشتركين' }}</span><span class="cover-category">{{ $teaser[1] }}</span>
            </a>
            <div class="discover-card-body"><h3>{{ $game->name }}</h3><p>{{ $teaser[2] }}</p><div class="card-bottom"><span>{{ !$game->is_free && $game->price > 0 ? number_format($game->price, 0).' ج.م' : 'ضمن باقة كل الألعاب' }}</span><a href="{{ $unlocked ? route('games.play', $game->slug) : route('games.show', $game->slug) }}">{{ $unlocked ? 'ابدأ اللعب' : 'خد لمحة' }} ←</a></div></div>
        </article>
    @empty<p class="membership-note">بنجهّز لكم ألعابنا. ارجعوا قريب واكتشفوا الجديد.</p>@endforelse
    </div>
    @foreach(['ضحك', 'تعارف', 'تحدي', 'مفاجأة'] as $mood)
        @if(!$games->contains(fn ($game) => \App\Support\Membership::teaser($game->type)[1] === $mood))<p x-cloak x-show="mood === '{{ $mood }}'" class="membership-note">لسّه مفيش ألعاب في المود ده. اختاروا مود تاني من فوق.</p>@endif
    @endforeach
</section>
<section class="discover-wrap preview-band"><div><span class="discover-eyebrow">الفضول أول خطوة</span><h2>ورا كل كارت،<br>لحظة تخصّكم.</h2><p>أسئلة تفتح كلام، اختيارات غير متوقعة، وتحديات على مزاجكم. شوفوا فكرة كل لعبة قبل الاشتراك، وسيبوا مفاجآتها لوقت اللعب.</p><a class="discover-link" href="#plans">جاهزين تفتحوا الكروت؟ ←</a></div><div class="mystery-deck" aria-hidden="true"><div>♡</div><div>؟<small>الحكاية لسه بتبدأ</small></div><div>✦</div></div></section>
<section class="discover-wrap" id="plans"><div class="section-intro"><div><span class="discover-eyebrow">اختيار واحد… وسهرة على مزاجكم</span><h2>افتحوا باب اللعب.</h2></div><p>دفعة واحدة • بدون تجديد تلقائي</p></div><div class="plans-grid {{ $paidGames->where('price', '>', 0)->isEmpty() ? 'bundle-only' : '' }}">@if($paidGames->where('price', '>', 0)->isNotEmpty())<article class="plan-card"><span class="discover-eyebrow">ابدأوا بلعبة</span><h3>لعبتكم المفضلة</h3><p>اختاروا اللعبة اللي شدّت فضولكم، وافتحوا محتواها كاملًا.</p><ul><li>وصول للعبة المختارة بعد الموافقة</li><li>الأسئلة والتحديات الخاصة بيها</li><li>اشتراك جديد بلا تاريخ انتهاء</li></ul><a class="discover-button secondary" href="#games">اختار لعبتك ←</a></article>@endif<article class="plan-card featured"><span class="plan-badge">كل المودات في مكان واحد</span><span class="discover-eyebrow">باقة كل الألعاب</span><h3>كل مرة، حكاية.</h3><p>كل الألعاب المتاحة بحساب واحد. بدّلوا بين التجارب على مزاجكم.</p>@if($games->isNotEmpty() && $bundlePrice > 0)<div class="plan-price">{{ number_format($bundlePrice, 0) }} <small>ج.م / دفعة واحدة</small></div><ul><li>{{ $games->count() }} ألعاب متاحة حاليًا</li><li>وصول كامل بعد الموافقة على الدفع</li><li>بلا تاريخ انتهاء أو تجديد تلقائي</li></ul><a class="discover-button" href="{{ route('subscribe.bundle') }}">افتح كل الألعاب ←</a>@else<p>الباقة هتكون متاحة قريب.</p>@endif</article></div><p class="plan-footnote">الدفع بفودافون كاش ورفع الإيصال. التفعيل بعد المراجعة والموافقة؛ تابع طلبك من ملفك الشخصي.</p></section>
@php($homeSettings['notifications_enabled'] = false)
@include('home.reviews')
<section class="discover-wrap faq-section"><div><span class="discover-eyebrow">قبل ما تبدأوا</span><h2>يمكن بتفكروا في…</h2></div><div>@foreach([
'هل لازم اشترك علشان ألعب؟' => 'أيوه. تقدر تشوف الألعاب وطريقة اللعب مجانًا، لكن الأسئلة والتحديات واللعب متاحة بعد تفعيل اشتراكك. إنشاء الحساب لوحده مش بيفتح الألعاب.',
'هل محتاجين نحمل تطبيق؟' => 'لأ، الألعاب بتفتح من المتصفح على الموبايل أو الكمبيوتر. تقدروا تلعبوا سوا على نفس الجهاز.',
'إيه الفرق بين اللعبة والباقة؟' => 'الاشتراك الفردي بيفتح اللعبة اللي اخترتها. باقة كل الألعاب بتفتح كل الألعاب المتاحة على المنصة.',
'إمتى الاشتراك بيتفعّل؟' => 'بعد تحويل المبلغ ورفع الإيصال، بنراجع طلبك وبنفعّله بعد الموافقة. حالة الطلب بتظهر في ملفك الشخصي، والتفعيل مش فوري.',
'ولو سؤال أو تحدي مش مناسب لينا؟' => 'اختاروا اللي يريحكم أنتم الاتنين، وتخطّوا أي سؤال أو مهمة من غير تبرير. التجربة على مزاجكم.'
] as $question => $answer)<details><summary>{{ $question }}</summary><p>{{ $answer }}</p></details>@endforeach</div></section>
<section class="discover-wrap final-invite"><span class="discover-eyebrow">سيبوا الروتين برّه</span><h2>سهرتكم الجاية… تبدأ بكارت.</h2><a class="discover-button" href="#plans">اختار اشتراكك ←</a></section>
</div>
</x-app-layout>
