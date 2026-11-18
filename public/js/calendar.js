// ==========================================================
// تقویم شمسی کارها (زیر داشبورد)
// بعد از app.js و panels.js لود می‌شه و همه‌چیز رو از روی لیست می‌خونه.
// ==========================================================
(function () {
    const dash = document.querySelector('.dash');
    const list = document.getElementById('task-list');
    if (!dash || !list) return;

    if (typeof request !== 'function' || typeof showToast !== 'function') {
        console.warn('calendar.js: اول باید app.js لود بشه.');
        return;
    }

    const PRI = { high: '#ef4444', medium: '#f59e0b', low: '#0ea5e9' };
    const MAX_CHIPS = 3;

    const fa = (n) => Number(n).toLocaleString('fa-IR');
    const pad = (n) => String(n).padStart(2, '0');
    const iso = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const addDays = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
    const cats = () => (typeof categories !== 'undefined' ? categories : (window.APP_CATEGORIES ?? []));
    const errText = (err) => (typeof firstError === 'function' ? firstError(err) : 'خطایی رخ داد.');

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }

    // ---------- تاریخ شمسی با Intl ----------
    const partsFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian-nu-latn', { year: 'numeric', month: 'numeric', day: 'numeric' });
    const titleFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { month: 'long', year: 'numeric' });

    function jp(d) {
        const o = {};
        partsFmt.formatToParts(d).forEach((p) => { if (p.type !== 'literal') o[p.type] = Number(p.value); });
        return o;
    }

    function monthLen(first) {
        const m = jp(first).month;
        if (m <= 6) return 31;
        if (m <= 11) return 30;
        return jp(addDays(first, 29)).month === 12 ? 30 : 29;
    }

    function monthStart(offset) {
        const t = new Date();
        t.setHours(12, 0, 0, 0);

        let first = addDays(t, -(jp(t).day - 1));

        for (let i = 0; i < Math.abs(offset); i++) {
            if (offset > 0) {
                first = addDays(first, monthLen(first));
            } else {
                const last = addDays(first, -1);
                first = addDays(last, -(jp(last).day - 1));
            }
        }

        return first;
    }

    // ---------- ساخت اسکلت ----------
    const sec = el('section', 'cal panel');
    sec.innerHTML = `
        <div class="cal-head">
            <h2>📅 تقویم کارها</h2>
            <div class="cal-nav">
                <button type="button" class="cal-btn" data-nav="-1" aria-label="ماه قبل">›</button>
                <strong class="cal-title"></strong>
                <button type="button" class="cal-btn" data-nav="1" aria-label="ماه بعد">‹</button>
                <button type="button" class="cal-btn cal-today" data-nav="0">امروز</button>
            </div>
        </div>
        <p class="cal-sum"></p>
        <div class="cal-week"></div>
        <div class="cal-grid"></div>
        <div class="cal-undated">
            <h3>📌 بدون تاریخ (بکش روی یه روز)</h3>
            <div class="cal-und-list"></div>
        </div>`;

    ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'].forEach((n, i) => {
        const s = el('span', i === 6 ? 'fri' : '', n.slice(0, 1));
        s.title = n;
        sec.querySelector('.cal-week').append(s);
    });

    dash.after(sec);

    // ---------- داده‌ها ----------
    function tasks() {
        return [...list.querySelectorAll('.task:not(.removing)')].map((li) => ({
            id: li.dataset.id,
            title: li.querySelector('.title')?.textContent ?? li.querySelector('.edit-input')?.value ?? '',
            due: li.dataset.due || '',
            done: li.dataset.done === '1',
            pri: li.querySelector('.priority')?.dataset.priority ?? 'medium',
            cat: li.dataset.category || '',
        }));
    }

    function chip(x, today) {
        const c = el('div', 'cal-chip' + (x.done ? ' done' : '') + (!x.done && x.due && x.due < today ? ' late' : ''), x.title);
        const cat = cats().find((k) => String(k.id) === x.cat);

        c.draggable = true;
        c.tabIndex = 0;
        c.setAttribute('role', 'button');
        c.dataset.id = x.id;
        c.title = x.title;
        c.style.setProperty('--c', cat ? cat.color : PRI[x.pri]);

        return c;
    }

    // ---------- نمایش ----------
    let offset = 0;
    let dragId = null;
    let pending = false;
    const expanded = new Set();

    function render() {
        const today = iso(new Date());
        const first = monthStart(offset);
        const len = monthLen(first);
        const lead = (first.getDay() + 1) % 7; // شروع هفته: شنبه
        const rows = Math.ceil((lead + len) / 7);
        const start = addDays(first, -lead);

        const all = tasks();
        const byDay = {};
        all.forEach((x) => { if (x.due) (byDay[x.due] ??= []).push(x); });

        const cells = [];

        for (let i = 0; i < rows * 7; i++) {
            const d = addDays(start, i);
            const k = iso(d);
            const inMonth = i >= lead && i < lead + len;

            const cell = el('div', 'cal-cell' + (inMonth ? '' : ' out') + (k === today ? ' today' : '') + (i % 7 === 6 ? ' fri' : ''));
            cell.dataset.date = k;

            const num = el('div', 'cal-dnum');
            const add = el('button', 'cal-add', '+');
            add.type = 'button';
            add.title = 'کار جدید برای این روز';
            add.setAttribute('aria-label', 'کار جدید برای این روز');
            num.append(el('span', '', fa(jp(d).day)), add);
            cell.append(num);

            const items = byDay[k] ?? [];
            const show = expanded.has(k) ? items : items.slice(0, MAX_CHIPS);
            show.forEach((x) => cell.append(chip(x, today)));

            if (items.length > MAX_CHIPS) {
                const more = el('button', 'cal-more', expanded.has(k) ? 'کمتر' : `+${fa(items.length - MAX_CHIPS)} بیشتر`);
                more.type = 'button';
                more.dataset.date = k;
                cell.append(more);
            }

            cells.push(cell);
        }

        sec.querySelector('.cal-grid').replaceChildren(...cells);
        sec.querySelector('.cal-title').textContent = titleFmt.format(first);

        // خلاصه‌ی این ماه
        const a = iso(first);
        const b = iso(addDays(first, len - 1));
        const inRange = all.filter((x) => x.due && x.due >= a && x.due <= b);
        const late = inRange.filter((x) => !x.done && x.due < today).length;

        sec.querySelector('.cal-sum').textContent =
            `${fa(inRange.length)} کار در این ماه · ✅ ${fa(inRange.filter((x) => x.done).length)} انجام‌شده · ⚠️ ${fa(late)} عقب‌افتاده`;

        // کارهای بدون تاریخ
        const und = all.filter((x) => !x.due && !x.done);
        sec.querySelector('.cal-und-list').replaceChildren(...(und.length
            ? und.map((x) => chip(x, today))
            : [el('span', 'cal-note', all.length ? 'همه‌ی کارهای باقی‌مانده تاریخ دارن 🎉' : 'هنوز کاری نداری.')]));
    }

    // ---------- تغییر تاریخ ----------
    async function move(id, date) {
        const li = list.querySelector(`.task[data-id="${id}"]`);
        if (!li || (li.dataset.due || '') === (date || '')) return;

        try {
            const task = await request(`/tasks/${id}`, 'PUT', { due_date: date });

            li.dataset.due = task.due_date ?? '';
            if (typeof renderDue === 'function') renderDue(li);
            showToast(date ? 'تاریخ سررسید عوض شد' : 'تاریخ سررسید پاک شد', 'success', 1500);
        } catch (err) {
            showToast(errText(err), 'error');
        }
    }

    function openAdd(date) {
        const fold = document.getElementById('fold-add');
        const due = document.getElementById('add-due');
        if (!fold || !due) return;

        if (!fold.classList.contains('open')) fold.querySelector('.fold-toggle')?.click();

        due.value = date;
        fold.scrollIntoView({ block: 'center' });
        fold.querySelector('input[name="title"]')?.focus({ preventScroll: true });
    }

    function focusTask(id) {
        const li = list.querySelector(`.task[data-id="${id}"]`);
        if (!li) return;

        li.scrollIntoView({ block: 'center' });
        li.classList.remove('flash');
        void li.offsetWidth;
        li.classList.add('flash');
        setTimeout(() => li.classList.remove('flash'), 1300);
    }

    // ---------- رویدادها ----------
    sec.addEventListener('click', (e) => {
        const nav = e.target.closest('[data-nav]');
        if (nav) {
            const n = Number(nav.dataset.nav);
            offset = n === 0 ? 0 : offset + n;
            render();
            return;
        }

        const add = e.target.closest('.cal-add');
        if (add) return openAdd(add.closest('.cal-cell').dataset.date);

        const more = e.target.closest('.cal-more');
        if (more) {
            const k = more.dataset.date;
            if (expanded.has(k)) expanded.delete(k); else expanded.add(k);
            render();
            return;
        }

        const c = e.target.closest('.cal-chip');
        if (c) focusTask(c.dataset.id);
    });

    sec.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.classList.contains('cal-chip')) focusTask(e.target.dataset.id);
    });

    const clearMarks = () => sec.querySelectorAll('.over, .dragging').forEach((x) => x.classList.remove('over', 'dragging'));
    const dropZone = (t) => t.closest('.cal-cell, .cal-undated');

    sec.addEventListener('dragstart', (e) => {
        const c = e.target.closest('.cal-chip');
        if (!c) return;

        dragId = c.dataset.id;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragId);
        c.classList.add('dragging');
    });

    sec.addEventListener('dragover', (e) => {
        const z = dragId && dropZone(e.target);
        if (!z) return;

        e.preventDefault();
        sec.querySelectorAll('.over').forEach((x) => { if (x !== z) x.classList.remove('over'); });
        z.classList.add('over');
    });

    sec.addEventListener('drop', (e) => {
        const z = dragId && dropZone(e.target);
        if (!z) return;

        e.preventDefault();

        const id = dragId;
        dragId = null;
        clearMarks();
        move(id, z.classList.contains('cal-cell') ? z.dataset.date : null);
    });

    sec.addEventListener('dragend', () => {
        dragId = null;
        clearMarks();
        if (pending) { pending = false; schedule(); }
    });

    // ---------- همگام‌سازی با لیست ----------
    let queued = false;

    function schedule() {
        if (dragId) { pending = true; return; }
        if (queued) return;

        queued = true;
        requestAnimationFrame(() => { queued = false; render(); });
    }

    new MutationObserver(schedule).observe(list, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true,
        attributeFilter: ['data-due', 'data-done', 'data-category', 'data-priority'],
    });

    const chips = document.getElementById('category-chips');
    if (chips) new MutationObserver(schedule).observe(chips, { childList: true });

    render();
})();