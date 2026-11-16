const token = document.querySelector('meta[name="csrf-token"]').content;
const list = document.getElementById('task-list');
const form = document.getElementById('add-form');
const prioritySelect = document.getElementById('priority-select');

// ========== اولویت‌ها ==========
const PRIORITY_ORDER = ['low', 'medium', 'high'];
const PRIORITY_LABELS = { low: 'کم', medium: 'متوسط', high: 'زیاد' };

function setPriority(btn, priority) {
    btn.dataset.priority = priority;
    btn.className = `priority priority-${priority}`;
    btn.textContent = PRIORITY_LABELS[priority] ?? PRIORITY_LABELS.medium;
}

// ========== Toast (پیام شناور) ==========
const toastContainer = document.createElement('div');
toastContainer.className = 'toast-container';
toastContainer.setAttribute('aria-live', 'polite');
document.body.append(toastContainer);

const TOAST_ICONS = { success: '✓', error: '⚠' };

function showToast(message, type = 'success', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

    const icon = document.createElement('span');
    icon.className = 'toast-icon';
    icon.textContent = TOAST_ICONS[type] ?? '•';

    const text = document.createElement('span');
    text.className = 'toast-text';
    text.textContent = message;

    toast.append(icon, text);
    toastContainer.append(toast);

    requestAnimationFrame(() => toast.classList.add('show'));

    let closed = false;

    function close() {
        if (closed) return;
        closed = true;
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }

    toast.addEventListener('click', close);
    setTimeout(close, duration);
}

// ========== کانفتی 🎉 ==========
const CONFETTI_COLORS = ['#6366f1', '#ec4899', '#22d3ee', '#f59e0b', '#22c55e', '#ef4444'];
const CONFETTI_EMOJIS = ['💗', '💕', '🎀', '🌸', '✨', '💖'];

function launchConfetti() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const isPink = document.documentElement.dataset.theme === 'pink';
    const rand = (min, max) => Math.random() * (max - min) + min;

    const layer = document.createElement('div');
    layer.className = 'confetti-layer';
    layer.setAttribute('aria-hidden', 'true');

    const count = isPink ? 32 : 60;

    for (let i = 0; i < count; i++) {
        const piece = document.createElement('span');
        piece.className = 'confetti-piece';

        piece.style.left = rand(0, 100) + '%';
        piece.style.setProperty('--dx', rand(-120, 120) + 'px');
        piece.style.setProperty('--rot', rand(-720, 720) + 'deg');
        piece.style.setProperty('--dur', rand(2.2, 3.8) + 's');
        piece.style.setProperty('--delay', rand(0, 0.5) + 's');

        if (isPink) {
            piece.textContent = CONFETTI_EMOJIS[Math.floor(rand(0, CONFETTI_EMOJIS.length))];
            piece.style.fontSize = rand(16, 30) + 'px';
        } else {
            piece.style.width = rand(6, 11) + 'px';
            piece.style.height = rand(10, 18) + 'px';
            piece.style.background = CONFETTI_COLORS[Math.floor(rand(0, CONFETTI_COLORS.length))];
            piece.style.borderRadius = Math.random() > 0.5 ? '2px' : '50%';
        }

        layer.append(piece);
    }

    document.body.append(layer);
    setTimeout(() => layer.remove(), 5000);
}

// ========== فیلتر، جستجو، شمارنده و نوار پیشرفت ==========
let currentFilter = 'all';
let searchQuery = '';

const remainingEl = document.getElementById('remaining-count');
const totalEl = document.getElementById('total-count');
const progressBar = document.getElementById('progress-bar');
const progressPercent = document.getElementById('progress-percent');
const progressWrap = document.querySelector('.progress');
const searchInput = document.getElementById('search-input');
const noResults = document.getElementById('no-results');

