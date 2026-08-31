document.addEventListener('DOMContentLoaded', () => {
    const room = document.querySelector('[data-pet-room]');
    const canvas = document.querySelector('[data-bubble-canvas]');
    const pets = [...document.querySelectorAll('[data-pet-id]')];
    if (!room || !canvas) return;
    const ctx = canvas.getContext('2d');
    let bubbles = [];
    const resize = () => {
        canvas.width = room.clientWidth * devicePixelRatio;
        canvas.height = room.clientHeight * devicePixelRatio;
        ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
    };
    resize();
    window.addEventListener('resize', resize);
    const burst = (x, y, symbol) => { for (let i = 0; i < 12; i++) bubbles.push({ x, y, vx: (Math.random() - .5) * 2, vy: -1 - Math.random() * 2, life: 1, symbol }); };
    const draw = () => {
        ctx.clearRect(0, 0, room.clientWidth, room.clientHeight);
        bubbles = bubbles.filter(item => item.life > 0);
        bubbles.forEach(item => {
            item.x += item.vx;
            item.y += item.vy;
            item.life -= .018;
            ctx.globalAlpha = item.life;
            ctx.font = '18px serif';
            ctx.fillText(item.symbol, item.x, item.y);
        });
        ctx.globalAlpha = 1;
        requestAnimationFrame(draw);
    };
    draw();
    const update = (card, pet) => {
        ['hunger', 'hygiene', 'happiness', 'energy', 'level', 'exp'].forEach(stat => { const value = Number(pet[stat] || 0); const text = card.querySelector(`[data-stat="${stat}"]`); if (text) text.textContent = value; const bar = card.querySelector(`[data-stat-bar="${stat}"]`); if (bar) bar.style.width = `${value}%`; });
        const mood = card.querySelector('[data-pet-mood]');
        if (mood) mood.textContent = pet.mood;
    };
    const action = async(card, actionName, event) => {
        const button = event.currentTarget;
        button.disabled = true;
        card.classList.add('is-active');
        const rect = card.getBoundingClientRect();
        burst(rect.left + rect.width / 2 - room.getBoundingClientRect().left, 100, actionName === 'bath' ? '✦' : actionName === 'sleep' ? 'Z' : actionName === 'pet' ? '♥' : '🐟');
        if (actionName === 'sleep') room.classList.add('is-sleeping');
        if (navigator.vibrate && actionName === 'pet') navigator.vibrate(20);
        try {
            const data = new FormData();
            data.append('pet_id', card.dataset.petId);
            data.append('action', actionName);
            data.append('csrf_token', window.MONTHSARY_CSRF || '');
            const response = await fetch((window.MONTHSARY_BASE_PATH || '') + '/pages/api_pet_action.php', { method: 'POST', body: data });
            const result = await response.json();
            if (!result.ok) throw new Error(result.message);
            update(card, result.pet);
            const feedback = card.querySelector('[data-pet-feedback]');
            feedback.textContent = actionName === 'feed' ? 'Yum!' : actionName === 'bath' ? 'Sparkly!' : actionName === 'sleep' ? 'Zzz...' : 'Purr...';
            setTimeout(() => { feedback.textContent = ''; if (actionName === 'sleep') room.classList.remove('is-sleeping'); }, 1800);
        } catch (error) {
            const feedback = card.querySelector('[data-pet-feedback]');
            feedback.textContent = error.message;
            room.classList.remove('is-sleeping');
        } finally {
            setTimeout(() => {
                card.classList.remove('is-active');
                button.disabled = false;
            }, 700);
        }
    };
    pets.forEach(card => {
        card.querySelectorAll('[data-pet-action]').forEach(button => button.addEventListener('click', event => action(card, button.dataset.petAction, event)));
        const avatar = card.querySelector('[data-pet-avatar]');
        let last = 0;
        avatar.addEventListener('pointermove', event => {
            if (event.buttons && Date.now() - last > 180) {
                last = Date.now();
                action(card, 'pet', { currentTarget: { disabled: false } });
            }
        });
    });
});