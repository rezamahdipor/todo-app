// ==========================================================
// ابزارک‌های زیر کانبان زنده: پومودورو 🍅 و ساعت شمسی
// بعد از panels.js لود می‌شه.
// ==========================================================
(function () {
    const side = document.getElementById('side-kanban');
    const list = document.getElementById('task-list');
    if (!side || !list) return;

    const NS = 'http://www.w3.org/2000/svg';
    const fa = (v) => String(v).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
    const pad = (n) => String(n).padStart(2, '0');
    const toast = (...args) => { if (typeof showToast === 'function') showToast(...args); };

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }

    const store = {
        get(key, fallback) {
            try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch (e) { return fallback; }
        },
        set(key, value) {
            try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) {}
        },
    };

    // ==========================================================
    // پومودورو
    // ==========================================================
    const MODES = { focus: ['تمرکز', 25], short: ['استراحت', 5], long: ['استراحت', 15] };
    const MODE_KEYS = Object.keys(MODES);
    const CIRC = 282.7;
    const baseTitle = document.title;

    let mode = 'focus';
    let remain = MODES.focus[1] * 60;
    let endAt = 0;
    let timer = null;
    let audio = null;

    const total = () => MODES[mode][1] * 60;
    const left = () => (endAt ? Math.max(0, Math.ceil((endAt - Date.now()) / 1000)) : remain);
    const save = () => store.set('pomo-state', { mode, remain, endAt });

    function todayCount() {
        const c = store.get('pomo-count', {});
        return c.d === new Date().toDateString() ? c.n : 0;
    }

    // ---------- ساخت رابط ----------
    const panel = el('section', 'panel pomodoro');
    panel.append(el('h2', '', '🍅 پومودورو'));

    const segs = el('div', 'filters');
    const segBtns = {};
    MODE_KEYS.forEach((k) => {
        const b = el('button', 'filter-btn', `${MODES[k][0]} ${fa(MODES[k][1])}`);
        b.type = 'button';
        b.onclick = () => setMode(k);
        segBtns[k] = b;
        segs.append(b);
    });

    const ring = el('div', 'pm-ring');
    const svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('viewBox', '0 0 100 100');
    svg.setAttribute('aria-hidden', 'true');

    const track = document.createElementNS(NS, 'circle');
    const fill = document.createElementNS(NS, 'circle');
    [[track, 'pm-track'], [fill, 'pm-fill']].forEach(([c, cls]) => {
        c.setAttribute('class', cls);
        c.setAttribute('cx', '50');
        c.setAttribute('cy', '50');
        c.setAttribute('r', '45');
    });
    svg.append(track, fill);

    const time = el('div', 'pm-time');
    time.setAttribute('role', 'timer');
    ring.append(svg, time);

    const sel = el('select', 'pm-task');
    sel.setAttribute('aria-label', 'کاری که روش تمرکز می‌کنی');

    const startBtn = el('button');
    startBtn.type = 'button';

    const resetBtn = el('button', 'btn-logout', '↺ از نو');
    resetBtn.type = 'button';

    const actions = el('div', 'pm-actions');
    actions.append(startBtn, resetBtn);

    const doneBtn = el('button', 'pm-done btn-done', '✅ این کار انجام شد');
    doneBtn.type = 'button';
    doneBtn.hidden = true;

    const counter = el('p', 'pm-count');

    panel.append(segs, ring, sel, actions, doneBtn, counter);

    // ---------- نمایش ----------
    function paint() {
        const s = left();

        time.textContent = fa(`${pad(Math.floor(s / 60))}:${pad(s % 60)}`);
        fill.style.strokeDashoffset = (CIRC * (s / total())).toFixed(1);
        document.title = endAt ? `${time.textContent} · ${baseTitle}` : baseTitle;

        panel.dataset.mode = mode;
        MODE_KEYS.forEach((k) => segBtns[k].classList.toggle('active', k === mode));

        startBtn.textContent = endAt ? '⏸ توقف' : (remain < total() ? '▶ ادامه' : '▶ شروع');
        counter.textContent = `امروز: ${fa(todayCount())} پومودورو 🍅`;
    }

    function beep() {
        try {
            audio = audio || new (window.AudioContext || window.webkitAudioContext)();
            [0, 0.25, 0.5].forEach((t) => {
                const o = audio.createOscillator();
                const g = audio.createGain();
                const at = audio.currentTime + t;

                o.frequency.value = 880;
                g.gain.setValueAtTime(0.15, at);
                g.gain.exponentialRampToValueAtTime(0.001, at + 0.2);
                o.connect(g);
                g.connect(audio.destination);
                o.start(at);
                o.stop(at + 0.2);
            });
        } catch (e) {}
    }

    // ---------- کنترل ----------
    function tick() {
        if (endAt && left() <= 0) {
            finish();
            return;
        }
        paint();
    }

    function start() {
        try {
            audio = audio || new (window.AudioContext || window.webkitAudioContext)();
            audio.resume?.();
        } catch (e) {}

        if (remain <= 0) remain = total();
        endAt = Date.now() + remain * 1000;
        timer = setInterval(tick, 500);
        save();
        paint();
    }

    function pause() {
        remain = left();
        endAt = 0;
        clearInterval(timer);
        timer = null;
        save();
        paint();
    }

    function setMode(k) {
        pause();
        mode = k;
        remain = total();
        save();
        paint();
    }

    function finish() {
        clearInterval(timer);
        timer = null;
        endAt = 0;
        remain = 0;
        beep();

        if (mode === 'focus') {
            const n = todayCount() + 1;
            store.set('pomo-count', { d: new Date().toDateString(), n });
            toast('یه پومودورو تموم شد 🍅 وقت استراحته', 'success', 5000);
            setMode(n % 4 === 0 ? 'long' : 'short');
        } else {
            toast('استراحت تموم شد؛ آماده‌ای؟ 💪', 'success', 5000);
            setMode('focus');
        }
    }

    startBtn.onclick = () => (endAt ? pause() : start());

    resetBtn.onclick = () => {
        pause();
        remain = total();
        save();
        paint();
    };

    sel.onchange = () => { doneBtn.hidden = !sel.value; };

    // همون چک‌باکس لیست رو می‌زنه، پس کانبان و آمار هم خودکار آپدیت می‌شن
    doneBtn.onclick = () => {
        list.querySelector(`.task[data-id="${sel.value}"] .check`)?.click();
    };

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) tick();
    });

    // ---------- لیست کارهای انجام‌نشده توی انتخابگر ----------
    function fillTasks() {
        const keep = sel.value;

        sel.replaceChildren(new Option('بدون انتخاب کار', ''));

        list.querySelectorAll('.task:not(.removing)[data-done="0"]').forEach((li) => {
            const t = li.querySelector('.title');
            if (t) sel.append(new Option(t.textContent, li.dataset.id));
        });

        sel.value = [...sel.options].some((o) => o.value === keep) ? keep : '';
        doneBtn.hidden = !sel.value;
    }

    let queued = false;
    new MutationObserver(() => {
        if (queued) return;
        queued = true;
        requestAnimationFrame(() => { queued = false; fillTasks(); });
    }).observe(list, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true,
        attributeFilter: ['data-done'],
    });

    // ---------- برگردوندن حالت قبل از رفرش ----------
    const saved = store.get('pomo-state', null);
    if (saved && MODES[saved.mode]) {
        mode = saved.mode;
        remain = Math.min(Math.max(Number(saved.remain) || 0, 0), total()) || total();

        if (saved.endAt > Date.now()) {
            endAt = saved.endAt;
            timer = setInterval(tick, 500);
        } else if (saved.endAt) {
            remain = total(); // وقتی صفحه بسته بود تموم شده
        }
    }

    // ==========================================================
    // ساعت زنده و تاریخ شمسی
    // ==========================================================
    const clock = el('section', 'panel clock');
    const wcTime = el('div', 'wc-time');
    const wcDate = el('div', 'wc-date');
    clock.append(wcTime, wcDate);

    const dateFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
    const timeFmt = new Intl.DateTimeFormat('fa-IR', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    });

    function tickClock() {
        const now = new Date();
        const h = now.getHours();
        const icon = h < 5 ? '🌙' : h < 12 ? '🌅' : h < 17 ? '☀️' : h < 20 ? '🌇' : '🌙';

        wcTime.textContent = `${icon} ${timeFmt.format(now)}`;
        wcDate.textContent = dateFmt.format(now);
    }

    // ---------- راه‌اندازی ----------
    side.append(panel, clock);
    fillTasks();
    paint();
    tickClock();
    setInterval(tickClock, 1000);
})();