const token = document.querySelector('meta[name="csrf-token"]').content;
const list = document.getElementById('task-list');
const form = document.getElementById('add-form');
const prioritySelect = document.getElementById('priority-select');
const categorySelect = document.getElementById('category-select');
const addDue = document.getElementById('add-due');
const categoryForm = document.getElementById('category-form');
const categoryChips = document.getElementById('category-chips');

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

// اولین پیام خطای اعتبارسنجی سرور
function firstError(err) {
    return Object.values(err?.errors ?? {})[0]?.[0] ?? 'خطایی رخ داد.';
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

// ========== دسته‌ها ==========
let categories = window.APP_CATEGORIES ?? [];
let currentCategory = 'all';

const findCategory = (id) => categories.find((c) => String(c.id) === String(id));

function renderCategory(li) {
    const badge = li.querySelector('.cat-badge');
    const cat = findCategory(li.dataset.category);

    if (cat) {
        badge.textContent = cat.name;
        badge.style.setProperty('--cat', cat.color);
        badge.classList.remove('empty');
    } else {
        li.dataset.category = '';
        badge.textContent = 'بدون دسته';
        badge.style.removeProperty('--cat');
        badge.classList.add('empty');
    }
}

function addCategoryChip(cat) {
    const wrap = document.createElement('span');
    wrap.className = 'cat-chip-wrap';
    wrap.dataset.categoryId = cat.id;

    const chip = document.createElement('button');
    chip.type = 'button';
    chip.className = 'cat-chip';
    chip.dataset.categoryId = cat.id;
    chip.style.setProperty('--cat', cat.color);
    chip.textContent = cat.name;

    const del = document.createElement('button');
    del.type = 'button';
    del.className = 'cat-del';
    del.title = 'حذف دسته';
    del.setAttribute('aria-label', `حذف دسته ${cat.name}`);
    del.textContent = '×';

    wrap.append(chip, del);
    categoryChips.append(wrap);
}

function addCategoryOption(cat) {
    const option = document.createElement('option');
    option.value = cat.id;
    option.textContent = cat.name;
    categorySelect.append(option);
}

function setCategoryFilter(id) {
    currentCategory = String(id);

    categoryChips.querySelectorAll('.cat-chip').forEach((chip) => {
        chip.classList.toggle('active', chip.dataset.categoryId === currentCategory);
    });

    applyFilter();
}

// ========== تاریخ سررسید ==========
const DAY_MS = 86400000;

function parseLocalDate(str) {
    const [y, m, d] = str.split('-').map(Number);
    return new Date(y, m - 1, d);
}

function daysFromToday(str) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return Math.round((parseLocalDate(str) - today) / DAY_MS);
}

function dueLabel(str) {
    const diff = daysFromToday(str);
    const fa = (n) => n.toLocaleString('fa-IR');

    if (diff === 0) return 'امروز';
    if (diff === 1) return 'فردا';
    if (diff === -1) return 'دیروز';
    if (diff < 0) return `${fa(Math.abs(diff))} روز گذشته`;
    if (diff <= 7) return `${fa(diff)} روز دیگر`;

    return parseLocalDate(str).toLocaleDateString('fa-IR', { month: 'long', day: 'numeric' });
}

function renderDue(li) {
    const badge = li.querySelector('.due-badge');
    const text = badge.querySelector('.due-text');
    const input = badge.querySelector('.due-input');
    const due = li.dataset.due || '';

    input.value = due;
    badge.classList.remove('empty', 'overdue', 'today', 'soon');

    if (!due) {
        text.textContent = '📅 تاریخ';
        badge.classList.add('empty');
        return;
    }

    text.textContent = '📅 ' + dueLabel(due);

    // رنگ هشدار فقط برای کارهای انجام‌نشده
    if (li.dataset.done !== '1') {
        const diff = daysFromToday(due);
        if (diff < 0) badge.classList.add('overdue');
        else if (diff === 0) badge.classList.add('today');
        else if (diff <= 2) badge.classList.add('soon');
    }
}

