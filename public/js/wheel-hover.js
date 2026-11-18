// ==========================================================
// افکت هاور چرخ تم (صفحه‌ی ولکام)
// تیکه میاد بالا، بقیه‌ی صفحه تار می‌شه و اسم تم با یه خط بهش وصل می‌شه.
// ==========================================================
(function () {
    const svg = document.querySelector('.theme-wheel');
    const wrap = svg ? svg.closest('.theme-wheel-wrap') : null;
    const slices = svg ? [...svg.querySelectorAll('[data-theme-choice]')] : [];

    if (!svg || !wrap || !slices.length) return;
    if (!window.matchMedia('(hover: hover)').matches) return; // موبایل: بدون هاور

    const N = slices.length;
    const POP = 6; // مقدار بالا اومدن تیکه (واحد SVG)

    // جهت هر تیکه: از مرکز به سمت وسط تیکه
    slices.forEach((p, i) => {
        const a = ((i + 0.5) / N) * 2 * Math.PI;
        p._dx = Math.sin(a);
        p._dy = -Math.cos(a);
        p.style.setProperty('--tx', (p._dx * POP).toFixed(2) + 'px');
        p.style.setProperty('--ty', (p._dy * POP).toFixed(2) + 'px');
    });

    // «بقیه‌ی صفحه»: همه‌ی خواهر و برادرهای چرخ و پدرهاش تا body
    const dimEls = [];
    for (let n = wrap; n && n !== document.body; n = n.parentElement) {
        [...(n.parentElement ? n.parentElement.children : [])].forEach((s) => {
            if (s !== n && !/^(SCRIPT|STYLE|LINK|TEMPLATE|NOSCRIPT)$/.test(s.tagName)) dimEls.push(s);
        });
    }
    dimEls.forEach((e) => e.classList.add('wheel-dimmable'));

    // خط و برچسب اسم
    const line = document.createElement('div');
    line.className = 'wheel-line';

    const tip = document.createElement('div');
    tip.className = 'wheel-tip';
    tip.setAttribute('aria-hidden', 'true');

    wrap.append(line, tip);

    let current = null;
    let locked = false; // بعد از کلیک تا خروج موس، افکت نشون داده نمی‌شه

    function place(p) {
        const r = svg.getBoundingClientRect();
        const w = wrap.getBoundingClientRect();
        const s = r.width / 200;
        const cx = r.left + r.width / 2;
        const cy = r.top + r.height / 2;
        const tw = tip.offsetWidth;
        const th = tip.offsetHeight;

        const edge = (90 + POP) * s + 3; // لبه‌ی بیرونی تیکه‌ی بالا اومده
        const reach = edge + 34 + (Math.abs(p._dx) * tw) / 2 + (Math.abs(p._dy) * th) / 2;

        const x1 = cx + p._dx * edge;
        const y1 = cy + p._dy * edge;

        // برچسب از صفحه بیرون نزنه
        const m = 8;
        const x2 = Math.min(Math.max(cx + p._dx * reach, m + tw / 2), window.innerWidth - m - tw / 2);
        const y2 = Math.min(Math.max(cy + p._dy * reach, m + th / 2), window.innerHeight - m - th / 2);

        line.style.left = x1 - w.left + 'px';
        line.style.top = y1 - w.top + 'px';
        line.style.width = Math.hypot(x2 - x1, y2 - y1) + 'px';
        line.style.transform = `rotate(${Math.atan2(y2 - y1, x2 - x1)}rad)`;

        tip.style.left = x2 - w.left + 'px';
        tip.style.top = y2 - w.top + 'px';
    }

    function dim(on) {
        dimEls.forEach((e) => e.classList.toggle('wheel-dim', on));
    }

    function show(p) {
        if (current === p) return;

        if (current) current.classList.remove('hov');
        current = p;

        const color = p.getAttribute('fill');
        tip.textContent = p.getAttribute('aria-label') || '';
        tip.style.setProperty('--wc', color);
        line.style.setProperty('--wc', color);

        p.classList.add('hov');
        place(p);
        wrap.classList.add('tip-on');
        dim(true);
    }

    function hide() {
        if (!current) return;

        current.classList.remove('hov');
        current = null;
        wrap.classList.remove('tip-on');
        dim(false);
    }

    // تشخیص تیکه با زاویه‌ی موس (بدون لرزش، چون تیکه که جابه‌جا شد زیر موس نمی‌مونه)
    svg.addEventListener('pointermove', (e) => {
        if (e.pointerType === 'touch' || locked) return;

        const r = svg.getBoundingClientRect();
        const s = r.width / 200;
        const dx = e.clientX - (r.left + r.width / 2);
        const dy = e.clientY - (r.top + r.height / 2);

        if (Math.hypot(dx, dy) > (current ? 90 + POP + 2 : 90) * s) {
            hide();
            return;
        }

        let a = Math.atan2(dx, -dy);
        if (a < 0) a += 2 * Math.PI;

        show(slices[Math.min(N - 1, Math.floor(a / ((2 * Math.PI) / N)))]);
    });

    svg.addEventListener('pointerleave', () => {
        hide();
        locked = false;
    });

    // با انتخاب تم، چرخ می‌چرخه؛ پس افکت می‌ره تا موس بره بیرون
    svg.addEventListener('click', () => {
        hide();
        locked = true;
    });

    // کیبورد: Tab روی تیکه‌ها هم همین افکت رو نشون می‌ده
    slices.forEach((p) => {
        p.addEventListener('focus', () => { if (p.matches(':focus-visible')) show(p); });
        p.addEventListener('blur', hide);
    });

    window.addEventListener('scroll', hide, { passive: true });
    window.addEventListener('blur', hide);
})();