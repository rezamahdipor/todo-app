// ==========================================================
// چت عمومی: پنل بالا راست + صفحه‌ی /chat
// بدون وب‌سوکت: هر چند ثانیه یک بار از سرور می‌پرسه.
// ==========================================================
(function () {
    const box = document.querySelector('[data-chat]');
    if (!box) return;

    const POLL = box.dataset.poll;
    const SEND = box.dataset.send;
    const ME = box.dataset.me;
    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const panel = document.getElementById('chat-panel'); // روی صفحه‌ی /chat نیست
    const fab = document.getElementById('chat-btn');
    if (panel && fab) document.body.append(fab, panel);

    const list = box.querySelector('.chat-list');
    const form = box.querySelector('.chat-form');
    const input = form.querySelector('input');
    const sendBtn = form.querySelector('button');
    const errEl = box.querySelector('.chat-err');
    const tabs = [...box.querySelectorAll('.chat-room')];
    const timeFmt = new Intl.DateTimeFormat('fa-IR', { hour: '2-digit', minute: '2-digit' });

    const store = {
        get(k, f) { try { return JSON.parse(localStorage.getItem(k)) ?? f; } catch (e) { return f; } },
        set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} },
    };

    let room = Number(store.get('chat-room', 1));
    if (![1, 2, 3].includes(room)) room = 1;

    let last = 0;
    let seen = store.get('chat-seen', null);
    let timer = null;
    let busy = false;
    let again = false;
    let sending = false;

    const isOpen = () => !panel || panel.classList.contains('open');
    const delay = () => (document.hidden ? 30000 : isOpen() ? 3000 : 25000);

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }

    function setEmpty(text) {
        let p = list.querySelector('.chat-empty');
        if (!p) { p = el('p', 'chat-empty'); list.append(p); }
        p.textContent = text;
    }

    function showErr(msg) {
        errEl.textContent = msg;
        errEl.hidden = !msg;
    }

    // ---------- ساخت پیام ----------
    function node(m) {
        const row = el('div', 'cm' + (String(m.user_id) === String(ME) ? ' me' : ''));
        row.dataset.id = m.id;

        let av;
        if (m.avatar) {
            av = el('img', 'avatar avatar-img cm-av');
            av.src = m.avatar;
            av.alt = '';
            av.loading = 'lazy';
        } else {
            av = el('span', 'avatar cm-av', [...m.name][0] ?? '?');
        }

        const head = el('div', 'cm-head');
        head.append(el('strong', '', m.name), el('time', '', timeFmt.format(new Date(m.at))));

        const body = el('div', 'cm-body');
        body.append(head, el('p', 'cm-text', m.body)); // textContent: امن در برابر HTML

        row.append(av, body);
        return row;
    }

    function append(messages) {
        const fresh = messages.filter((m) => m.id > last);
        if (!fresh.length) return;

        const stick = last === 0 || list.scrollHeight - list.scrollTop - list.clientHeight < 90;

        list.querySelector('.chat-empty')?.remove();
        fresh.forEach((m) => list.append(node(m)));
        last = Math.max(last, ...fresh.map((m) => m.id));

        while (list.children.length > 200) list.firstElementChild.remove();
        if (stick) list.scrollTop = list.scrollHeight;
    }

    // ---------- نقطه‌ی قرمز پیام تازه ----------
    function badges(latest) {
        if (!seen) {
            seen = { ...latest }; // بار اول: پیام‌های قدیمی «تازه» حساب نشن
        }

        if (isOpen() && !document.hidden) {
            seen[room] = Math.max(seen[room] ?? 0, last);
        }

        store.set('chat-seen', seen);

        let any = false;
        tabs.forEach((t) => {
            const n = Number(t.dataset.room);
            const fresh = (latest[n] ?? 0) > (seen[n] ?? 0) && !(isOpen() && n === room);
            t.querySelector('.chat-new').hidden = !fresh;
            any = any || fresh;
        });

        const dot = fab?.querySelector('.chat-dot');
        if (dot) dot.hidden = !(any && !isOpen());
    }

    // ---------- گرفتن پیام‌های تازه ----------
    async function poll() {
        if (busy) { again = true; return; }

        busy = true;
        clearTimeout(timer);
        const asked = room;

        try {
            const res = await fetch(`${POLL}?room=${asked}&after=${last}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(res.status);

            const d = await res.json();

            if (asked === room) {
                append(d.messages);
                if (last === 0) setEmpty('هنوز پیامی نیست؛ اولین نفر باش 👋');
                badges(d.latest);
            }
        } catch (e) {
            // اینترنت قطع یا محدودیت درخواست؛ دفعه‌ی بعد دوباره
        } finally {
            busy = false;

            if (again) {
                again = false;
                poll();
            } else {
                timer = setTimeout(poll, delay());
            }
        }
    }

    function pollNow() {
        if (busy) again = true;
        else poll();
    }

    function setRoom(n) {
        room = n;
        last = 0;
        store.set('chat-room', n);

        tabs.forEach((t) => t.setAttribute('aria-selected', Number(t.dataset.room) === n ? 'true' : 'false'));

        list.replaceChildren();
        setEmpty('در حال بارگذاری...');
        showErr('');
        pollNow();
    }

    // ---------- ارسال ----------
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const body = input.value.trim();
        if (!body || sending) return;

        sending = true;
        sendBtn.disabled = true;
        showErr('');

        let res;

        try {
            res = await fetch(`${SEND}/${room}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ body }),
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw data;

            input.value = '';
            pollNow(); // پیام خودت (و پیام‌های تازه‌ی بقیه) با هم میان
        } catch (err) {
            showErr(
                res?.status === 429 ? 'خیلی سریع پیام می‌فرستی؛ چند لحظه صبر کن.'
                : res?.status === 419 ? 'نشستت منقضی شده؛ صفحه رو رفرش کن.'
                : (err?.errors?.body?.[0] ?? 'ارسال نشد؛ دوباره امتحان کن.'),
            );
        } finally {
            sending = false;
            sendBtn.disabled = false;
            input.focus();
        }
    });

    // ---------- اتاق‌ها و پنل ----------
    tabs.forEach((t) => t.addEventListener('click', () => {
        const n = Number(t.dataset.room);
        if (n !== room) setRoom(n);
    }));

    if (panel && fab) {
        const setOpen = (o) => {
            panel.classList.toggle('open', o);
            fab.setAttribute('aria-expanded', o ? 'true' : 'false');

            if (o) {
                list.scrollTop = list.scrollHeight;
                input.focus({ preventScroll: true });
                pollNow();
            }
        };

        fab.addEventListener('click', () => setOpen(!panel.classList.contains('open')));

        panel.querySelector('.chat-close')?.addEventListener('click', () => {
            setOpen(false);
            fab.focus();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && panel.classList.contains('open')) {
                setOpen(false);
                fab.focus();
            }
        });
    }

    document.addEventListener('visibilitychange', () => { if (!document.hidden) pollNow(); });

    setRoom(room);
})();