@extends('layouts.app')

@section('title', 'لیست کارها')
@section('container-class', 'container-dash')

@section('content')
    @php
        $priorityLabels = ['low' => 'کم', 'medium' => 'متوسط', 'high' => 'زیاد'];
        $columns = ['todo' => '📝 باقی‌مانده', 'doing' => '🔄 در حال انجام', 'done' => '✅ انجام‌شده'];

        $total = $tasks->count();
        $doneCount = $tasks->where('is_done', true)->count();
        $percent = $total > 0 ? round($doneCount / $total * 100) : 0;
    @endphp

    <div class="topbar">
        <span>سلام {{ auth()->user()->name }} 👋</span>
        <div class="topbar-actions">
            <a href="{{ route('habits') }}" class="topbar-link">🔥 عادت‌ها</a>
            <a href="{{ route('dashboard') }}" class="topbar-link">🏠 داشبورد</a>
            <a href="{{ route('board') }}" class="topbar-link">🗂️ کانبان</a>
            <a href="{{ route('stats') }}" class="topbar-link">📊 آمار</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">خروج</button>
            </form>
        </div>
    </div>

    <div class="dash">

        {{-- راست: کانبان زنده --}}
        <aside class="dash-side side-kanban" id="side-kanban">
            <section class="panel">
                <h2>🗂️ کانبان زنده</h2>
                @foreach ($columns as $key => $label)
                    <div class="mk-col">
                        <h3 class="mk-head">
                            <span>{{ $label }}</span>
                            <span class="mk-count" data-status="{{ $key }}">0</span>
                        </h3>
                        <ul class="mk-list" data-status="{{ $key }}"></ul>
                    </div>
                @endforeach
            </section>
        </aside>

        {{-- وسط: لیست کارها --}}
        <div class="dash-main">
            <h1>لیست کارهای من</h1>

            <p class="subtitle">
                <span id="remaining-count">{{ $tasks->where('is_done', false)->count() }}</span> کار باقی مانده
                از <span id="total-count">{{ $tasks->count() }}</span> کار
            </p>

            {{-- نوار پیشرفت --}}
            <div class="progress-wrap">
                <div class="progress-info">
                    <span>پیشرفت</span>
                    <span id="progress-percent">{{ $percent }}٪</span>
                </div>
                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}">
                    <div id="progress-bar" class="progress-bar" style="width: {{ $percent }}%"></div>
                </div>
            </div>

            {{-- کشویی: افزودن کار --}}
            <div class="fold" id="fold-add">
                <button type="button" class="fold-toggle" aria-expanded="false" aria-controls="add-body">
                    <span>➕ کار جدید</span>
                    <span class="fold-arrow" aria-hidden="true">▾</span>
                </button>
                <div class="fold-body" id="add-body">
                    <div class="fold-inner">
                        <form id="add-form" action="{{ route('tasks.store') }}" method="POST" class="add-form">
                            @csrf
                            <div class="add-row">
                                <input type="text" name="title" placeholder="امروز چه کاری داری؟" value="{{ old('title') }}" required>
                                <button type="submit">افزودن</button>
                            </div>

                            <div class="add-options">
                                <select name="priority" id="priority-select" aria-label="اولویت">
                                    <option value="low">🔵 کم</option>
                                    <option value="medium" selected>🟠 متوسط</option>
                                    <option value="high">🔴 زیاد</option>
                                </select>

                                <select name="category_id" id="category-select" aria-label="دسته">
                                    <option value="">بدون دسته</option>
                                </select>

                                <input type="date" id="add-due" class="add-due" aria-label="تاریخ سررسید">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- کشویی: دسته‌ها --}}
            <div class="fold" id="fold-cat">
                <button type="button" class="fold-toggle" aria-expanded="false" aria-controls="cat-body">
                    <span>🏷️ دسته‌ها</span>
                    <span class="fold-arrow" aria-hidden="true">▾</span>
                </button>
                <div class="fold-body" id="cat-body">
                    <div class="fold-inner">
                        <div class="category-bar">
                            <div id="category-chips" class="category-chips">
                                <button type="button" class="cat-chip active" data-category-id="all">همه دسته‌ها</button>
                            </div>

                            <form id="category-form" action="{{ route('categories.store') }}" method="POST" class="category-form">
                                @csrf
                                <input type="text" name="name" placeholder="دسته‌ی جدید..." maxlength="40" required>
                                <input type="color" name="color" value="#8b5cf6" aria-label="رنگ دسته">
                                <button type="submit" class="cat-add">+ دسته</button>
                            </form>
                        </div>
                    </div>
                </div>
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

            <p class="tip">💡 دابل‌کلیک روی عنوان = ویرایش. کلیک روی نشان دسته یا اولویت = تغییر. کلیک روی تاریخ = تقویم. دستگیره‌ی ⋮⋮ = جابه‌جایی. نشان ☑ = زیرتسک‌ها.</p>

            <ul id="task-list" class="task-list">
                @forelse ($tasks as $task)
                    <li class="task {{ $task->is_done ? 'done' : '' }}"
                        data-id="{{ $task->id }}"
                        data-done="{{ $task->is_done ? 1 : 0 }}"
                        data-status="{{ $task->status }}"
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
        </div>

        {{-- چپ: آمار زنده --}}
        <aside class="dash-side side-stats" id="side-stats">
            <section class="panel">
                <h2>📊 آمار زنده</h2>

                <div class="donut">
                    <svg viewBox="0 0 100 100" aria-hidden="true">
                        <circle class="donut-track" cx="50" cy="50" r="42" />
                        <circle class="donut-fill" id="st-donut" cx="50" cy="50" r="42" style="--circ: 263.9; --dash: 0" />
                    </svg>
                    <div class="donut-center">
                        <strong id="st-percent">0٪</strong>
                        <span>پیشرفت</span>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card"><span class="stat-icon">📋</span><span class="stat-value" id="st-total">0</span><span class="stat-label">کل کارها</span></div>
                    <div class="stat-card"><span class="stat-icon">✅</span><span class="stat-value stat-good" id="st-done">0</span><span class="stat-label">انجام‌شده</span></div>
                    <div class="stat-card"><span class="stat-icon">⏳</span><span class="stat-value" id="st-left">0</span><span class="stat-label">باقی‌مانده</span></div>
                    <div class="stat-card"><span class="stat-icon">⚠️</span><span class="stat-value stat-bad" id="st-late">0</span><span class="stat-label">عقب‌افتاده</span></div>
                </div>
            </section>

            <section class="panel">
                <h2>🎯 به تفکیک اولویت</h2>
                <div class="hbars" id="st-pri"></div>
            </section>

            <section class="panel">
                <h2>🏷️ به تفکیک دسته</h2>
                <div class="hbars" id="st-cat"></div>
            </section>

            <a href="{{ route('stats') }}" class="back-link">📈 آمار کامل (رکورد و نمودار هفتگی)</a>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        window.APP_CATEGORIES = @json($categories);
        window.APP_SUBTASKS = @json($subtasksByTask);
    </script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    <script src="{{ asset('js/subtasks.js') }}?v={{ filemtime(public_path('js/subtasks.js')) }}"></script>
    <script src="{{ asset('js/panels.js') }}?v={{ filemtime(public_path('js/panels.js')) }}"></script>
    <script src="{{ asset('js/widgets.js') }}?v={{ filemtime(public_path('js/widgets.js')) }}"></script>
@endpush