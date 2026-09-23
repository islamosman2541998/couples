// First-party analytics: no form values, question content or external URL queries.
const endpoint = document.querySelector('meta[name="analytics-endpoint"]')?.content;
if (endpoint) {
    const send = (name, detail = null) => {
        const payload = {name, path: location.pathname, detail};
        if (name === 'heartbeat') {
            payload.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            payload.screen = `${screen.width}x${screen.height}`;
        }
        fetch(endpoint, {method:'POST', credentials:'same-origin', keepalive:true,
            headers:{'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            body:JSON.stringify(payload)}).catch(() => {});
    };
    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (link) {
            const url = new URL(link.href, location.origin);
            if (url.hostname === 'wa.me') send('whatsapp_click');
            else if (url.origin === location.origin && (url.pathname === '/membership' || /^\/subscribe\/\d+$/.test(url.pathname) || url.hash === '#plans')) send('subscribe_click');
        }
        const mood = event.target.closest('[data-analytics-mood]');
        if (mood) send('mood_select', mood.dataset.analyticsMood);
    });
    const heartbeat = () => { if (document.visibilityState === 'visible') send('heartbeat'); };
    heartbeat();
    setInterval(heartbeat, 30000);
}
