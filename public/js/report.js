// ==========================================================
// دکمه‌ی 🚩 گزارش کنار پیام‌های چت (بدون دست زدن به chat.js)
// ==========================================================
(function () {
    const box = document.querySelector('[data-chat]');
    const list = box ? box.querySelector('.chat-list') : null;
    if (!list) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const REASONS = [['spam', 'اسپم'], ['abuse', 'توهین'], ['inappropriate', 'نامناسب'], ['other', 'سایر']];

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }

    // به پیام‌های بقیه (نه پیام خودت) دکمه‌ی گزارش اضافه می‌شه
    function decorate(row) {
        if (row.classList.contains('me') || row.querySelector('.cm-flag')) return;

        const head = row.querySelector('.cm-head');
        if (!head) return;

        const b = el('button', 'cm-flag', '🚩');
        b.type = 'button';
        b.title = 'گزارش پیام';
        b.setAttribute('aria-label', 'گزارش پیام');
        head.append(b);
    }

    const scan = () => list.querySelectorAll('.cm').forEach(decorate);

    new MutationObserver(scan).observe(list, { childList: true });
    scan();

    list.addEventListener('click', async (e) => {
        const row = e.target.closest('.cm');
        if (!row) return;

        // باز و بسته کردن انتخاب دلیل
        if (e.target.closest('.cm-flag')) {
            const open = row.querySelector('.cm-rep');
            list.querySelectorAll('.cm-rep').forEach((x) => x.remove());
            if (open) return;

            const pop = el('div', 'cm-rep');
            pop.append(el('span', '', 'دلیل گزارش:'));

            REASONS.forEach(([key, text]) => {
                const b = el('button', 'cm-rep-opt', text);
                b.type = 'button';
                b.dataset.reason = key;
                pop.append(b);
            });

            row.querySelector('.cm-body').append(pop);
            return;
        }

        const pick = e.target.closest('.cm-rep-opt');
        if (!pick) return;

        const pop = pick.closest('.cm-rep');
        pop.querySelectorAll('button').forEach((b) => { b.disabled = true; });

        try {
            const res = await fetch('/reports', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ type: 'message', id: Number(row.dataset.id), reason: pick.dataset.reason }),
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw { status: res.status, data };

            pop.textContent = '✓ گزارش شد؛ ممنون 🙏';
            row.querySelector('.cm-flag')?.remove();
        } catch (err) {
            pop.textContent = err.status === 429
                ? 'خیلی سریع گزارش می‌دی؛ کمی بعد دوباره.'
                : (err.data?.errors?.reason?.[0] || 'ارسال نشد؛ دوباره امتحان کن.');
        }

        setTimeout(() => pop.remove(), 2800);
    });
})();