@if($homeSettings['slider_enabled'] && count($slides))
<section class="home-hero" aria-label="العروض والألعاب">
    <div class="home-eyebrow"><span></span> وقتكم سوا يستاهل لعبة حلوة</div>
    <div class="swiper home-swiper" data-home-slider data-autoplay="{{ $homeSettings['autoplay'] ? $homeSettings['delay'] : 0 }}" dir="rtl">
        <div class="swiper-wrapper">
            @foreach($slides as $slide)
            <div class="swiper-slide">
                <a class="home-slide {{ empty($slide['image']) ? 'home-slide-art' : '' }}" href="{{ $slide['url'] ?: '#games' }}">
                    @if(!empty($slide['image']))
                        <picture>
                            @if(!empty($slide['mobile_image']))<source media="(max-width: 640px)" srcset="{{ $slide['mobile_image'] }}">@endif
                            <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" width="900" height="470" {{ $loop->first ? 'fetchpriority=high' : 'loading=lazy' }}>
                        </picture>
                    @else
                        <span class="slide-suit" aria-hidden="true">♥</span>
                        <div class="slide-copy"><span class="home-kicker">ضحك • تحديات • ذكريات</span><h1>{{ $slide['title'] }}</h1><p>سيبوا الروتين برّه… وابدأوا حكاية جديدة سوا.</p><span class="home-cta">اختاروا لعبتكم <span aria-hidden="true">←</span></span></div>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
        <div class="home-slider-controls">
            @if($homeSettings['arrows'])<button class="home-prev" aria-label="الشريحة السابقة">→</button>@endif
            @if($homeSettings['dots'])<div class="home-pagination"></div>@endif
            @if($homeSettings['arrows'])<button class="home-next" aria-label="الشريحة التالية">←</button>@endif
            @if($homeSettings['autoplay'] && count($slides) > 1)<button data-slider-pause aria-label="إيقاف التقليب التلقائي" aria-pressed="false">Ⅱ</button>@endif
        </div>
    </div>
    <div class="home-benefits"><span>✦ لحظات مختلفة</span><span>♡ أقرب لبعض</span><span>↗ العب من موبايلك</span></div>
</section>
@endif
