{{-- کارت آمار زنده (صفحه‌ی ولکام) --}}
<section class="live" id="live-stats" aria-live="polite">
    <h2>📡 آمار زنده <i class="live-dot" aria-hidden="true"></i></h2>

    <div class="live-grid">
        <div class="live-card online"><span class="live-ico">🟢</span><strong data-k="online">—</strong><span>آنلاین</span></div>
        <div class="live-card"><span class="live-ico">👥</span><strong data-k="users">—</strong><span>کاربر</span></div>
        <div class="live-card"><span class="live-ico">📋</span><strong data-k="tasks">—</strong><span>کل کارها</span></div>
        <div class="live-card"><span class="live-ico">✅</span><strong data-k="done">—</strong><span>انجام‌شده</span></div>
        <div class="live-card"><span class="live-ico">⚡</span><strong data-k="today">—</strong><span>انجام‌شده‌ی امروز</span></div>
        <div class="live-card"><span class="live-ico">🔥</span><strong data-k="habits">—</strong><span>عادت ثبت‌شده</span></div>
    </div>

    <h3 class="live-sub">📰 آمار وبلاگ</h3>

    <div class="live-grid">
        <div class="live-card"><span class="live-ico">📰</span><strong data-k="posts">—</strong><span>نوشته‌ی منتشرشده</span></div>
        <div class="live-card"><span class="live-ico">✍️</span><strong data-k="authors">—</strong><span>نویسنده</span></div>
        <div class="live-card"><span class="live-ico">🆕</span><strong data-k="posts_week">—</strong><span>نوشته‌ی ۷ روز اخیر</span></div>
    </div>

    <div class="live-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div id="live-bar"></div>
    </div>
    <p class="live-foot"><b id="live-pct">—</b>٪ کل کارها انجام شده · آخرین بروزرسانی: <span id="live-time">—</span></p>
</section>

<script>
    (function () {
        const root = document.getElementById('live-stats');
        if (!root) return;

        const ENDPOINT = @json(route('live.stats'));
        const fa = (n) => Number(n).toLocaleString('fa-IR');
        const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
        const cards = [...root.querySelectorAll('.live-card')];

        // شمارش نرم از مقدار قبلی تا مقدار جدید
        function countTo(el, to, ms) {
            const from = Number(el.dataset.v ?? 0);
            el.dataset.v = to;

            if (reduce || from === to) { el.textContent = fa(to); return; }

            const t0 = performance.now();
            (function step(t) {
                const p = Math.min(1, (t - t0) / ms);
                el.textContent = fa(Math.round(from + (to - from) * (1 - Math.pow(1 - p, 3))));
                if (p < 1) requestAnimationFrame(step);
            })(t0);
        }

        // خط کجی که از روی کارت رد می‌شه
        function sweep(card, delay) {
            if (reduce) return;
            card.style.setProperty('--d', delay + 'ms');
            card.classList.remove('sweep');
            void card.offsetWidth;
            card.classList.add('sweep');
        }

        root.addEventListener('animationend', (e) => {
            if (e.animationName === 'liveSweep') e.target.classList.remove('sweep');
        });

        async function load() {
            if (document.hidden) return;

            try {
                const res = await fetch(ENDPOINT, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error();
                const d = await res.json();

                // موج خط‌ها: کارت به کارت، و عدد وسط عبور خط آپدیت می‌شه
                cards.forEach((card, i) => {
                    const el = card.querySelector('[data-k]');
                    const delay = reduce ? 0 : i * 70;

                    sweep(card, delay);
                    setTimeout(() => countTo(el, d[el.dataset.k] ?? 0, 700), reduce ? 0 : delay + 320);
                });

                const pct = d.tasks ? Math.round((d.done / d.tasks) * 100) : 0;

                setTimeout(() => {
                    document.getElementById('live-bar').style.width = pct + '%';
                    countTo(document.getElementById('live-pct'), pct, 700);
                }, reduce ? 0 : 500);

                root.querySelector('.live-bar').setAttribute('aria-valuenow', pct);
                document.getElementById('live-time').textContent = new Date().toLocaleTimeString('fa-IR');
            } catch (e) {
                // اگه اینترنت قطع شد، عددهای قبلی می‌مونن
            }
        }

        load();
        setInterval(load, 15000);
        document.addEventListener('visibilitychange', load);

        // نور دنبال‌کننده‌ی موس روی کارت‌ها
        document.addEventListener('pointermove', (e) => {
            const c = e.target.closest('.lp-card, .live-card, .post-card');
            if (!c) return;

            const r = c.getBoundingClientRect();
            c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            c.style.setProperty('--my', (e.clientY - r.top) + 'px');
        }, { passive: true });
    })();
</script>