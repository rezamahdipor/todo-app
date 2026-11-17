// ==========================================================
// زیرتسک‌ها (چک‌لیست داخل هر کار)
// این فایل بعد از app.js لود می‌شه و از request / showToast / firstError اون استفاده می‌کنه.
// ==========================================================
(function () {
    const taskList = document.getElementById('task-list');
    if (!taskList) return;

    if (typeof request !== 'function' || typeof showToast !== 'function' || typeof firstError !== 'function') {
        console.warn('subtasks.js: اول باید app.js لود بشه.');
        return;
    }

    const SUBTASKS = window.APP_SUBTASKS ?? {};
    const SVG_NS = 'http://www.w3.org/2000/svg';
    const fa = (n) => Number(n).toLocaleString('fa-IR');

    // ========== ساخت اجزای رابط ==========
    function createSubCheck() {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sub-check';
        btn.setAttribute('role', 'checkbox');
        btn.setAttribute('aria-checked', 'false');
        btn.setAttribute('aria-label', 'تغییر وضعیت زیرتسک');

        const svg = document.createElementNS(SVG_NS, 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('aria-hidden', 'true');

        const path = document.createElementNS(SVG_NS, 'path');
        path.setAttribute('d', 'M5 13l4 4L19 7');

        svg.append(path);
        btn.append(svg);

        return btn;
    }

    function setSubDone(item, isDone) {
        item.dataset.done = isDone ? '1' : '0';
        item.classList.toggle('done', isDone);
        item.querySelector('.sub-check').setAttribute('aria-checked', isDone ? 'true' : 'false');
    }

    function createSubItem(sub) {
        const item = document.createElement('li');
        item.className = 'sub-item';
        item.dataset.id = sub.id;

        const title = document.createElement('span');
        title.className = 'sub-title';
        title.title = 'دابل‌کلیک = ویرایش';
        title.textContent = sub.title;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'sub-del';
        del.title = 'حذف زیرتسک';
        del.setAttribute('aria-label', 'حذف زیرتسک');
        del.textContent = '×';

        item.append(createSubCheck(), title, del);
        setSubDone(item, Boolean(sub.is_done));

        return item;
    }

    function createPanel(subs) {
        const panel = document.createElement('div');
        panel.className = 'subtasks';
        panel.hidden = true;

        // نوار پیشرفت مخصوص همین کار
        const progress = document.createElement('div');
        progress.className = 'sub-progress';
        progress.hidden = true;

        const track = document.createElement('div');
        track.className = 'sub-track';
        track.setAttribute('role', 'progressbar');
        track.setAttribute('aria-valuemin', '0');
        track.setAttribute('aria-valuemax', '100');
        track.setAttribute('aria-label', 'پیشرفت زیرتسک‌ها');

        const bar = document.createElement('div');
        bar.className = 'sub-bar';
        track.append(bar);

        const text = document.createElement('span');
        text.className = 'sub-progress-text';

        progress.append(track, text);

        // لیست زیرتسک‌ها
        const listEl = document.createElement('ul');
        listEl.className = 'sub-list';
        subs.forEach((sub) => listEl.append(createSubItem(sub)));

        // فرم افزودن
        const form = document.createElement('form');
        form.className = 'sub-form';
        form.setAttribute('autocomplete', 'off');

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'sub-input';
        input.placeholder = 'زیرتسک جدید...';
        input.maxLength = 255;
        input.setAttribute('aria-label', 'عنوان زیرتسک');

        const add = document.createElement('button');
        add.type = 'submit';
        add.className = 'sub-add';
        add.textContent = '+ افزودن';

        form.append(input, add);
        panel.append(progress, listEl, form);

        return panel;
    }

    // ========== بروزرسانی نشان و نوار پیشرفت ==========
    function refreshProgress(li) {
        const total = li.querySelectorAll('.sub-item').length;
        const done = li.querySelectorAll('.sub-item[data-done="1"]').length;
        const percent = total > 0 ? Math.round((done / total) * 100) : 0;
        const complete = total > 0 && done === total;

        const badge = li.querySelector('.sub-badge');
        badge.textContent = total > 0 ? `☑ ${fa(done)}/${fa(total)}` : '☑ زیرتسک';
        badge.classList.toggle('has-items', total > 0);
        badge.classList.toggle('complete', complete);

        li.querySelector('.sub-progress').hidden = total === 0;
        li.querySelector('.sub-track').setAttribute('aria-valuenow', percent);

        const bar = li.querySelector('.sub-bar');
        bar.style.width = percent + '%';
        bar.classList.toggle('complete', complete);

        li.querySelector('.sub-progress-text').textContent =
            `${fa(done)} از ${fa(total)} · ${fa(percent)}٪`;
    }

    // ==========================================================
    // انجام‌شدن خودکار کار اصلی (تمرین ۲)
    // ==========================================================
    const autoDone = new Set(); // شماره‌ی کارهایی که «خودکار» انجام‌شده شدن
    const syncing = new Set();  // کارهایی که همین الان درخواستشون در راهه
    const again = new Set();    // کارهایی که وسط درخواست، دوباره تغییر کردن

    function countsOf(li) {
        const total = li.querySelectorAll('.sub-item').length;
        const done = li.querySelectorAll('.sub-item[data-done="1"]').length;

        return { total, done, complete: total > 0 && done === total };
    }

    // ظاهر کار اصلی رو (بعد از جواب سرور) عوض می‌کنه
    function paintParent(li, isDone) {
        li.dataset.done = isDone ? '1' : '0';
        li.classList.toggle('done', isDone);
        li.querySelector('.check')?.setAttribute('aria-checked', isDone ? 'true' : 'false');

        if (typeof renderDue === 'function') renderDue(li);
        if (typeof refreshUI === 'function') refreshUI();
    }

    // وضعیت زیرتسک‌ها رو با وضعیت کار اصلی مقایسه می‌کنه و اگه لازم بود درستش می‌کنه
    async function applyParentState(li) {
        if (!li.isConnected) return; // کار وسط راه حذف شده

        const id = li.dataset.id;
        const { total, complete } = countsOf(li);
        const parentDone = li.dataset.done === '1';

        // کار بدون زیرتسک: کاری باهاش نداریم
        if (total === 0) {
            autoDone.delete(id);
            return;
        }

        // همه‌ی زیرتسک‌ها تیک خورده ولی کار اصلی نه: انجامش بده
        if (complete && !parentDone) {
            const task = await request(`/tasks/${id}`, 'PUT', { is_done: 1 });

            autoDone.add(id);
            paintParent(li, Boolean(task.is_done));

            const remaining = taskList.querySelectorAll('.task[data-done="0"]').length;

            if (remaining === 0) {
                if (typeof launchConfetti === 'function') launchConfetti();
                showToast('همه‌ی کارها انجام شد 🎉', 'success', 4000);
            } else {
                showToast('همه‌ی زیرتسک‌ها تموم شد؛ کار اصلی هم انجام شد ✅');
            }

            return;
        }

        // یه زیرتسک باز شد (یا اضافه شد) و کار اصلی رو خودمون تیک زده بودیم: دوباره بازش کن
        if (!complete && parentDone && autoDone.has(id)) {
            const task = await request(`/tasks/${id}`, 'PUT', { is_done: 0 });

            autoDone.delete(id);
            paintParent(li, Boolean(task.is_done));
            showToast('کار اصلی دوباره باز شد');
        }
    }

    // اگه پشت سر هم چند تا تغییر بشه، درخواست‌ها قاطی نمی‌شن
    async function syncParent(li) {
        const id = li.dataset.id;

        if (syncing.has(id)) {
            again.add(id);
            return;
        }

        syncing.add(id);

        try {
            do {
                again.delete(id);
                await applyParentState(li);
            } while (again.has(id));
        } catch (err) {
            showToast('وضعیت کار اصلی ذخیره نشد.', 'error');
        } finally {
            again.delete(id);
            syncing.delete(id);
        }
    }

    // اگه خودت دستی کار اصلی رو تیک زدی یا برداشتی، دیگه «خودکار» حسابش نمی‌کنیم
    taskList.addEventListener('click', (e) => {
        const check = e.target.closest('.check');
        if (check) autoDone.delete(check.closest('.task').dataset.id);
    });

    // ========== اضافه کردن بخش زیرتسک به یه کار ==========
    function initTask(li) {
        if (li.querySelector('.subtasks')) return; // قبلاً ساخته شده

        const meta = li.querySelector('.task-meta');
        if (!meta) return;

        const badge = document.createElement('button');
        badge.type = 'button';
        badge.className = 'sub-badge';
        badge.title = 'زیرتسک‌ها (چک‌لیست این کار)';
        badge.setAttribute('aria-expanded', 'false');
        meta.append(badge);

        li.append(createPanel(SUBTASKS[li.dataset.id] ?? []));
        refreshProgress(li);
    }

    // ========== کلیک‌ها: باز و بسته کردن، تیک، حذف ==========
    taskList.addEventListener('click', async (e) => {
        const badge = e.target.closest('.sub-badge');

        if (badge) {
            const panel = badge.closest('.task').querySelector('.subtasks');
            panel.hidden = !panel.hidden;
            badge.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');

            if (!panel.hidden) panel.querySelector('.sub-input').focus();
            return;
        }

        const check = e.target.closest('.sub-check');
        const del = e.target.closest('.sub-del');
        if (!check && !del) return;

        const item = e.target.closest('.sub-item');
        const li = item.closest('.task');
        const url = `/subtasks/${item.dataset.id}`;

        try {
            if (check) {
                const newState = item.dataset.done === '1' ? 0 : 1;
                const sub = await request(url, 'PUT', { is_done: newState });

                setSubDone(item, Boolean(sub.is_done));
                refreshProgress(li);
                syncParent(li);
            }

            if (del) {
                await request(url, 'DELETE');

                item.remove();
                refreshProgress(li);
                showToast('زیرتسک حذف شد');
                syncParent(li);
            }
        } catch (err) {
            showToast(firstError(err), 'error');
        }
    });

    // ========== افزودن زیرتسک ==========
    taskList.addEventListener('submit', async (e) => {
        const form = e.target.closest('.sub-form');
        if (!form) return;

        e.preventDefault();

        const li = form.closest('.task');
        const input = form.querySelector('.sub-input');
        const title = input.value.trim();

        if (title === '') return;

        try {
            const sub = await request(`/tasks/${li.dataset.id}/subtasks`, 'POST', { title });

            li.querySelector('.sub-list').append(createSubItem(sub));
            input.value = '';
            input.focus();
            refreshProgress(li);
            syncParent(li);
        } catch (err) {
            showToast(firstError(err), 'error');
        }
    });

    // ========== ویرایش زیرتسک (دابل‌کلیک) ==========
    function startSubEdit(item) {
        const titleEl = item.querySelector('.sub-title');
        const oldTitle = titleEl.textContent;

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'sub-edit';
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
                const sub = await request(`/subtasks/${item.dataset.id}`, 'PUT', { title: newTitle });
                titleEl.textContent = sub.title;
                showToast('زیرتسک ویرایش شد');
            } catch (err) {
                showToast(firstError(err), 'error');
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

    taskList.addEventListener('dblclick', (e) => {
        const titleEl = e.target.closest('.sub-title');
        if (titleEl) startSubEdit(titleEl.closest('.sub-item'));
    });

    // ========== راه‌اندازی ==========
    // ۱) کارهایی که از سرور اومدن
    taskList.querySelectorAll('.task').forEach(initTask);

    // ۲) کارهایی که بعداً با fetch اضافه می‌شن (بدون دست زدن به app.js)
    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && node.classList.contains('task')) {
                    initTask(node);
                }
            });
        });
    }).observe(taskList, { childList: true });
})();