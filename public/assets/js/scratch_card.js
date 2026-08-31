document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-scratch-card]').forEach(card => {
        const canvas = card.querySelector('canvas'),
            ctx = canvas.getContext('2d');
        let drawing = false,
            scratched = 0;
        const resize = () => { canvas.width = card.clientWidth * devicePixelRatio;
            canvas.height = card.clientHeight * devicePixelRatio;
            ctx.scale(devicePixelRatio, devicePixelRatio);
            ctx.fillStyle = '#c79a9e';
            ctx.fillRect(0, 0, card.clientWidth, card.clientHeight);
            ctx.fillStyle = '#fff0f2';
            ctx.font = '600 15px Poppins';
            ctx.textAlign = 'center';
            ctx.fillText('Scratch to reveal', card.clientWidth / 2, card.clientHeight / 2) };
        resize();
        const scratch = e => { if (!drawing) return; const r = canvas.getBoundingClientRect(),
                x = e.clientX - r.left,
                y = e.clientY - r.top;
            ctx.globalCompositeOperation = 'destination-out';
            ctx.beginPath();
            ctx.arc(x, y, 22, 0, Math.PI * 2);
            ctx.fill();
            scratched++; if (scratched > 35) canvas.style.display = 'none' };
        canvas.addEventListener('pointerdown', e => { drawing = true;
            canvas.setPointerCapture(e.pointerId);
            scratch(e) });
        canvas.addEventListener('pointermove', scratch);
        canvas.addEventListener('pointerup', () => drawing = false);
        canvas.addEventListener('pointercancel', () => drawing = false);
    });
});