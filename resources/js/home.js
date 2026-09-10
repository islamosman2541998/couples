import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, A11y, Keyboard } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import '../css/home.css';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.querySelectorAll('[data-home-slider], [data-review-slider]').forEach((element) => {
    const hero = element.hasAttribute('data-home-slider');
    const count = element.querySelectorAll('.swiper-slide').length;
    const delay = Number(element.dataset.autoplay || 0);
    const swiper = new Swiper(element, {
        modules: [Navigation, Pagination, Autoplay, A11y, Keyboard],
        slidesPerView: hero ? 1.08 : 1.12, spaceBetween: hero ? 20 : 16,
        // Centered fractional slides require five items in Swiper's loop buffer.
        centeredSlides: hero, loop: hero && count >= 5, rewind: hero && count < 5, watchOverflow: true,
        speed: reducedMotion ? 0 : 650, grabCursor: count > 1,
        autoplay: delay && count > 1 ? { enabled: !reducedMotion, delay, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
        keyboard: { enabled: true, onlyInViewport: true },
        navigation: { nextEl: element.querySelector('.home-next'), prevEl: element.querySelector('.home-prev') },
        pagination: { el: element.querySelector('.home-pagination'), clickable: true },
        a11y: { prevSlideMessage: 'الشريحة السابقة', nextSlideMessage: 'الشريحة التالية', paginationBulletMessage: 'انتقل للشريحة {{index}}' },
        breakpoints: hero ? { 768: { slidesPerView: 1.24, spaceBetween: 28 } } : { 640: { slidesPerView: 2, spaceBetween: 20 }, 1024: { slidesPerView: 3, spaceBetween: 24 } },
    });
    const pause = element.querySelector('[data-slider-pause]');
    if (pause) {
        let paused = reducedMotion;
        const sync = () => { pause.textContent = paused ? '▶' : 'Ⅱ'; pause.setAttribute('aria-pressed', String(paused)); pause.setAttribute('aria-label', paused ? 'تشغيل التقليب التلقائي' : 'إيقاف التقليب التلقائي'); };
        sync();
        pause.addEventListener('click', () => { paused = !paused; paused ? swiper.autoplay.stop() : swiper.autoplay.start(); sync(); });
        element.addEventListener('focusin', () => swiper.autoplay.stop());
        element.addEventListener('focusout', (event) => { if (!element.contains(event.relatedTarget) && !paused) swiper.autoplay.start(); });
    }
});

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const key = `home-offer-${element.dataset.version}`;
    let deadline = element.dataset.deadline ? Date.parse(element.dataset.deadline) : null;
    if (!deadline) {
        try { deadline = Number(localStorage.getItem(key)) || null; } catch {}
        if (!deadline) {
            deadline = Date.now() + Number(element.dataset.hours) * 3600000;
            try { localStorage.setItem(key, String(deadline)); } catch {}
        }
    }
    const tick = () => {
        const seconds = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
        element.hidden = seconds === 0;
        element.querySelector('[data-hours]').textContent = String(Math.floor(seconds / 3600)).padStart(2, '0');
        element.querySelector('[data-minutes]').textContent = String(Math.floor(seconds / 60) % 60).padStart(2, '0');
        element.querySelector('[data-seconds]').textContent = String(seconds % 60).padStart(2, '0');
        return seconds;
    };
    if (tick()) { const timer = setInterval(() => { if (!tick()) clearInterval(timer); }, 1000); }
});

const dialog = document.querySelector('.review-dialog');
if (dialog) {
    document.querySelectorAll('[data-review-image]').forEach(button => button.addEventListener('click', () => {
        dialog.querySelector('img').src = button.dataset.reviewImage;
        dialog.querySelector('img').alt = `رأي ${button.dataset.reviewName}`;
        dialog.showModal();
    }));
    dialog.querySelector('[data-close-review]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
}

const toast = document.querySelector('[data-purchase-toast]');
const dataElement = document.getElementById('home-notifications-data');
if (toast && dataElement) {
    const data = JSON.parse(dataElement.textContent);
    let dismissed = false;
    try { dismissed = sessionStorage.getItem('home-toast-dismissed') === '1'; } catch {}
    let bag = [], timer;
    const nextName = () => {
        if (!bag.length) {
            bag = [...data.names];
            for (let i = bag.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [bag[i], bag[j]] = [bag[j], bag[i]]; }
        }
        return bag.pop();
    };
    const show = () => {
        if (dismissed || document.hidden) return;
        const game = data.games[Math.floor(Math.random() * data.games.length)];
        toast.querySelector('[data-buyer]').textContent = nextName();
        const link = toast.querySelector('[data-purchase-game]'); link.textContent = game.name; link.href = game.url;
        toast.hidden = false;
        timer = setTimeout(() => { toast.hidden = true; timer = setTimeout(show, data.interval * 1000); }, data.duration * 1000);
    };
    toast.querySelector('.purchase-close').addEventListener('click', () => {
        dismissed = true; clearTimeout(timer); toast.hidden = true;
        try { sessionStorage.setItem('home-toast-dismissed', '1'); } catch {}
    });
    document.addEventListener('visibilitychange', () => {
        clearTimeout(timer); toast.hidden = true;
        if (!document.hidden && !dismissed) timer = setTimeout(show, data.interval * 1000);
    });
    if (!dismissed && data.names.length && data.games.length) timer = setTimeout(show, 4500);
}
