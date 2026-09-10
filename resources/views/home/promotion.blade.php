@if($homeSettings['countdown_enabled'])
@php
    $deadline = $homeSettings['countdown_mode'] === 'fixed' && $homeSettings['ends_at']
        ? \Carbon\Carbon::parse($homeSettings['ends_at'], config('app.timezone'))->toIso8601String() : null;
@endphp
<section class="home-offer" data-countdown data-hours="{{ $homeSettings['hours'] }}" data-deadline="{{ $deadline }}" data-version="{{ $homeSettings['countdown_version'] }}" aria-label="العرض الحالي">
    <div class="offer-copy"><span class="home-kicker">✦ عرض لفترة محدودة</span><h2>{{ $homeSettings['countdown_title'] }}</h2><p>{{ $homeSettings['countdown_text'] }}</p></div>
    <div class="offer-action"><div class="countdown-clock" dir="ltr" aria-label="الوقت المتبقي">
        <div><strong data-hours>00</strong><span>ساعة</span></div><b>:</b><div><strong data-minutes>00</strong><span>دقيقة</span></div><b>:</b><div><strong data-seconds>00</strong><span>ثانية</span></div>
    </div><a class="home-cta" href="{{ $homeSettings['offer_url'] ?: '#premium-games' }}">{{ $homeSettings['offer_label'] }} <span aria-hidden="true">←</span></a></div>
</section>
@endif