function renderTaskMeta(li) {
    renderCategory(li);
    renderDue(li);
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

        const matchesCategory =
            currentCategory === 'all' || li.dataset.category === currentCategory;

        const matchesSearch = q === '' || title.includes(q);
        const show = matchesFilter && matchesCategory && matchesSearch;

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

        if (y < 80) window.scrollBy(0, -12);
        if (y > window.innerHeight - 80) window.scrollBy(0, 12);

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

// ========== ساخت آیتم لیست ==========
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
    li.dataset.category = task.category_id ?? '';
    li.dataset.due = task.due_date ?? '';

    const handle = createDragHandle();
    const check = createCheckButton(task.is_done);

    const main = document.createElement('div');
    main.className = 'task-main';

    const title = document.createElement('span');
    title.className = 'title';
    title.textContent = task.title;

    const meta = document.createElement('div');
    meta.className = 'task-meta';

    const catBadge = document.createElement('button');
    catBadge.type = 'button';
    catBadge.className = 'cat-badge empty';
    catBadge.title = 'برای تغییر دسته کلیک کن';

    const dueBadge = document.createElement('label');
    dueBadge.className = 'due-badge empty';
    dueBadge.title = 'تاریخ سررسید';

    const dueText = document.createElement('span');
    dueText.className = 'due-text';

    const dueInput = document.createElement('input');
    dueInput.type = 'date';
    dueInput.className = 'due-input';
    dueInput.setAttribute('aria-label', 'تاریخ سررسید');

    dueBadge.append(dueText, dueInput);
    meta.append(catBadge, dueBadge);
    main.append(title, meta);

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
    li.append(handle, check, main, priority, actions);

    renderTaskMeta(li);

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
            category_id: categorySelect.value || null,
            due_date: addDue.value || null,
        });

        list.querySelector('.empty')?.remove();
        list.prepend(createTaskElement(task));

        savedOrder.unshift(task.id);

        input.value = '';
        prioritySelect.value = 'medium';
        addDue.value = '';
        refreshUI();
        showToast('کار اضافه شد');
    } catch (err) {
        showToast(firstError(err), 'error');
    }
});

// ========== ساخت و حذف دسته ==========
categoryForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const nameInput = categoryForm.querySelector('input[name="name"]');
    const colorInput = categoryForm.querySelector('input[name="color"]');

    try {
        const cat = await request(categoryForm.action, 'POST', {
            name: nameInput.value,
            color: colorInput.value,
        });

        categories.push(cat);
        addCategoryChip(cat);
        addCategoryOption(cat);

        nameInput.value = '';
        showToast('دسته ساخته شد');
    } catch (err) {
        showToast(firstError(err), 'error');
    }
});

categoryChips.addEventListener('click', async (e) => {
    const del = e.target.closest('.cat-del');
    const chip = e.target.closest('.cat-chip');

    if (del) {
        const wrap = del.closest('.cat-chip-wrap');
        const id = wrap.dataset.categoryId;

        if (!confirm('این دسته حذف بشه؟ کارهاش پاک نمی‌شن و «بدون دسته» می‌شن.')) return;

        try {
            await request(`/categories/${id}`, 'DELETE');

            categories = categories.filter((c) => String(c.id) !== String(id));
            wrap.remove();
            categorySelect.querySelector(`option[value="${id}"]`)?.remove();

            list.querySelectorAll(`.task[data-category="${id}"]`).forEach((li) => {
                li.dataset.category = '';
                renderCategory(li);
            });

            if (currentCategory === String(id)) {
                setCategoryFilter('all');
            } else {
                applyFilter();
            }

            showToast('دسته حذف شد');
        } catch (err) {
            showToast('خطایی رخ داد.', 'error');
        }

        return;
    }

    if (chip) {
        setCategoryFilter(chip.dataset.categoryId);
    }
});

// ========== تیک زدن، دسته، اولویت و حذف ==========
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
            renderDue(li);
            refreshUI();

            const remaining = list.querySelectorAll('.task[data-done="0"]').length;
            if (task.is_done && remaining === 0) {
                launchConfetti();
                showToast('همه‌ی کارها انجام شد 🎉', 'success', 4000);
            }
        }

        if (btn.classList.contains('cat-badge')) {
            if (categories.length === 0) {
                showToast('اول یه دسته بساز', 'error');
                return;
            }

            const ids = [null, ...categories.map((c) => c.id)];
            const current = li.dataset.category ? Number(li.dataset.category) : null;
            const next = ids[(ids.indexOf(current) + 1) % ids.length];

            const task = await request(url, 'PUT', { category_id: next });

            li.dataset.category = task.category_id ?? '';
            renderCategory(li);
            applyFilter();
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

// ========== تغییر تاریخ سررسید ==========
// کلیک روی نشان تاریخ: باز کردن تقویم
list.addEventListener('click', (e) => {
    if (!e.target.classList.contains('due-input')) return;

    try {
        e.target.showPicker?.();
    } catch (err) {}
});

list.addEventListener('change', async (e) => {
    if (!e.target.classList.contains('due-input')) return;

    const li = e.target.closest('.task');

    try {
        const task = await request(`/tasks/${li.dataset.id}`, 'PUT', {
            due_date: e.target.value || null,
        });

        li.dataset.due = task.due_date ?? '';
        renderDue(li);
        showToast(task.due_date ? 'تاریخ ثبت شد' : 'تاریخ پاک شد');
    } catch (err) {
        renderDue(li);
        showToast(firstError(err), 'error');
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
            showToast(firstError(err), 'error');
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

// ========== راه‌اندازی اولیه ==========
categories.forEach((cat) => {
    addCategoryChip(cat);
    addCategoryOption(cat);
});

list.querySelectorAll('.task').forEach(renderTaskMeta);