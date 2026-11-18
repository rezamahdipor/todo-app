// ==========================================================
// ساعت زنده کنار «سلام» + ساعت کشورها (زیر کانبان زنده)
// بعد از widgets.js لود می‌شه.
// ==========================================================
(function () {
    const side = document.getElementById('side-kanban');
    const hello = document.querySelector('.topbar > span');
    const showTop = hello && hello.textContent.includes('سلام');
    if (!side && !showTop) return;

    const ZONES = [
        ['ایران', 'Asia/Tehran'],
        ['ترکیه', 'Europe/Istanbul'],
        ['امارات', 'Asia/Dubai'],
        ['آلمان', 'Europe/Berlin'],
        ['انگلیس', 'Europe/London'],
        ['آمریکا (نیویورک)', 'America/New_York'],
        ['ژاپن', 'Asia/Tokyo'],
    ];

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }

    const dayNum = (s) => {
        const [y, m, d] = s.split('-').map(Number);
        return Date.UTC(y, m - 1, d) / 864e5;
    };

    const timeOpts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' };
    const dayOpts = { year: 'numeric', month: '2-digit', day: '2-digit' };

    const fWeek = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { weekday: 'long' });
    const fFull = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const fLocal = new Intl.DateTimeFormat('fa-IR', timeOpts);
    const fLocalDay = new Intl.DateTimeFormat('en-CA', dayOpts);

    let me = '';
    try { me = Intl.DateTimeFormat().resolvedOptions().timeZone; } catch (e) {}

    // ---------- ردیف کشورها ----------
    const rows = ZONES.map(([name, tz]) => {
        try {
            const li = el('li', 'wc-row' + (tz === me ? ' me' : ''));
            const ico = el('span', 'wc-ico');
            const tm = el('strong', 'wc-tm');
            const off = el('span', 'wc-off');
            off.hidden = true;

            li.append(ico, el('span', 'wc-name', name), off, tm);

            return {
                li, ico, tm, off,
                fTime: new Intl.DateTimeFormat('fa-IR', { ...timeOpts, timeZone: tz }),
                fDay: new Intl.DateTimeFormat('en-CA', { ...dayOpts, timeZone: tz }),
                fHour: new Intl.DateTimeFormat('en-US', { hour: 'numeric', hourCycle: 'h23', timeZone: tz }),
            };
        } catch (e) {
            return null; // منطقه‌ی زمانی نامعتبر
        }
    }).filter(Boolean);

    // ---------- کارت ساعت کشورها (جای کارت قدیمی) ----------
    let dateEl = null;

    if (side) {
        const panel = el('section', 'panel wclock');
        dateEl = el('div', 'wc-date');

        const ul = el('ul', 'wc-list');
        rows.forEach((r) => ul.append(r.li));

        panel.append(el('h2', '', '🌍 ساعت کشورها'), dateEl, ul);

        const old = side.querySelector('.clock');
        if (old) old.replaceWith(panel);
        else side.append(panel);
    }

    // ---------- نشان کنار «سلام» ----------
    let tb = null;

    if (showTop) {
        tb = el('span', 'tb-clock');
        hello.append(' ', tb);
    }

    // ---------- ساعت زنده ----------
    function tick() {
        const now = new Date();

        if (tb) {
            tb.textContent = `${fWeek.format(now)} · ${fLocal.format(now)}`;
            tb.title = fFull.format(now);
        }

        if (dateEl) dateEl.textContent = fFull.format(now);

        const today = dayNum(fLocalDay.format(now));

        rows.forEach((r) => {
            r.tm.textContent = r.fTime.format(now);

            const h = Number(r.fHour.format(now));
            r.ico.textContent = h >= 6 && h < 18 ? '☀️' : '🌙';

            const diff = dayNum(r.fDay.format(now)) - today;
            r.off.textContent = diff > 0 ? 'فردا' : 'دیروز';
            r.off.hidden = diff === 0;
        });
    }

    tick();
    setInterval(tick, 1000);
})();