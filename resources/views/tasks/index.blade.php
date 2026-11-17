@extends('layouts.app')

@section('title', 'لیست کارها')

@section('content')
    @php
        $priorityLabels = ['low' => 'کم', 'medium' => 'متوسط', 'high' => 'زیاد'];
    @endphp

    <div class="topbar">
        <span>سلام {{ auth()->user()->name }} 👋</span>
        <div class="topbar-actions">
            <a href="{{ route('board') }}" class="topbar-link">🗂️ کانبان</a>
            <a href="{{ route('stats') }}" class="topbar-link">📊 آمار</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">خروج</button>
            </form>
        </div>
    </div>

    <h1>لیست کارهای من</h1>

    <p class="subtitle">
        <span id="remaining-count">{{ $tasks->where('is_done', false)->count() }}</span> کار باقی مانده
        از <span id="total-count">{{ $tasks->count() }}</span> کار
    </p>

    {{-- نوار پیشرفت --}}
    @php
        $total = $tasks->count();
        $doneCount = $tasks->where('is_done', true)->count();
        $percent = $total > 0 ? round($doneCount / $total * 100) : 0;
    @endphp
    <div class="progress-wrap">
        <div class="progress-info">
            <span>پیشرفت</span>
            <span id="progress-percent">{{ $percent }}٪</span>
        </div>
        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}">
            <div id="progress-bar" class="progress-bar" style="width: {{ $percent }}%"></div>
        </div>
    </div>

    {{-- فرم افزودن؛ با کلیک روی دکمه باز و بسته می‌شود --}}
    <details class="add-task-panel" {{ $errors->any() ? 'open' : '' }}>
        <summary class="add-task-toggle">
            <span class="add-task-toggle-icon" aria-hidden="true">＋</span>
            <span>ساخت تسک جدید</span>
            <span class="add-task-toggle-hint">برای نمایش فرم کلیک کن</span>
            <span class="add-task-chevron" aria-hidden="true">⌄</span>
        </summary>

        <form id="add-form" action="{{ route('tasks.store') }}" method="POST" class="add-form">
            @csrf
            <div class="add-row">
                <input type="text" name="title" placeholder="نام کار؛ مثلاً مطالعه فصل اول" value="{{ old('title') }}" required>
                <button type="submit">ثبت تسک</button>
            </div>

            <div class="add-options">
                <label class="add-option-field">
                    <span>درجه سختی / اولویت</span>
                    <select name="priority" id="priority-select" aria-label="اولویت">
                        <option value="low" @selected(old('priority', 'medium') === 'low')>🔵 کم</option>
                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>🟠 متوسط</option>
                        <option value="high" @selected(old('priority') === 'high')>🔴 زیاد</option>
                    </select>
                </label>

                <label class="add-option-field">
                    <span>دسته‌بندی</span>
                    <select name="category_id" id="category-select" aria-label="دسته">
                        <option value="">بدون دسته</option>
                    </select>
                </label>

                <label class="add-option-field">
                    <span>تاریخ سررسید</span>
                    <input type="date" id="add-due" class="add-due" aria-label="تاریخ سررسید">
                </label>
            </div>
        </form>
    </details>

    {{-- دسته‌ها و ساخت دسته‌ی جدید داخل یک کشوی بازشونده --}}
    <div class="category-bar">
        <details class="category-create-panel">
            <summary class="category-create-toggle">
                <span class="category-create-icon" aria-hidden="true">＋</span>
                <span>دسته‌ها و ساخت دسته جدید</span>
                <span class="category-create-hint">برای نمایش دسته‌ها کلیک کن</span>
                <span class="category-create-chevron" aria-hidden="true">⌄</span>
            </summary>

            <div id="category-chips" class="category-chips">
                <button type="button" class="cat-chip active" data-category-id="all">همه دسته‌ها</button>
            </div>

            <form id="category-form" action="{{ route('categories.store') }}" method="POST" class="category-form">
                @csrf
                <input type="text" name="name" placeholder="نام دسته را وارد کن..." maxlength="40" required>
                <label class="category-color-field">
                    <span>رنگ دسته</span>
                    <input type="color" name="color" value="#8b5cf6" aria-label="رنگ دسته">
                </label>
                <button type="submit" class="cat-add">ثبت دسته</button>
            </form>
        </details>
    </div>

    {{-- جستجو --}}
    <div class="search-box">
        <input type="text" id="search-input" placeholder="جستجو در کارها... (کلید / )" autocomplete="off">
    </div>

    <div class="filters">
        <button type="button" class="filter-btn active" data-filter="all">همه</button>
        <button type="button" class="filter-btn" data-filter="active">انجام‌نشده</button>
        <button type="button" class="filter-btn" data-filter="done">انجام‌شده</button>
    </div>

    <details class="usage-help">
        <summary class="usage-help-toggle" aria-label="نمایش راهنمای استفاده" title="راهنمای استفاده">?</summary>
        <p class="tip">دابل‌کلیک روی عنوان = ویرایش. کلیک روی نشان دسته یا اولویت = تغییر. کلیک روی تاریخ = تقویم. دستگیره‌ی ⋮⋮ = جابه‌جایی. نشان ☑ = زیرتسک‌ها.</p>
    </details>

    <ul id="task-list" class="task-list">
        @forelse ($tasks as $task)
            <li class="task {{ $task->is_done ? 'done' : '' }}"
                data-id="{{ $task->id }}"
                data-done="{{ $task->is_done ? 1 : 0 }}"
                data-category="{{ $task->category_id }}"
                data-due="{{ $task->due_date?->format('Y-m-d') }}">

                <button type="button"
                        class="drag-handle"
                        aria-label="جابه‌جایی (بکش یا فلش بالا و پایین بزن)"
                        title="بکش تا جابه‌جا بشه">⋮⋮</button>

                <button type="button"
                        class="check"
                        role="checkbox"
                        aria-checked="{{ $task->is_done ? 'true' : 'false' }}"
                        aria-label="تغییر وضعیت انجام شدن">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 13l4 4L19 7" />
                    </svg>
                </button>

                <div class="task-main">
                    <span class="title">{{ $task->title }}</span>

                    <div class="task-meta">
                        <button type="button" class="cat-badge empty" title="برای تغییر دسته کلیک کن">بدون دسته</button>

                        <label class="due-badge empty" title="تاریخ سررسید">
                            <span class="due-text">📅 تاریخ</span>
                            <input type="date" class="due-input" value="{{ $task->due_date?->format('Y-m-d') }}" aria-label="تاریخ سررسید">
                        </label>
                    </div>
                </div>

                <button type="button"
                        class="priority priority-{{ $task->priority }}"
                        data-priority="{{ $task->priority }}"
                        title="برای تغییر اولویت کلیک کن">
                    {{ $priorityLabels[$task->priority] ?? 'متوسط' }}
                </button>

                <div class="actions">
                    <button type="button" class="btn-delete">حذف</button>
                </div>
            </li>
        @empty
            <li class="empty">هنوز کاری اضافه نکردی.</li>
        @endforelse
    </ul>

    <p id="no-results" class="no-results" hidden>🔍 موردی پیدا نشد.</p>
@endsection

@push('scripts')
    <script>
        window.APP_CATEGORIES = @json($categories);
        window.APP_SUBTASKS = @json($subtasksByTask);
    </script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    <script src="{{ asset('js/subtasks.js') }}?v={{ filemtime(public_path('js/subtasks.js')) }}"></script>
@endpush