function applyFilter() {
    const q = searchQuery.trim().toLowerCase();
    let visible = 0;

    list.querySelectorAll('.task').forEach((li) => {
        const isDone = li.dataset.done === '1';
        const title = li.querySelector('.title')?.textContent.toLowerCase() ?? '';

        const matchesFilter =
            currentFilter === 'all' ||
            (currentFilter === 'done' && isDone) ||
            (currentFilter === 'active' && !isDone);

        const matchesSearch = q === '' || title.includes(q);
        const show = matchesFilter && matchesSearch;

        li.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const total = list.querySelectorAll('.task').length;
    noResults.hidden = !(total > 0 && visible === 0);
}

function updateCounter() {
    const tasks = list.querySelectorAll('.task');
    const remaining = list.querySelectorAll('.task[data-done="0"]').length;
    const total = tasks.length;
    const done = total - remaining;
    const percent = total > 0 ? Math.round((done / total) * 100) : 0;

    remainingEl.textContent = remaining;
    totalEl.textContent = total;

    progressBar.style.width = percent + '%';
    progressPercent.textContent = percent + '٪';
    progressWrap.setAttribute('aria-valuenow', percent);
    progressBar.classList.toggle('complete', total > 0 && percent === 100);
}

function refreshUI() {
    updateCounter();
    applyFilter();
}

document.querySelectorAll('.filter-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        currentFilter = btn.dataset.filter;

        document.querySelectorAll('.filter-btn').forEach((b) => {
            b.classList.toggle('active', b === btn);
        });

        applyFilter();
    });
});

searchInput.addEventListener('input', () => {
    searchQuery = searchInput.value;
    applyFilter();
});

document.addEventListener('keydown', (e) => {
    const tag = document.activeElement.tagName;
    const typing = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

    if (e.key === '/' && !typing) {
        e.preventDefault();
        searchInput.focus();
    }

    if (e.key === 'Escape' && document.activeElement === searchInput) {
        searchInput.value = '';
        searchQuery = '';
        applyFilter();
        searchInput.blur();
    }
});

// ========== درخواست به سرور ==========
async function request(url, method, body = null) {
    const response = await fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: body ? JSON.stringify(body) : null,
    });

    const data = await response.json();

    if (!response.ok) {
        throw data;
    }

    return data;
}

// ========== جابه‌جایی تسک‌ها (درگ‌اند‌دراپ ماندگار) ==========
function currentOrder() {
    return [...list.querySelectorAll('.task')].map((li) => Number(li.dataset.id));
}

// آخرین ترتیبی که روی سرور ذخیره شده
let savedOrder = currentOrder();
let saveTimer = null;

function restoreOrder() {
    savedOrder.forEach((id) => {
        const li = list.querySelector(`.task[data-id="${id}"]`);
        if (li) list.append(li);
    });
}

async function saveOrder() {
    const order = currentOrder();
    if (order.join(',') === savedOrder.join(',')) return;

    try {
        await request('/tasks/reorder', 'POST', { order });
        savedOrder = order;
        showToast('ترتیب ذخیره شد', 'success', 1500);
    } catch (err) {
        restoreOrder();
        showToast('ذخیره‌ی ترتیب ناموفق بود', 'error');
    }
}

function scheduleSave() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveOrder, 400);
}

// نزدیک‌ترین تسک قابل‌دیدن قبل (-1) یا بعد (1)
function visibleSibling(li, dir) {
    let el = dir < 0 ? li.previousElementSibling : li.nextElementSibling;

    while (el) {
        if (el.classList.contains('task') && el.style.display !== 'none' && !el.classList.contains('removing')) {
            return el;
        }
        el = dir < 0 ? el.previousElementSibling : el.nextElementSibling;
    }

    return null;
}

function middleY(el) {
    const r = el.getBoundingClientRect();
    return r.top + r.height / 2;
}

