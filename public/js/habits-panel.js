// ==========================================================
// بخش عادت‌ها زیر تقویم (صفحه‌ی لیست کارها)
// بعد از calendar.js لود می‌شه و از request / showToast / firstError اپ اصلی استفاده می‌کنه.
// ==========================================================
(function () {
    const anchor = document.querySelector('.cal') || document.querySelector('.dash');
    if (!anchor) return;

    if (typeof request !== 'function' || typeof showToast !== 'function') {
        console.warn('habits-panel.js: اول باید app.js لود بشه.');
        return;
    }

    const DAY = 86400000;
    const WD = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
    const ICONS = [['💧', 'آب'], ['🏃', 'ورزش'], ['📚', 'مطالعه'], ['🧘', 'مدیتیشن'], ['🍎', 'تغذیه'],
        ['😴', 'خواب'], ['✍️', 'نوشتن'], ['💊', 'دارو'], ['🧹', 'نظافت'], ['🎯', 'هدف']];

    const fa = (n) => Number(n).toLocaleString('fa-IR');
    const pad = (n) => String(n).padStart(2, '0');
    const dayFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { weekday: 'long', day: 'numeric', month: 'long' });
    const errText = (e) => (typeof firstError === 'function' ? firstError(e) : 'خطایی رخ داد.');

    let habits = [];

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }

    const today = () => { const d = new Date(); d.setHours(0, 0, 0, 0); return d; };
    const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const num = (s) => { const [y, m, d] = s.split('-').map(Number); return Date.UTC(y, m - 1, d) / DAY; };
    const dateOf = (n) => new Date(n * DAY).toISOString().slice(0, 10);

    // ---------- اسکلت ----------
    const sec = el('section', 'panel hbp');
    sec.innerHTML = `
        <div class="cal-head">
            <h2>🔥 عادت‌ها</h2>
            <a href="/habits" class="topbar-link">صفحه‌ی کامل ←</a>
        </div>
        <p class="cal-sum hbp-sub">در حال بارگذاری...</p>

        <div class="progress-wrap">
            <div class="progress-info"><span>پیشرفت امروز</span><span class="hbp-percent">۰٪</span></div>
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="progress-bar hbp-bar" style="width: 0%"></div>
            </div>
        </div>

        <div class="stats-grid hb-stats">
            <div class="stat-card"><span class="stat-icon">✅</span><span class="stat-value stat-good hbp-today">۰</span><span class="stat-label">امروز</span></div>
            <div class="stat-card"><span class="stat-icon">🔥</span><span class="stat-value hbp-streak">۰</span><span class="stat-label">بهترین رکورد فعلی</span></div>
            <div class="stat-card"><span class="stat-icon">📈</span><span class="stat-value hbp-rate">۰٪</span><span class="stat-label">میانگین ۳۰ روز</span></div>
        </div>

        <div class="fold">
            <button type="button" class="fold-toggle" aria-expanded="false">
                <span>➕ عادت جدید</span>
                <span class="fold-arrow" aria-hidden="true">▾</span>
            </button>
            <div class="fold-body">
                <div class="fold-inner">
                    <form class="add-form hb-form" autocomplete="off">
                        <div class="add-row">
                            <input type="text" name="name" placeholder="مثلاً: ۸ لیوان آب" maxlength="60" required>
                            <button type="submit">افزودن</button>
                        </div>
                        <div class="add-options">
                            <select name="icon" aria-label="آیکون"></select>
                            <input type="color" name="color" value="#8b5cf6" aria-label="رنگ عادت">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="hb-list"></div>`;

    anchor.after(sec);

    const $ = (s) => sec.querySelector(s);
    const iconSel = $('select[name="icon"]');
    ICONS.forEach(([i, n]) => iconSel.append(new Option(`${i} ${n}`, i)));
    iconSel.value = '🎯';

    // ---------- آمار هر عادت ----------
    function statsOf(h) {
        const t = num(fmt(today()));

        let n = h.set.has(dateOf(t)) ? t : t - 1;
        let cur = 0;
        while (h.set.has(dateOf(n))) { cur++; n--; }

        let best = 0, run = 0, prev = null;
        [...h.set].map(num).sort((a, b) => a - b).forEach((x) => {
            run = prev !== null && x === prev + 1 ? run + 1 : 1;
            best = Math.max(best, run);
            prev = x;
        });

        let hit = 0;
        for (let i = 0; i < 30; i++) if (h.set.has(dateOf(t - i))) hit++;

        return { cur, best, rate: Math.round((hit / 30) * 100) };
    }

    // ---------- کارت هر عادت ----------
    function card(h) {
        const t = today();
        const tk = fmt(t);
        const st = statsOf(h);

        const c = el('article', 'hb-card');
        c.dataset.id = h.id;
        c.style.setProperty('--hc', h.color);

        const info = el('div', 'hb-info');
        info.append(
            el('strong', 'hb-name', h.name),
            el('div', 'hb-meta', `🔥 ${fa(st.cur)} روز · 🏆 ${fa(st.best)} · 📈 ${fa(st.rate)}٪`),
        );

        const tb = el('button', 'hb-today', '✓');
        tb.type = 'button';
        tb.title = 'انجام شدن امروز';
        tb.setAttribute('aria-label', `امروز «${h.name}» انجام شد`);
        tb.setAttribute('aria-pressed', h.set.has(tk) ? 'true' : 'false');

        const del = el('button', 'hb-del', '×');
        del.type = 'button';
        del.title = 'حذف عادت';
        del.setAttribute('aria-label', `حذف ${h.name}`);

        const head = el('div', 'hb-head');
        head.append(el('span', 'hb-icon', h.icon), info, tb, del);

        const grid = el('div', 'hb-grid');
        WD.forEach((w) => grid.append(el('span', 'hb-wd', w)));

        const ws = new Date(t);
        ws.setDate(t.getDate() - ((t.getDay() + 1) % 7)); // شروع هفته: شنبه

        for (let i = 0; i < 35; i++) {
            const d = new Date(ws);
            d.setDate(ws.getDate() + i - 28);

            const k = fmt(d);
            const b = el('button', 'hb-cell' + (h.set.has(k) ? ' on' : '') + (d > t ? ' future' : '') + (k === tk ? ' today' : ''));

            b.type = 'button';
            b.dataset.date = k;
            b.title = dayFmt.format(d);
            b.setAttribute('aria-label', dayFmt.format(d));
            grid.append(b);
        }

        c.append(head, grid);
        return c;
    }

    // ---------- نمایش ----------
    let lastDay = fmt(today());

    function render() {
        lastDay = fmt(today());

        $('.hb-list').replaceChildren(...(habits.length
            ? habits.map(card)
            : [el('p', 'empty', 'هنوز عادتی نساختی. از «➕ عادت جدید» شروع کن 🌱')]));

        const total = habits.length;
        const done = habits.filter((h) => h.set.has(lastDay)).length;
        const percent = total ? Math.round((done / total) * 100) : 0;
        const all = habits.map(statsOf);

        $('.hbp-sub').textContent = total
            ? `${fa(done)} از ${fa(total)} عادت امروز انجام شد`
            : 'عادت‌های روزانه‌ات رو اینجا دنبال کن';
        $('.hbp-percent').textContent = fa(percent) + '٪';
        $('.hbp-bar').style.width = percent + '%';
        $('.progress').setAttribute('aria-valuenow', percent);
        $('.hbp-today').textContent = `${fa(done)}/${fa(total)}`;
        $('.hbp-streak').textContent = fa(Math.max(0, ...all.map((s) => s.cur)));
        $('.hbp-rate').textContent = fa(total ? Math.round(all.reduce((a, s) => a + s.rate, 0) / total) : 0) + '٪';
    }

    // ---------- تیک زدن ----------
    async function toggle(h, date) {
        const was = h.set.has(date);
        const apply = (on) => (on ? h.set.add(date) : h.set.delete(date));

        apply(!was);
        render();

        try {
            const r = await request(`/habits/${h.id}/toggle`, 'POST', { date });

            if (r.done !== !was) { apply(r.done); render(); }

            const t = fmt(today());
            if (r.done && date === t && habits.length && habits.every((x) => x.set.has(t))) {
                if (typeof launchConfetti === 'function') launchConfetti();
                showToast('همه‌ی عادت‌های امروز انجام شد 🎉', 'success', 4000);
            }
        } catch (err) {
            apply(was);
            render();
            showToast(errText(err), 'error');
        }
    }

    // ---------- رویدادها ----------
    sec.addEventListener('click', async (e) => {
        const ft = e.target.closest('.fold-toggle');
        if (ft) {
            const fold = ft.closest('.fold');
            const open = fold.classList.toggle('open');
            ft.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) fold.querySelector('input[name="name"]')?.focus();
            return;
        }

        const c = e.target.closest('.hb-card');
        const h = c && habits.find((x) => String(x.id) === c.dataset.id);
        if (!h) return;

        if (e.target.closest('.hb-today')) return toggle(h, fmt(today()));

        const cell = e.target.closest('.hb-cell');
        if (cell && !cell.classList.contains('future')) return toggle(h, cell.dataset.date);

        if (e.target.closest('.hb-del')) {
            if (!confirm(`عادت «${h.name}» با همه‌ی سابقه‌اش حذف بشه؟`)) return;

            try {
                await request(`/habits/${h.id}`, 'DELETE');
                habits = habits.filter((x) => x !== h);
                render();
                showToast('عادت حذف شد');
            } catch (err) {
                showToast(errText(err), 'error');
            }
        }
    });

    sec.addEventListener('submit', async (e) => {
        const form = e.target.closest('.hb-form');
        if (!form) return;

        e.preventDefault();

        const nameInput = form.querySelector('[name="name"]');

        try {
            const h = await request('/habits', 'POST', {
                name: nameInput.value,
                icon: form.querySelector('[name="icon"]').value,
                color: form.querySelector('[name="color"]').value,
            });

            habits.push({ ...h, set: new Set(h.dates) });
            nameInput.value = '';
            render();
            showToast('عادت اضافه شد');
        } catch (err) {
            showToast(errText(err), 'error');
        }
    });

    // عوض شدن روز (نیمه‌شب)
    const checkDay = () => { if (fmt(today()) !== lastDay) render(); };
    document.addEventListener('visibilitychange', () => { if (!document.hidden) checkDay(); });
    setInterval(checkDay, 60000);

    // ---------- بارگذاری ----------
    request('/habits/data', 'GET')
        .then((data) => {
            habits = data.map((h) => ({ ...h, set: new Set(h.dates) }));
            render();
        })
        .catch(() => {
            $('.hbp-sub').textContent = 'بارگذاری عادت‌ها ناموفق بود؛ صفحه رو رفرش کن.';
        });
})();