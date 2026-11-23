<x-app-layout>
<x-slot name="title">{{ $game->name }} — اكتشف التجربة</x-slot>
@php
$teaser = \App\Support\Membership::teaser($game->type);
$unlocked = auth()->user()?->hasActiveSubscription($game->id);
$trialOpen = $game->trial_enabled;
$subscribeUrl = !$game->is_free && $game->price > 0 ? route('subscribe.create', $game->id) : route('subscribe.bundle');
@endphp
<div class="discover-page discover-wrap game-detail">
<a class="discover-link" href="{{ route('home') }}#games">→ كل الألعاب</a>
<section class="detail-grid"><div class="detail-art game-cover cover-{{ $game->type }}">@if($game->image)<img src="{{ $game->image_url }}" alt="{{ $game->name }}">@else<span class="cover-symbol">{{ $teaser[0] }}</span>@endif<span class="access-pill">{{ $unlocked ? '✓ متاحة ليك' : ($trialOpen ? 'جرّبها مجانًا' : 'بالاشتراك') }}</span></div><div><span class="discover-eyebrow">{{ $teaser[1] }} · لشخصين</span><h1>{{ $game->name }}</h1><h2>{{ $teaser[2] }}</h2><p>{{ $game->description ?: $teaser[3] }}</p><div class="discover-actions">@if($unlocked)<a class="discover-button" href="{{ route('games.play', $game->slug) }}">ابدأ اللعب ←</a>@elseif($trialOpen)<a class="discover-button" href="{{ route('games.play', $game->slug) }}">جرّب مجاناً · أول {{ \App\Support\GameTrial::size() }} ←</a><a class="discover-link-secondary" href="{{ $subscribeUrl }}">افتح اللعبة كاملة بالاشتراك</a>@else<a class="discover-button" href="{{ $subscribeUrl }}">اشترك والعب ←</a>@endif</div><p class="detail-note">{{ !$game->is_free && $game->price > 0 ? \App\Support\Money::egp($game->price).' ج.م للعبة • أو ضمن باقة كل الألعاب' : 'متاحة ضمن باقة كل الألعاب' }}</p></div></section>
<section class="preview-band"><div><span class="discover-eyebrow">لمحة عن التجربة</span><h2>{{ $teaser[2] }}</h2><p>{{ $teaser[3] }}</p><p class="detail-note">{{ $unlocked ? 'اللعبة كاملة متاحة ليك.' : ($trialOpen ? 'جرّبوا أول '.\App\Support\GameTrial::size().' مجانًا من غير حساب، وكمّلوا الباقي بالاشتراك.' : 'اللعبة دي بتتفتح كاملة مع الاشتراك.') }}</p></div><div class="locked-previews"><div><span>01</span><strong>اختاروا البداية</strong><small>كل تجربة ليها حكايتها</small></div><a href="{{ $unlocked ? route('games.play', $game->slug) : $subscribeUrl }}"><span>♧</span><strong>{{ $unlocked ? 'جاهزين تكتشفوا؟' : 'المفاجأة جوّه' }}</strong><small>{{ $unlocked ? 'افتح اللعبة' : 'تتفتح مع اشتراكك' }}</small></a></div></section>
@php($steps = collect(preg_split('/\R/u', trim((string) $game->how_to_play)))->filter())
@if($steps->isNotEmpty())<section class="membership-note"><h2>طريقة اللعب</h2><ol class="list-decimal list-inside space-y-3 mt-4">@foreach($steps as $step)<li>{{ $step }}</li>@endforeach</ol><p>♡ أي سؤال أو مهمة ممكن تتخطّوها من غير تبرير؛ اختاروا ما يناسبكم أنتم الاتنين.</p></section>@endif
@if(!$unlocked)<section class="final-invite"><h2>عجبتكم الفكرة؟ الباقي جوّه.</h2><p>الدفع مرة واحدة، والتفعيل بعد مراجعة الإيصال والموافقة.</p><a class="discover-button" href="{{ $subscribeUrl }}">اختار اشتراكك ←</a></section>@endif
</div>
</x-app-layout>
