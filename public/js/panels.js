// ==========================================================
// داشبورد: بخش‌های کشویی + آمار زنده + کانبان زنده
// بعد از app.js و subtasks.js لود می‌شه.
// ==========================================================
(function () {
    // ---------- بخش‌های کشویی ----------
    document.querySelectorAll('.fold-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const fold = btn.closest('.fold');
            const open = fold.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) fold.querySelector('input[type="text"]')?.focus();
        });
    });

    const list = document.getElementById('task-list');
    const board = document.getElementById('side-kanban');
    const stats = document.getElementById('side-stats');
    if (!list || !board || !stats) return;

    if (typeof request !== 'function' || typeof showToast !== 'function') {
        console.warn('panels.js: اول باید app.js لود بشه.');
        return;
    }

    const $ = (id) => document.getElementById(id);
    const fa = (n) => Number(n).toLocaleString('fa-IR');
    const COLS = ['todo', 'doing', 'done'];
    const PRI = [['high', 'زیاد', '#ef4444'], ['medium', 'متوسط', '#f59e0b'], ['low', 'کم', '#0ea5e9']];
    const CIRC = 263.9;

    let dragging = false;
    let pending = false;
    let queued = false;

    // ---------- کمکی‌ها ----------
    const items = () => [...list.querySelectorAll('.task:not(.removing)')];
    const cats = () => (typeof categories !== 'undefined' ? categories : (window.APP_CATEGORIES ?? []));
    const priorityOf = (li) => li.querySelector('.priority')?.dataset.priority ?? 'medium';
    const isDone = (li) => li.dataset.done === '1';

    function daysTo(str) {
        const t = new Date();
        t.setHours(0, 0, 0, 0);
        const [y, m, d] = str.split('-').map(Number);
        return Math.round((new Date(y, m - 1, d) - t) / 86400000);
    }

    // وضعیت (status) همیشه با انجام‌شدن هماهنگ می‌مونه
    function syncStatus(li) {
        let st = li.dataset.status || (isDone(li) ? 'done' : 'todo');

        if (isDone(li)) st = 'done';
        else if (st === 'done') st = 'todo';

        if (li.dataset.status !== st) li.dataset.status = st;
    }

    // ---------- آمار زنده ----------
    function syncBars(box, rows) {
        const keep = new Set(rows.map((r) => r.key));
        [...box.children].forEach((c) => { if (!keep.has(c.dataset.key)) c.remove(); });

        rows.forEach((r) => {
            let row = box.querySelector(`[data-key="${r.key}"]`);

            if (!row) {
                row = document.createElement('div');
                row.dataset.key = r.key;

                const head = document.createElement('div');
                head.className = 'hbar-label';
                const name = document.createElement('span');
                const cnt = document.createElement('span');
                cnt.className = 'hbar-count';
                head.append(name, cnt);

                const track = document.createElement('div');
                track.className = 'hbar-track';
                const fill = document.createElement('div');
                fill.className = 'hbar-fill';
                track.append(fill);

                row.append(head, track);
                box.append(row);
            }

            row.querySelector('.hbar-label span').textContent = r.label;
            row.querySelector('.hbar-count').textContent = `${fa(r.done)}/${fa(r.total)}`;

            const fill = row.querySelector('.hbar-fill');
            fill.style.setProperty('--w', (r.total ? (r.done / r.total) * 100 : 0) + '%');
            fill.style.setProperty('--c', r.color);
        });
    }

    function renderStats() {
        const all = items();
        const total = all.length;
        const done = all.filter(isDone).length;
        const late = all.filter((li) => !isDone(li) && li.dataset.due && daysTo(li.dataset.due) < 0).length;
        const percent = total ? Math.round((done / total) * 100) : 0;

        $('st-percent').textContent = fa(percent) + '٪';
        $('st-donut').style.setProperty('--dash', ((percent / 100) * CIRC).toFixed(1));
        $('st-total').textContent = fa(total);
        $('st-done').textContent = fa(done);
        $('st-left').textContent = fa(total - done);
        $('st-late').textContent = fa(late);

        const count = (pred) => {
            const g = all.filter(pred);
            return [g.filter(isDone).length, g.length];
        };

        syncBars($('st-pri'), PRI.map(([k, label, color]) => {
            const [d, t] = count((li) => priorityOf(li) === k);
            return { key: 'p-' + k, label, done: d, total: t, color };
        }));

        const rows = cats().map((c) => {
            const [d, t] = count((li) => li.dataset.category === String(c.id));
            return { key: 'c-' + c.id, label: c.name, done: d, total: t, color: c.color };
        });

        const [nd, nt] = count((li) => !li.dataset.category);
        if (nt > 0) rows.push({ key: 'c-none', label: 'بدون دسته', done: nd, total: nt, color: 'var(--muted)' });

        syncBars($('st-cat'), rows);
    }

    // ---------- کانبان زنده ----------
    const span = (cls) => {
        const s = document.createElement('span');
        s.className = cls;
        return s;
    };

    function card(li) {
        const c = document.createElement('li');
        c.className = 'mk-card';
        c.dataset.id = li.dataset.id;

        const handle = document.createElement('button');
        handle.type = 'button';
        handle.className = 'drag-handle';
        handle.setAttribute('aria-label', 'جابه‌جایی بین ستون‌ها (فلش بالا و پایین)');
        handle.textContent = '⋮⋮';

        const title = span('mk-title');
        const t = li.querySelector('.title');
        title.textContent = t ? t.textContent : (li.querySelector('.edit-input')?.value ?? '');

        const dots = span('mk-dots');
        const cat = cats().find((k) => String(k.id) === li.dataset.category);
        if (cat) {
            const d = span('mk-dot');
            d.style.background = cat.color;
            d.title = cat.name;
            dots.append(d);
        }
        dots.append(span('mk-dot mk-pri p-' + priorityOf(li)));

        c.append(handle, title, dots);
        return c;
    }

    function renderBoard() {
        if (dragging) { pending = true; return; }

        const groups = { todo: [], doing: [], done: [] };
        items().forEach((li) => groups[li.dataset.status]?.push(li));

        COLS.forEach((key) => {
            board.querySelector(`.mk-list[data-status="${key}"]`).replaceChildren(...groups[key].map(card));
            board.querySelector(`.mk-count[data-status="${key}"]`).textContent = fa(groups[key].length);
        });
    }

    function refresh() {
        if (queued) return;
        queued = true;

        requestAnimationFrame(() => {
            queued = false;
            items().forEach(syncStatus);
            renderStats();
            renderBoard();
        });
    }

    // ---------- جابه‌جایی کارت‌ها ----------
    const idsOf = (ul) => [...ul.querySelectorAll('.mk-card')].map((c) => Number(c.dataset.id));

    function setState(li, status) {
        const done = status === 'done';

        li.dataset.status = status;
        li.dataset.done = done ? '1' : '0';
        li.classList.toggle('done', done);
        li.querySelector('.check')?.setAttribute('aria-checked', done ? 'true' : 'false');

        if (typeof renderDue === 'function') renderDue(li);
        if (typeof refreshUI === 'function') refreshUI();
    }

    // ترتیب کارها توی لیست اصلی هم مثل سرور عوض می‌شه
    function reorder(ids) {
        const all = items();
        const byId = new Map(all.map((li) => [Number(li.dataset.id), li]));
        if (ids.some((id) => !byId.has(id))) return;

        const slots = ids.map((id) => all.indexOf(byId.get(id))).sort((a, b) => a - b);
        const next = [...all];
        ids.forEach((id, i) => { next[slots[i]] = byId.get(id); });
        next.forEach((li) => list.append(li));
    }

    async function commit(id, status, ids) {
        const li = list.querySelector(`.task[data-id="${id}"]`);
        if (!li) { refresh(); return; }

        setState(li, status);
        reorder(ids);

        if (status === 'done' && !list.querySelector('.task[data-done="0"]') && typeof launchConfetti === 'function') {
            launchConfetti();
        }

        refresh();

        try {
            await request('/board/move', 'POST', { status, order: ids });
            showToast('ذخیره شد', 'success', 1200);
        } catch (err) {
            showToast('ذخیره نشد؛ صفحه دوباره بارگذاری می‌شه', 'error');
            setTimeout(() => location.reload(), 1200);
        }
    }

    board.addEventListener('pointerdown', (e) => {
        const handle = e.target.closest('.drag-handle');
        const card = handle?.closest('.mk-card');
        if (!card) return;
        if (e.pointerType === 'mouse' && e.button !== 0) return;

        e.preventDefault();

        const lists = [...board.querySelectorAll('.mk-list')];
        const from = card.parentElement;
        const before = idsOf(from).join(',');

        dragging = true;
        card.classList.add('dragging');
        document.body.classList.add('is-dragging');

        function onMove(ev) {
            const x = ev.clientX;
            const y = ev.clientY;

            if (y < 80) window.scrollBy(0, -12);
            if (y > window.innerHeight - 80) window.scrollBy(0, 12);

            const target = lists.find((l) => {
                const r = l.closest('.mk-col').getBoundingClientRect();
                return x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;
            });

            lists.forEach((l) => l.classList.toggle('over', l === target));
            if (!target) return;

            const next = [...target.querySelectorAll('.mk-card:not(.dragging)')].find((c) => {
                const r = c.getBoundingClientRect();
                return y < r.top + r.height / 2;
            });

            target.insertBefore(card, next ?? null);
        }

        function onEnd() {
            document.removeEventListener('pointermove', onMove);
            document.removeEventListener('pointerup', onEnd);
            document.removeEventListener('pointercancel', onEnd);

            card.classList.remove('dragging');
            document.body.classList.remove('is-dragging');
            lists.forEach((l) => l.classList.remove('over'));
            dragging = false;

            const to = card.parentElement;
            const ids = idsOf(to);

            if (to === from && ids.join(',') === before) {
                refresh();
                return;
            }

            commit(Number(card.dataset.id), to.dataset.status, ids);
        }

        document.addEventListener('pointermove', onMove);
        document.addEventListener('pointerup', onEnd);
        document.addEventListener('pointercancel', onEnd);
    });

    // با کیبورد: فلش بالا و پایین = رفتن به ستون قبلی یا بعدی
    board.addEventListener('keydown', (e) => {
        const card = e.target.closest('.drag-handle')?.closest('.mk-card');
        if (!card || (e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) return;

        e.preventDefault();

        const i = COLS.indexOf(card.parentElement.dataset.status) + (e.key === 'ArrowUp' ? -1 : 1);
        if (i < 0 || i >= COLS.length) return;

        const target = board.querySelector(`.mk-list[data-status="${COLS[i]}"]`);
        target.append(card);

        const id = card.dataset.id;
        commit(Number(id), COLS[i], idsOf(target)).then(() => {
            setTimeout(() => board.querySelector(`.mk-card[data-id="${id}"] .drag-handle`)?.focus(), 50);
        });
    });

    // ---------- همگام‌سازی با لیست ----------
    new MutationObserver(refresh).observe(list, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['data-done', 'data-category', 'data-due', 'data-priority', 'data-status'],
    });

    const chips = document.getElementById('category-chips');
    if (chips) new MutationObserver(refresh).observe(chips, { childList: true });

    refresh();
})();