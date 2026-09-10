<x-app-layout>
    <x-slot name="title">الاشتراك في {{ $game->name }}</x-slot>
    @php
        $checkoutData = [
            'user' => auth()->user()?->only(['name', 'email', 'phone']),
            'gameId' => $game->id,
            'games' => $paidGames->map(fn ($item) => $item->only(['id', 'name', 'price']))->values(),
        ];
    @endphp
    <div class="checkout-page max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-14" x-data="checkout({{ \Illuminate\Support\Js::from($checkoutData) }})">
        <div class="text-center mb-8">
            <span class="inline-block rounded-full bg-purple-500/10 border border-purple-500/20 px-4 py-2 text-sm text-purple-300 mb-4">خطوتين… وتبدأ الحكاية ♡</span>
            <h1 class="text-2xl sm:text-3xl font-black mb-3">الاشتراك في <span x-text="currentGame?.name">{{ $game->name }}</span></h1>
            <p class="text-gray-400 text-sm">سجّل بياناتك، حوّل المبلغ وارفع الإيصال من نفس الصفحة.</p>
        </div>

        <div x-show="!success">
            <section class="checkout-card mb-6" aria-labelledby="checkout-account-heading">
                <div class="flex items-center gap-3 mb-5"><span class="checkout-step">1</span><div><h2 id="checkout-account-heading" class="font-bold text-lg">حسابك أولاً</h2><p class="text-gray-400 text-xs mt-1">علشان تلاقي لعبتك واشتراكك في مكان واحد</p></div></div>
                <div x-show="!user" @if(auth()->check()) x-cloak @endif>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-gray-950 rounded-xl mb-5" role="group" aria-label="نوع الحساب">
                        <button type="button" @click="switchMode('register')" :disabled="authBusy" :aria-pressed="mode === 'register'" :class="mode === 'register' ? 'bg-purple-600 text-white shadow-lg' : 'text-gray-400'" class="rounded-lg py-3 font-bold text-sm transition-colors">مستخدم جديد</button>
                        <button type="button" @click="switchMode('login')" :disabled="authBusy" :aria-pressed="mode === 'login'" :class="mode === 'login' ? 'bg-purple-600 text-white shadow-lg' : 'text-gray-400'" class="rounded-lg py-3 font-bold text-sm transition-colors">لديك حساب</button>
                    </div>
                    <div x-show="authErrors.length" x-cloak class="checkout-error" role="alert"><template x-for="(error, index) in authErrors" :key="index"><p x-text="error"></p></template></div>
                    <form x-show="mode === 'register'" action="{{ route('checkout.register') }}" method="POST" @submit.prevent="authenticate($el)" class="space-y-4">
                        @csrf
                        <fieldset :disabled="authBusy" class="space-y-4">
                            <label class="checkout-label">الاسم الكامل<input class="checkout-input" name="name" autocomplete="name" maxlength="255" required placeholder="اكتب اسمك"></label>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <label class="checkout-label">البريد الإلكتروني<input class="checkout-input" type="email" name="email" autocomplete="email" maxlength="255" dir="ltr" required placeholder="name@example.com"></label>
                                <label class="checkout-label">رقم الموبايل<input class="checkout-input" type="tel" name="phone" autocomplete="tel" maxlength="20" dir="ltr" required placeholder="01xxxxxxxxx"></label>
                            </div>
                            <label class="checkout-label">كلمة المرور<input class="checkout-input" type="password" name="password" autocomplete="new-password" minlength="8" required placeholder="8 أحرف على الأقل"></label>
                            <button class="checkout-submit" type="submit" x-text="authBusy ? 'جارٍ حفظ الحساب…' : 'حفظ الحساب ومتابعة الدفع ←'">حفظ الحساب ومتابعة الدفع ←</button>
                        </fieldset>
                    </form>
                    <form x-show="mode === 'login'" x-cloak action="{{ route('checkout.login') }}" method="POST" @submit.prevent="authenticate($el)" class="space-y-4">
                        @csrf
                        <fieldset :disabled="authBusy" class="space-y-4">
                            <label class="checkout-label">البريد الإلكتروني<input class="checkout-input" type="email" name="email" autocomplete="username" dir="ltr" required></label>
                            <label class="checkout-label">كلمة المرور<input class="checkout-input" type="password" name="password" autocomplete="current-password" required></label>
                            <a href="{{ route('password.request') }}" target="_blank" rel="noopener" class="inline-block text-xs text-purple-300">نسيت كلمة المرور؟</a>
                            <button class="checkout-submit" type="submit" x-text="authBusy ? 'جارٍ تسجيل الدخول…' : 'تسجيل الدخول ومتابعة الدفع ←'">تسجيل الدخول ومتابعة الدفع ←</button>
                        </fieldset>
                    </form>
                </div>
                <div x-show="user" @if(!auth()->check()) x-cloak @endif class="rounded-xl bg-green-500/10 border border-green-500/20 p-4">
                    <p class="text-green-300 font-bold">✓ أهلاً <span x-text="user?.name">{{ auth()->user()?->name }}</span>، حسابك جاهز</p>
                    <p class="text-gray-400 text-sm mt-1 break-all" x-text="user?.email">{{ auth()->user()?->email }}</p>
                    <p class="text-sm mt-2 text-gray-300">كمّل التحويل وارفع الإيصال بالأسفل.</p>
                </div>
            </section>

            <section x-ref="payment" class="checkout-card scroll-mt-24" aria-labelledby="checkout-payment-heading">
                <div class="flex items-center gap-3 mb-5"><span class="checkout-step">2</span><div><h2 id="checkout-payment-heading" class="font-bold text-lg">الدفع وتأكيد الاشتراك</h2><p class="text-gray-400 text-xs mt-1">حوّل المبلغ ثم ارفع صورة الإيصال للمراجعة</p></div></div>
                <label class="checkout-label mb-5">اللعبة المراد الاشتراك فيها<select class="checkout-input" x-model="selectedGame" :disabled="paymentBusy">@foreach($paidGames as $item)<option value="{{ $item->id }}" @selected($item->id === $game->id)>{{ $item->name }} — {{ number_format($item->price, 2) }}</option>@endforeach</select></label>
                <div class="rounded-xl bg-blue-900/20 border border-blue-500/25 p-5 mb-6">
                    <h3 class="text-blue-300 font-bold mb-3">بيانات التحويل · فودافون كاش</h3>
                    <p class="text-gray-400 text-xs mb-2">رقم التحويل</p>
                    <p class="font-mono text-lg font-bold break-all bg-gray-950/50 rounded-lg px-3 py-2" dir="ltr">{{ \App\Models\Setting::get('bank_account', '') }}</p>
                    <div class="flex items-center justify-between gap-4 mt-4"><span class="text-sm text-gray-300">المبلغ المطلوب</span><strong class="text-2xl text-yellow-300" x-text="amount">{{ number_format($game->price, 2) }}</strong></div>
                </div>
                <p x-show="!user" class="text-amber-300 text-sm mb-4">أنشئ حساباً أو سجّل الدخول بالأعلى لتكمل إرسال الإيصال.</p>
                <div x-show="paymentErrors.length" x-cloak class="checkout-error" role="alert"><template x-for="(error, index) in paymentErrors" :key="index"><p x-text="error"></p></template></div>
                <form method="POST" action="{{ route('subscribe.store') }}" enctype="multipart/form-data" @submit.prevent="submitPayment($el)">
                    @csrf
                    <input type="hidden" name="game_id" :value="selectedGame" value="{{ $game->id }}">
                    <fieldset :disabled="!user || paymentBusy" class="space-y-4 disabled:opacity-60">
                        <label class="checkout-label">الاسم الكامل<input class="checkout-input" name="full_name" x-model="details.full_name" autocomplete="name" maxlength="255" required></label>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="checkout-label">رقم الموبايل<input class="checkout-input" name="phone" x-model="details.phone" type="tel" autocomplete="tel" maxlength="20" dir="ltr" required></label>
                            <label class="checkout-label">البريد الإلكتروني<input class="checkout-input" name="email" x-model="details.email" type="email" autocomplete="email" maxlength="255" dir="ltr" required></label>
                        </div>
                        <label class="checkout-label">صورة إيصال التحويل
                            <span class="block border-2 border-dashed border-purple-500/30 rounded-xl p-5 mt-2 text-center bg-purple-500/5">
                                <span class="block text-3xl mb-2" aria-hidden="true">📸</span>
                                <span class="block text-xs text-gray-400 mb-3">JPG، PNG، WEBP — حتى 5 ميجابايت</span>
                                <input type="file" name="receipt_image" accept="image/jpeg,image/png,image/webp" @change="chooseReceipt($event)" class="block w-full text-xs text-gray-300 file:rounded-lg file:border-0 file:bg-purple-600 file:text-white file:px-3 file:py-2 file:ml-3" required>
                                <img x-show="preview" x-cloak :src="preview" alt="معاينة إيصال التحويل" class="max-h-56 max-w-full mx-auto rounded-xl mt-4">
                            </span>
                        </label>
                        <button type="submit" class="checkout-submit" x-text="paymentBusy ? 'جارٍ رفع الإيصال…' : 'إرسال طلب الاشتراك ✓'">إرسال طلب الاشتراك ✓</button>
                    </fieldset>
                </form>
            </section>
        </div>
        <section x-show="success" x-cloak x-ref="success" tabindex="-1" role="status" class="checkout-card text-center">
            <div class="text-5xl mb-5">✓</div><h2 class="text-2xl font-black text-green-300 mb-4">تم إرسال طلبك بنجاح!</h2>
            <p class="text-gray-300 leading-relaxed mb-6" x-text="successMessage"></p>
            <a href="{{ route('profile.index') }}" class="checkout-submit inline-block">متابعة طلباتي من ملفي الشخصي ←</a>
        </section>
        <noscript><p class="checkout-error">فعّل JavaScript لإتمام إنشاء الحساب ورفع الإيصال داخل الصفحة.</p></noscript>
    </div>
</x-app-layout>