// کشیدن با موس یا لمس
list.addEventListener('pointerdown', (e) => {
    const handle = e.target.closest('.drag-handle');
    if (!handle) return;
    if (e.pointerType === 'mouse' && e.button !== 0) return;

    const li = handle.closest('.task');
    if (!li) return;

    e.preventDefault();

    li.classList.add('dragging');
    document.body.classList.add('is-dragging');

    function onMove(ev) {
        const y = ev.clientY;

        // اسکرول خودکار نزدیک لبه‌ها
        if (y < 80) window.scrollBy(0, -12);
        if (y > window.innerHeight - 80) window.scrollBy(0, 12);

        // به‌جای جابه‌جا کردن خود li، همسایه رو جابه‌جا می‌کنیم
        // تا المانی که کاربر داره می‌کشه هیچ‌وقت از DOM بیرون نره
        let prev = visibleSibling(li, -1);
        while (prev && y < middleY(prev)) {
            li.after(prev);
            prev = visibleSibling(li, -1);
        }

        let next = visibleSibling(li, 1);
        while (next && y > middleY(next)) {
            li.before(next);
            next = visibleSibling(li, 1);
        }
    }

    function onEnd() {
        document.removeEventListener('pointermove', onMove);
        document.removeEventListener('pointerup', onEnd);
        document.removeEventListener('pointercancel', onEnd);

        li.classList.remove('dragging');
        document.body.classList.remove('is-dragging');

        saveOrder();
    }

    document.addEventListener('pointermove', onMove);
    document.addEventListener('pointerup', onEnd);
    document.addEventListener('pointercancel', onEnd);
});

// جابه‌جایی با کیبورد: فلش بالا و پایین روی دستگیره
list.addEventListener('keydown', (e) => {
    const handle = e.target.closest('.drag-handle');
    if (!handle) return;
    if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') return;

    e.preventDefault();

    const li = handle.closest('.task');

    if (e.key === 'ArrowUp') {
        const prev = visibleSibling(li, -1);
        if (prev) li.after(prev);
    } else {
        const next = visibleSibling(li, 1);
        if (next) li.before(next);
    }

    handle.focus();
    scheduleSave();
});

// ========== ساخت آیتم لیست (دستگیره + چک‌باکس گرد + نشان اولویت) ==========
const SVG_NS = 'http://www.w3.org/2000/svg';

function createCheckButton(isDone) {
    const check = document.createElement('button');
    check.type = 'button';
    check.className = 'check';
    check.setAttribute('role', 'checkbox');
    check.setAttribute('aria-checked', isDone ? 'true' : 'false');
    check.setAttribute('aria-label', 'تغییر وضعیت انجام شدن');

    const svg = document.createElementNS(SVG_NS, 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('aria-hidden', 'true');

    const path = document.createElementNS(SVG_NS, 'path');
    path.setAttribute('d', 'M5 13l4 4L19 7');

    svg.append(path);
    check.append(svg);

    return check;
}

function createDragHandle() {
    const handle = document.createElement('button');
    handle.type = 'button';
    handle.className = 'drag-handle';
    handle.setAttribute('aria-label', 'جابه‌جایی (بکش یا فلش بالا و پایین بزن)');
    handle.title = 'بکش تا جابه‌جا بشه';
    handle.textContent = '⋮⋮';

    return handle;
}

function createTaskElement(task) {
    const li = document.createElement('li');
    li.className = 'task' + (task.is_done ? ' done' : '');
    li.dataset.id = task.id;
    li.dataset.done = task.is_done ? 1 : 0;

    const handle = createDragHandle();
    const check = createCheckButton(task.is_done);

    const title = document.createElement('span');
    title.className = 'title';
    title.textContent = task.title;

    const priority = document.createElement('button');
    priority.type = 'button';
    priority.title = 'برای تغییر اولویت کلیک کن';
    setPriority(priority, task.priority ?? 'medium');

    const actions = document.createElement('div');
    actions.className = 'actions';

    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.className = 'btn-delete';
    deleteBtn.textContent = 'حذف';

    actions.append(deleteBtn);
    li.append(handle, check, title, priority, actions);

    return li;
}

function animateRemove(li) {
    return new Promise((resolve) => {
        li.style.height = li.offsetHeight + 'px';
        li.offsetHeight;

        li.classList.add('removing');

        setTimeout(() => {
            li.remove();
            resolve();
        }, 350);
    });
}

function updateEmptyState() {
    const hasTasks = list.querySelector('.task');
    const empty = list.querySelector('.empty');

    if (!hasTasks && !empty) {
        const li = document.createElement('li');
        li.className = 'empty';
        li.textContent = 'هنوز کاری اضافه نکردی.';
        list.append(li);
    }
}

// ========== افزودن تسک ==========
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const input = form.querySelector('input[name="title"]');

    try {
        const task = await request(form.action, 'POST', {
            title: input.value,
            priority: prioritySelect.value,
        });

        list.querySelector('.empty')?.remove();
        list.prepend(createTaskElement(task));

        // سرور تسک جدید رو بالای لیست گذاشته
        savedOrder.unshift(task.id);

        input.value = '';
        prioritySelect.value = 'medium';
        refreshUI();
        showToast('کار اضافه شد');
    } catch (err) {
        showToast(err.errors?.title?.[0] ?? 'خطایی رخ داد.', 'error');
    }
});

