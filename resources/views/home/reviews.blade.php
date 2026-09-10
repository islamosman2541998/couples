@if($homeSettings['reviews_enabled'] && count($reviews))
<section class="home-reviews" aria-labelledby="reviews-heading">
    <div class="home-section-heading"><div><span class="home-kicker">من شات الواتساب… ليكم ♡</span><h2 id="reviews-heading">{{ $homeSettings['reviews_title'] }}</h2></div><span class="review-note">كلامهم بيفرحنا</span></div>
    <div class="swiper reviews-swiper" data-review-slider dir="rtl"><div class="swiper-wrapper">
        @foreach($reviews as $review)
        <article class="swiper-slide review-card"><div class="review-person"><span class="review-avatar">{{ mb_substr($review['title'], 0, 1) }}</span><div><h3>{{ $review['title'] }}</h3><span>رسالة من عميلنا</span></div><span class="review-quote" aria-hidden="true">”</span></div>
            <button type="button" class="review-image" data-review-image="{{ $review['image'] }}" data-review-name="{{ $review['title'] }}" aria-label="تكبير رأي {{ $review['title'] }}"><img src="{{ $review['image'] }}" alt="رأي {{ $review['title'] }}" loading="lazy"><span>اضغط لقراءة الرسالة ↗</span></button>
        </article>
        @endforeach
    </div><div class="home-slider-controls"><button class="home-prev" aria-label="الرأي السابق">→</button><div class="home-pagination"></div><button class="home-next" aria-label="الرأي التالي">←</button></div></div>
</section>
<dialog class="review-dialog" aria-label="صورة رأي العميل"><button type="button" data-close-review aria-label="إغلاق الصورة">×</button><img alt=""></dialog>
@endif
@if($homeSettings['notifications_enabled'] && $notificationGames->isNotEmpty() && count($homeSettings['names']))
<aside class="purchase-toast purchase-toast-{{ $homeSettings['position'] }}" data-purchase-toast hidden aria-label="اقتراح لعبة">
    <button class="purchase-close" aria-label="إخفاء الإشعارات لهذه الزيارة">×</button><span class="purchase-icon" aria-hidden="true">🎮</span><div class="purchase-copy"><small>نشاط ترويجي</small><p>اشترى <strong data-buyer></strong> الآن</p><a data-purchase-game></a></div><span class="purchase-dot" aria-hidden="true"></span>
</aside>
@push('scripts')
<script type="application/json" id="home-notifications-data">{!! json_encode(['names' => $homeSettings['names'], 'games' => $notificationGames, 'duration' => $homeSettings['duration'], 'interval' => $homeSettings['interval']], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
@endif
