(function () {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const list = document.getElementById('hb-list');
    const form = document.getElementById('hb-form');
    if (!list || !form) return;

    const DAY = 86400000;
    const WD = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
    const fa = (n) => Number(n).toLocaleString('fa-IR');
    const pad = (n) => String(n).padStart(2, '0');
    const dayFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { weekday: 'long', day: 'numeric', month: 'long' });

    let habits = (window.APP_HABITS ?? []).map((h) => ({ ...h, set: new Set(h.dates) }));

    // ---------- کمکی‌ها ----------
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
    const firstError = (err) => Object.values(err?.errors ?? {})[0]?.[0] ?? 'خطایی رخ داد.';

    async function req(url, method, body = null) {
        const r = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: body ? JSON.stringify(body) : null,
        });
        const data = await r.json();
        if (!r.ok) throw data;
        return data;
    }

    // ---------- Toast ----------
    const box = el('div', 'toast-container');
    box.setAttribute('aria-live', 'polite');
    document.body.append(box);

    function toast(message, type = 'success', ms = 2200) {
        const t = el('div', `toast toast-${type}`);
        t.append(el('span', 'toast-icon', type === 'error' ? '⚠' : '✓'), el('span', 'toast-text', message));
        box.append(t);
        requestAnimationFrame(() => t.classList.add('show'));

        const close = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); };
        t.onclick = close;
        setTimeout(close, ms);
    }

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

    // ---------- کارت عادت ----------
    function card(h) {
        const t = today();
        const tk = fmt(t);
        const st = statsOf(h);
        const doneToday = h.set.has(tk);

        const c = el('article', 'hb-card');
        c.dataset.id = h.id;
        c.style.setProperty('--hc', h.color);

        const head = el('div', 'hb-head');
        const info = el('div', 'hb-info');
        info.append(
            el('strong', 'hb-name', h.name),
            el('div', 'hb-meta', `🔥 ${fa(st.cur)} روز · 🏆 ${fa(st.best)} · 📈 ${fa(st.rate)}٪`),
        );

        const tb = el('button', 'hb-today', '✓');
        tb.type = 'button';
        tb.title = 'انجام شدن امروز';
        tb.setAttribute('aria-label', `امروز «${h.name}» انجام شد`);
        tb.setAttribute('aria-pressed', doneToday ? 'true' : 'false');

        const del = el('button', 'hb-del', '×');
        del.type = 'button';
        del.title = 'حذف عادت';
        del.setAttribute('aria-label', `حذف ${h.name}`);

        head.append(el('span', 'hb-icon', h.icon), info, tb, del);

        const grid = el('div', 'hb-grid');
        WD.forEach((w) => grid.append(el('span', 'hb-wd', w)));

        const ws = new Date(t);
        ws.setDate(t.getDate() - ((t.getDay() + 1) % 7)); // شروع هفته: شنبه

        for (let i = 0; i < 35; i++) {
            const d = new Date(ws);
            d.setDate(ws.getDate() + i - 28);

            const k = fmt(d);
            const b = el('button', 'hb-cell'
                + (h.set.has(k) ? ' on' : '')
                + (d > t ? ' future' : '')
                + (k === tk ? ' today' : ''));

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

        list.replaceChildren(...(habits.length
            ? habits.map(card)
            : [el('p', 'empty', 'هنوز عادتی نساختی. از «➕ عادت جدید» شروع کن 🌱')]));

        const total = habits.length;
        const done = habits.filter((h) => h.set.has(lastDay)).length;
        const percent = total ? Math.round((done / total) * 100) : 0;
        const all = habits.map(statsOf);

        document.getElementById('hb-sub').textContent = total
            ? `${fa(done)} از ${fa(total)} عادت امروز انجام شد`
            : 'عادت‌های روزانه‌ات رو اینجا دنبال کن';
        document.getElementById('hb-percent').textContent = fa(percent) + '٪';
        document.getElementById('hb-bar').style.width = percent + '%';
        document.querySelector('.hb-stats').previousElementSibling
            .querySelector('.progress').setAttribute('aria-valuenow', percent);
        document.getElementById('hs-today').textContent = `${fa(done)}/${fa(total)}`;
        document.getElementById('hs-streak').textContent = fa(Math.max(0, ...all.map((s) => s.cur)));
        document.getElementById('hs-rate').textContent =
            fa(total ? Math.round(all.reduce((a, s) => a + s.rate, 0) / total) : 0) + '٪';
    }

    // ---------- تیک زدن ----------
    async function toggle(h, date) {
        const was = h.set.has(date);
        const apply = (on) => (on ? h.set.add(date) : h.set.delete(date));

        apply(!was);
        render();

        try {
            const r = await req(`/habits/${h.id}/toggle`, 'POST', { date });

            if (r.done !== !was) { apply(r.done); render(); }

            const t = fmt(today());
            if (r.done && date === t && habits.length && habits.every((x) => x.set.has(t))) {
                toast('همه‌ی عادت‌های امروز انجام شد 🎉', 'success', 4000);
            }
        } catch (err) {
            apply(was);
            render();
            toast(firstError(err), 'error');
        }
    }

    list.addEventListener('click', async (e) => {
        const c = e.target.closest('.hb-card');
        if (!c) return;

        const h = habits.find((x) => String(x.id) === c.dataset.id);
        if (!h) return;

        if (e.target.closest('.hb-today')) return toggle(h, fmt(today()));

        const cell = e.target.closest('.hb-cell');
        if (cell && !cell.classList.contains('future')) return toggle(h, cell.dataset.date);

        if (e.target.closest('.hb-del')) {
            if (!confirm(`عادت «${h.name}» با همه‌ی سابقه‌اش حذف بشه؟`)) return;

            try {
                await req(`/habits/${h.id}`, 'DELETE');
                habits = habits.filter((x) => x !== h);
                render();
                toast('عادت حذف شد');
            } catch (err) {
                toast(firstError(err), 'error');
            }
        }
    });

    // ---------- افزودن ----------
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nameInput = form.querySelector('[name="name"]');

        try {
            const h = await req('/habits', 'POST', {
                name: nameInput.value,
                icon: form.querySelector('[name="icon"]').value,
                color: form.querySelector('[name="color"]').value,
            });

            habits.push({ ...h, set: new Set(h.dates) });
            nameInput.value = '';
            render();
            toast('عادت اضافه شد');
        } catch (err) {
            toast(firstError(err), 'error');
        }
    });

    // ---------- باکس کشویی ----------
    document.querySelectorAll('.fold-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const fold = btn.closest('.fold');
            const open = fold.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) fold.querySelector('input[type="text"]')?.focus();
        });
    });

    // ---------- عوض شدن روز (نیمه‌شب) ----------
    document.addEventListener('visibilitychange', () => { if (!document.hidden && fmt(today()) !== lastDay) render(); });
    setInterval(() => { if (fmt(today()) !== lastDay) render(); }, 60000);

    render();
})();