// ========== تیک زدن، تغییر اولویت و حذف ==========
list.addEventListener('click', async (e) => {
    const btn = e.target.closest('button');
    if (!btn) return;

    const li = btn.closest('.task');
    if (!li) return;

    const url = `/tasks/${li.dataset.id}`;

    try {
        if (btn.classList.contains('check')) {
            const newState = li.dataset.done === '1' ? 0 : 1;
            const task = await request(url, 'PUT', { is_done: newState });

            li.dataset.done = task.is_done ? 1 : 0;
            li.classList.toggle('done', task.is_done);
            btn.setAttribute('aria-checked', task.is_done ? 'true' : 'false');
            refreshUI();

            const remaining = list.querySelectorAll('.task[data-done="0"]').length;
            if (task.is_done && remaining === 0) {
                launchConfetti();
                showToast('همه‌ی کارها انجام شد 🎉', 'success', 4000);
            }
        }

        if (btn.classList.contains('priority')) {
            const current = btn.dataset.priority;
            const next = PRIORITY_ORDER[(PRIORITY_ORDER.indexOf(current) + 1) % PRIORITY_ORDER.length];

            const task = await request(url, 'PUT', { priority: next });
            setPriority(btn, task.priority);
            showToast(`اولویت: ${PRIORITY_LABELS[task.priority]}`);
        }

        if (btn.classList.contains('btn-delete')) {
            if (li.classList.contains('removing')) return;

            const id = Number(li.dataset.id);

            await request(url, 'DELETE');
            savedOrder = savedOrder.filter((x) => x !== id);

            await animateRemove(li);
            updateEmptyState();
            refreshUI();
            showToast('کار حذف شد');
        }
    } catch (err) {
        showToast('خطایی رخ داد.', 'error');
    }
});

// ========== ویرایش تسک ==========
function startEdit(li) {
    const titleEl = li.querySelector('.title');
    const oldTitle = titleEl.textContent;

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'edit-input';
    input.value = oldTitle;
    input.maxLength = 255;

    titleEl.replaceWith(input);
    input.focus();
    input.select();

    let finished = false;

    async function save() {
        if (finished) return;
        finished = true;

        const newTitle = input.value.trim();

        if (newTitle === '' || newTitle === oldTitle) {
            input.replaceWith(titleEl);
            return;
        }

        try {
            const task = await request(`/tasks/${li.dataset.id}`, 'PUT', { title: newTitle });
            titleEl.textContent = task.title;
            showToast('کار ویرایش شد');
        } catch (err) {
            showToast(err.errors?.title?.[0] ?? 'خطایی رخ داد.', 'error');
        }

        input.replaceWith(titleEl);
        applyFilter();
    }

    function cancel() {
        if (finished) return;
        finished = true;
        input.replaceWith(titleEl);
    }

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') save();
        if (e.key === 'Escape') cancel();
    });

    input.addEventListener('blur', save);
}

list.addEventListener('dblclick', (e) => {
    if (e.target.classList.contains('title')) {
        startEdit(e.target.closest('.task'));
    }
});