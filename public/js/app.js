const token = document.querySelector('meta[name="csrf-token"]').content;
const list = document.getElementById('task-list');
const form = document.getElementById('add-form');
const errorBox = document.getElementById('error');

// ========== فیلتر و شمارنده ==========
let currentFilter = 'all';
const remainingEl = document.getElementById('remaining-count');
const totalEl = document.getElementById('total-count');

function applyFilter() {
    list.querySelectorAll('.task').forEach((li) => {
        const isDone = li.dataset.done === '1';
        const show =
            currentFilter === 'all' ||
            (currentFilter === 'done' && isDone) ||
            (currentFilter === 'active' && !isDone);

        li.style.display = show ? '' : 'none';
    });
}

function updateCounter() {
    const tasks = list.querySelectorAll('.task');
    const remaining = list.querySelectorAll('.task[data-done="0"]').length;

    remainingEl.textContent = remaining;
    totalEl.textContent = tasks.length;
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

// ========== ساخت آیتم لیست ==========
function createTaskElement(task) {
    const li = document.createElement('li');
    li.className = 'task' + (task.is_done ? ' done' : '');
    li.dataset.id = task.id;
    li.dataset.done = task.is_done ? 1 : 0;

    const title = document.createElement('span');
    title.className = 'title';
    title.textContent = task.title;

    const actions = document.createElement('div');
    actions.className = 'actions';

    const doneBtn = document.createElement('button');
    doneBtn.type = 'button';
    doneBtn.className = 'btn-done';
    doneBtn.textContent = task.is_done ? 'برگردان' : 'انجام شد';

    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.className = 'btn-delete';
    deleteBtn.textContent = 'حذف';

    actions.append(doneBtn, deleteBtn);
    li.append(title, actions);

    return li;
}

// پیام «لیست خالیه»
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
    errorBox.textContent = '';

    const input = form.querySelector('input[name="title"]');

    try {
        const task = await request(form.action, 'POST', { title: input.value });

        list.querySelector('.empty')?.remove();
        list.prepend(createTaskElement(task));
        input.value = '';
        refreshUI();
    } catch (err) {
        errorBox.textContent = err.errors?.title?.[0] ?? 'خطایی رخ داد.';
    }
});

// ========== انجام شد و حذف ==========
list.addEventListener('click', async (e) => {
    const btn = e.target.closest('button');
    if (!btn) return;

    const li = btn.closest('.task');
    if (!li) return;

    const url = `/tasks/${li.dataset.id}`;

    try {
        if (btn.classList.contains('btn-done')) {
            const newState = li.dataset.done === '1' ? 0 : 1;
            const task = await request(url, 'PUT', { is_done: newState });

            li.dataset.done = task.is_done ? 1 : 0;
            li.classList.toggle('done', task.is_done);
            btn.textContent = task.is_done ? 'برگردان' : 'انجام شد';
            refreshUI();
        }

        if (btn.classList.contains('btn-delete')) {
            await request(url, 'DELETE');
            li.remove();
            updateEmptyState();
            refreshUI();
        }
    } catch (err) {
        errorBox.textContent = 'خطایی رخ داد.';
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
            errorBox.textContent = '';
        } catch (err) {
            errorBox.textContent = err.errors?.title?.[0] ?? 'خطایی رخ داد.';
        }

        input.replaceWith(titleEl);
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