(function() {
    const hearts = [];
    const colors = ['#efbbcf', '#cdb4db', '#f7e1e9', '#ffdfe9'];
    const num = 24;
    const container = document.createElement('div');
    container.style.position = 'fixed';
    container.style.pointerEvents = 'none';
    container.style.inset = '0';
    container.style.zIndex = '9999';
    document.addEventListener('DOMContentLoaded', () => {
        document.body.appendChild(container);
        for (let i = 0; i < num; i++) spawnHeart();
        requestAnimationFrame(tick);
    });
    window.heartBurst = function() { for (let i = 0; i < 32; i++) { const el = document.createElement('div');
            el.textContent = '♥';
            el.style.cssText = `position:fixed;left:50%;top:45%;z-index:10000;pointer-events:none;color:${colors[i%colors.length]};font-size:${12+Math.random()*20}px`;
            container.appendChild(el); const angle = Math.random() * Math.PI * 2,
                distance = 80 + Math.random() * 240;
            el.animate([{ transform: 'translate(-50%,-50%) scale(.4)', opacity: 1 }, { transform: `translate(calc(-50% + ${Math.cos(angle)*distance}px),calc(-50% + ${Math.sin(angle)*distance}px)) scale(1.2)`, opacity: 0 }], { duration: 900, easing: 'cubic-bezier(.2,.8,.3,1)' }).finished.then(() => el.remove()); } };

    function spawnHeart() {
        const el = document.createElement('div');
        el.textContent = '❤';
        el.style.position = 'absolute';
        el.style.fontSize = (8 + Math.random() * 18) + 'px';
        el.style.color = colors[Math.floor(Math.random() * colors.length)];
        el.style.left = (Math.random() * 100) + '%';
        el.style.top = (-10 - Math.random() * 30) + 'px';
        el.style.opacity = '0.8';
        container.appendChild(el);
        hearts.push({ el, x: parseFloat(el.style.left), y: parseFloat(el.style.top), vy: 0.2 + Math.random() * 0.5, vx: (Math.random() - 0.5) * 0.2, rot: Math.random() * 360 });
    }

    function tick() {
        for (const h of hearts) {
            h.y += h.vy;
            h.x += h.vx;
            h.rot += 1;
            h.el.style.transform = `rotate(${h.rot}deg)`;
            h.el.style.left = h.x + '%';
            h.el.style.top = h.y + 'px';
            if (h.y > window.innerHeight + 20) {
                container.removeChild(h.el);
                hearts.splice(hearts.indexOf(h), 1);
                spawnHeart();
            }
        }
        requestAnimationFrame(tick);
    }
})();