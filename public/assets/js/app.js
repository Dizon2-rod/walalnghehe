document.addEventListener('DOMContentLoaded', () => {
	// Countdown to next monthsary (14th day as example)
	const el = document.querySelector('[data-countdown]');
	if (el) {
		function next14th(){
			const now = new Date();
			let target = new Date(now.getFullYear(), now.getMonth(), 14, 0, 0, 0);
			if (now > target) target = new Date(now.getFullYear(), now.getMonth()+1, 14, 0, 0, 0);
			return target;
		}
		function tick(){
			const t = next14th() - new Date();
			const d = Math.floor(t/86400000);
			const h = Math.floor((t%86400000)/3600000);
			const m = Math.floor((t%3600000)/60000);
			el.textContent = `${d}d ${h}h ${m}m`;
		}
		setInterval(tick, 1000*30);
		tick();
	}
});
