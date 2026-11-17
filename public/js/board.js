(function () {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const lists = [...document.querySelectorAll('.kb-list')];

    // ---------- Toast ساده ----------
    const box = document.createElement('div');
    box.className = 'toast-container';
    document.body.append(box);

    function toast(message, type = 'success') {
        const el = document.createElement('div');
        el.className = `toast toast-${type}`;

        const icon = document.createElement('span');
        icon.className = 'toast-icon';
        icon.textContent = type === 'error' ? '⚠' : '✓';

        const text = document.createElement('span');
        text.textContent = message;

        el.append(icon, text);
        box.append(el);

        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 300);
        }, 2000);
    }

    // ---------- کمکی‌ها ----------
    const orderOf = (list) =>
        [...list.querySelectorAll('.kb-card')].map((c) => Number(c.dataset.id));

    function updateCounts() {
        lists.forEach((list) => {
            list.closest('.kb-col').querySelector('.kb-count').textContent =
                list.querySelectorAll('.kb-card').length.toLocaleString('fa-IR');
        });
    }

    async function save(list) {
        try {
            const response = await fetch('/board/move', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ status: list.dataset.status, order: orderOf(list) }),
            });

            if (!response.ok) throw new Error();
            toast('ذخیره شد');
        } catch (err) {
            toast('ذخیره نشد؛ صفحه دوباره بارگذاری می‌شه', 'error');
            setTimeout(() => location.reload(), 1200);
        }
    }

    // ---------- درگ‌اند‌دراپ ----------
    document.addEventListener('pointerdown', (e) => {
        const handle = e.target.closest('.drag-handle');
        const card = handle?.closest('.kb-card');
        if (!card) return;
        if (e.pointerType === 'mouse' && e.button !== 0) return;

        e.preventDefault();

        const from = card.parentElement;
        const before = orderOf(from).join(',');

        card.classList.add('dragging');
        document.body.classList.add('is-dragging');

        function onMove(ev) {
            const { clientX: x, clientY: y } = ev;

            if (y < 80) window.scrollBy(0, -12);
            if (y > window.innerHeight - 80) window.scrollBy(0, 12);

            const target = lists.find((list) => {
                const r = list.closest('.kb-col').getBoundingClientRect();
                return x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;
            });
            if (!target) return;

            const next = [...target.querySelectorAll('.kb-card:not(.dragging)')].find((c) => {
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

            const to = card.parentElement;
            updateCounts();

            if (to === from && orderOf(to).join(',') === before) return;
            save(to);
        }

        document.addEventListener('pointermove', onMove);
        document.addEventListener('pointerup', onEnd);
        document.addEventListener('pointercancel', onEnd);
    });
})();