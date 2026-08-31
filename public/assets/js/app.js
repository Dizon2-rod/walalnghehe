document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.anniversary-page');
    const countdown = document.querySelector('[data-anniversary-countdown]');
    if (countdown && page) {
        const target = new Date(page.dataset.unlockAt).getTime();
        const tick = () => {
            const left = Math.max(0, target - Date.now());
            const d = Math.floor(left / 86400000);
            const h = Math.floor(left % 86400000 / 3600000);
            const m = Math.floor(left % 3600000 / 60000);
            const s = Math.floor(left % 60000 / 1000);
            countdown.textContent = `${String(d).padStart(2,'0')} : ${String(h).padStart(2,'0')} : ${String(m).padStart(2,'0')} : ${String(s).padStart(2,'0')}`;
        };
        tick();
        setInterval(tick, 1000);
    }
    const seal = document.querySelector('[data-seal]');
    if (seal) seal.addEventListener('click', () => {
        const envelope = document.querySelector('[data-envelope]');
        const content = document.querySelector('[data-reveal-content]');
        envelope.classList.add('open');
        if (window.heartBurst) window.heartBurst();
        setTimeout(() => {
            content.hidden = false;
            document.querySelector('[data-unboxing-stage]').hidden = true;
            typewriter();
            startMusic();
        }, 850);
    }, { once: true });

    function typewriter() {
        const el = document.querySelector('[data-typewriter]');
        if (!el) return;
        const text = el.textContent;
        el.textContent = '';
        let i = 0;
        const write = () => {
            if (i < text.length) {
                el.textContent += text[i++];
                setTimeout(write, 28);
            }
        };
        write();
    }

    function startMusic() {
        const audio = document.querySelector('[data-background-music]');
        const button = document.querySelector('[data-music-toggle]');
        if (!audio || !button) return;
        audio.volume = .35;
        button.onclick = () => {
            if (audio.paused) {
                audio.play();
                button.textContent = 'Pause';
            } else {
                audio.pause();
                button.textContent = 'Play';
            }
        };
        const volume = document.querySelector('[data-music-volume]');
        if (volume) volume.addEventListener('input', e => audio.volume = e.target.value);
    }
    const reply = document.querySelector('[data-reply-form]');
    if (reply) reply.addEventListener('submit', async e => {
        e.preventDefault();
        const data = new FormData(reply);
        data.append('id', page.dataset.giftId);
        data.append('csrf_token', window.MONTHSARY_CSRF);
        const result = await fetch((window.MONTHSARY_BASE_PATH || '') + '/pages/reply_gift.php', { method: 'POST', body: data });
        const json = await result.json();
        document.querySelector('[data-reply-status]').textContent = json.message;
        if (json.ok) reply.reset();
    });
    document.querySelectorAll('[data-redeem]').forEach(button => button.addEventListener('click', async e => {
        const card = e.target.closest('[data-scratch-card]');
        const data = new FormData();
        data.append('id', page.dataset.giftId);
        data.append('coupon_id', card.dataset.couponId);
        data.append('csrf_token', window.MONTHSARY_CSRF);
        const json = await (await fetch((window.MONTHSARY_BASE_PATH || '') + '/pages/redeem_coupon.php', { method: 'POST', body: data })).json();
        e.target.textContent = json.ok ? 'Redeemed' : 'Already redeemed';
        e.target.disabled = true;
    }));
    // Countdown to next monthsary (14th day as example)
    const el = document.querySelector('[data-countdown]');
    if (el) {
        function next14th() {
            const now = new Date();
            let target = new Date(now.getFullYear(), now.getMonth(), 14, 0, 0, 0);
            if (now > target) target = new Date(now.getFullYear(), now.getMonth() + 1, 14, 0, 0, 0);
            return target;
        }

        function tick() {
            const t = next14th() - new Date();
            const d = Math.floor(t / 86400000);
            const h = Math.floor((t % 86400000) / 3600000);
            const m = Math.floor((t % 3600000) / 60000);
            el.textContent = `${d}d ${h}h ${m}m`;
        }
        setInterval(tick, 1000 * 30);
        tick();
    }
});