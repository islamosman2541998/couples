export function checkout(config) {
    return {
        user: config.user, mode: 'register', authBusy: false, paymentBusy: false,
        authErrors: [], paymentErrors: [], success: false, successMessage: '', preview: null,
        selectedGame: String(config.gameId), games: config.games,
        details: { full_name: config.user?.name || '', email: config.user?.email || '', phone: config.user?.phone || '' },
        get currentGame() { return this.games.find(game => String(game.id) === this.selectedGame); },
        get amount() { return Number(this.currentGame?.price || 0).toFixed(2); },
        switchMode(mode) { this.mode = mode; this.authErrors = []; },
        async request(url, body) {
            const response = await fetch(url, {
                method: 'POST', credentials: 'same-origin', body,
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            let data;
            try { data = await response.json(); } catch { data = {}; }
            if (!response.ok || response.redirected) {
                const messages = data.errors ? Object.values(data.errors).flat() : [
                    response.status === 419 ? 'انتهت صلاحية الجلسة. افتح صفحة الدفع من جديد ثم حاول مرة أخرى.' :
                    response.status === 429 ? 'محاولات كثيرة. انتظر دقيقة ثم حاول مجدداً.' :
                    response.status === 401 ? 'سجّل الدخول أولاً لإرسال طلب الاشتراك.' :
                    'تعذر إتمام الطلب. تحقق من الاتصال وحاول مرة أخرى.',
                ];
                throw { messages, status: response.status };
            }
            return data;
        },
        async authenticate(form) {
            if (this.authBusy) return;
            const body = new FormData(form);
            this.authBusy = true; this.authErrors = [];
            try {
                const data = await this.request(form.action, body);
                this.user = data.user;
                window.dispatchEvent(new CustomEvent('checkout-authenticated', { detail: data.user }));
                this.details = { full_name: data.user.name, email: data.user.email, phone: data.user.phone || this.details.phone };
                document.querySelector('meta[name="csrf-token"]').content = data.csrf_token;
                document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = data.csrf_token; });
                form.querySelectorAll('input[type="password"]').forEach(input => { input.value = ''; });
                this.$nextTick(() => this.$refs.payment.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start' }));
            } catch (error) { this.authErrors = error.messages || ['تعذر الاتصال. حاول مرة أخرى.']; }
            finally { this.authBusy = false; }
        },
        chooseReceipt(event) {
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = null; this.paymentErrors = [];
            const file = event.target.files[0];
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
                this.paymentErrors = ['اختر صورة JPG أو PNG أو WEBP بحجم لا يتجاوز 5 ميجابايت.'];
                event.target.value = ''; return;
            }
            this.preview = URL.createObjectURL(file);
        },
        async submitPayment(form) {
            if (this.paymentBusy || this.success) return;
            if (!this.user) { this.paymentErrors = ['أنشئ حساباً أو سجّل الدخول أولاً.']; return; }
            const body = new FormData(form);
            this.paymentBusy = true; this.paymentErrors = [];
            try {
                const data = await this.request(form.action, body);
                this.successMessage = data.message; this.success = true;
                if (this.preview) URL.revokeObjectURL(this.preview);
                this.preview = null; form.reset();
                this.$nextTick(() => { this.$refs.success.focus(); this.$refs.success.scrollIntoView({ behavior: 'smooth', block: 'center' }); });
            } catch (error) {
                this.paymentErrors = error.messages || ['تعذر الاتصال. حاول مرة أخرى.'];
                if (error.status === 401) this.user = null;
            } finally { this.paymentBusy = false; }
        },
        destroy() { if (this.preview) URL.revokeObjectURL(this.preview); },
    };
}
