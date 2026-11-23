@props(['trial'])
@if($trial)
    <div class="trial-banner" role="note">
        <span class="trial-banner-tag">تجربة مجانية</span>
        <span class="trial-banner-text">بتلعبوا أول {{ $trial['shown'] }} من اللعبة. عجبتكم؟ كمّلوا الباقي بالاشتراك.</span>
        <a href="{{ $trial['subscribe_url'] }}" class="trial-banner-link">اشترك بـ {{ $trial['price'] }} ج.م</a>
    </div>
@endif
@if($trial)
    <script>globalThis.gameTrial = true;</script>
@endif
