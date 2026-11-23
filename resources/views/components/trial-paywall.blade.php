@props(['trial', 'game'])
@if($trial)
    <section {{ $attributes->merge(['class' => 'trial-paywall']) }} aria-labelledby="trial-paywall-title">
        <p class="trial-paywall-eyebrow">خلصتوا التجربة المجانية</p>
        <h2 id="trial-paywall-title" class="trial-paywall-title">الحكاية لسه في أولها</h2>
        <p class="trial-paywall-text">
            لعبتوا {{ $trial['shown'] }}@if($trial['total']) من {{ $trial['total'] }}@endif في «{{ $game->name }}».
            اشتركوا مرة واحدة وافتحوا اللعبة كاملة، من غير تجديد تلقائي.
        </p>
        <div class="trial-paywall-actions">
            <a href="{{ $trial['subscribe_url'] }}" class="trial-paywall-primary">افتح اللعبة كاملة · {{ $trial['price'] }} ج.م</a>
            <a href="{{ $trial['bundle_url'] }}" class="trial-paywall-secondary">أو كل الألعاب في باقة واحدة</a>
        </div>
    </section>
@endif
