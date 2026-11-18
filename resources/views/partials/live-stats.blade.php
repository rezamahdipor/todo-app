{{-- کارت آمار زنده (برای صفحه‌ی ولکام) --}}
<section class="live" id="live-stats" aria-live="polite">
    <h2>📡 آمار زنده</h2>

    <div class="live-grid">
        <div class="live-card online"><span class="live-ico">🟢</span><strong data-k="online">—</strong><span>آنلاین الان</span></div>
        <div class="live-card"><span class="live-ico">👥</span><strong data-k="users">—</strong><span>کاربر</span></div>
        <div class="live-card"><span class="live-ico">📋</span><strong data-k="tasks">—</strong><span>کل کارها</span></div>
        <div class="live-card"><span class="live-ico">✅</span><strong data-k="done">—</strong><span>انجام‌شده</span></div>
        <div class="live-card"><span class="live-ico">⚡</span><strong data-k="today">—</strong><span>انجام‌شده‌ی امروز</span></div>
        <div class="live-card"><span class="live-ico">🔥</span><strong data-k="habits">—</strong><span>عادت ثبت‌شده</span></div>
    </div>

    <div class="live-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div id="live-bar"></div>
    </div>
    <p class="live-foot"><span id="live-pct">—</span> کل کارها انجام شده · آخرین بروزرسانی: <span id="live-time">—</span></p>
</section>

<script>
    (function () {
        const root = document.getElementById('live-stats');
        if (!root) return;

        const URL = @json(route('live.stats'));
        const fa = (n) => Number(n).toLocaleString('fa-IR');

        async function load() {
            if (document.hidden) return;

            try {
                const res = await fetch(URL, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error();
                const d = await res.json();

                root.querySelectorAll('[data-k]').forEach((el) => {
                    const text = fa(d[el.dataset.k] ?? 0);
                    if (el.textContent !== text) {
                        el.textContent = text;
                        el.classList.remove('bump');
                        void el.offsetWidth;
                        el.classList.add('bump');
                    }
                });

                const pct = d.tasks ? Math.round((d.done / d.tasks) * 100) : 0;
                document.getElementById('live-bar').style.width = pct + '%';
                root.querySelector('.live-bar').setAttribute('aria-valuenow', pct);
                document.getElementById('live-pct').textContent = fa(pct) + '٪';
                document.getElementById('live-time').textContent = new Date().toLocaleTimeString('fa-IR');
            } catch (e) {
                // اگه اینترنت قطع شد، عددهای قبلی می‌مونن
            }
        }

        load();
        setInterval(load, 15000);
        document.addEventListener('visibilitychange', load);
    })();
</script>