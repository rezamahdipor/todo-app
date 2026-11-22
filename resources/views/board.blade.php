@extends('layouts.app')

@section('title', 'نمای کانبان')
@section('container-class', 'container-board')

@section('content')
    @include('partials.side-menu')
    @php
        $statuses = [
            'todo'  => '📝 باقی‌مانده',
            'doing' => '🔄 در حال انجام',
            'done'  => '✅ انجام‌شده',
        ];
        $priorityLabels = ['low' => 'کم', 'medium' => 'متوسط', 'high' => 'زیاد'];
    @endphp

    <div class="topbar">
        <span>سلام {{ auth()->user()->name }} 👋</span>
        <div class="topbar-actions">
            <a href="{{ route('tasks.index') }}" class="topbar-link">📋 لیست</a>
            <a href="{{ route('stats') }}" class="topbar-link">📊 آمار</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">خروج</button>
            </form>
        </div>
    </div>

    <h1>نمای کانبان</h1>
    <p class="subtitle">کارت‌ها رو با دستگیره‌ی ⋮⋮ بین ستون‌ها بکش. برای افزودن کار جدید به صفحه‌ی لیست برو.</p>

    <div class="kb-board">
        @foreach ($statuses as $key => $label)
            @php $items = $columns->get($key, collect()); @endphp

            <section class="kb-col" data-status="{{ $key }}">
                <h2 class="kb-head">
                    <span>{{ $label }}</span>
                    <span class="kb-count">{{ $items->count() }}</span>
                </h2>

                <ul class="kb-list" data-status="{{ $key }}">
                    @foreach ($items as $task)
                        <li class="kb-card" data-id="{{ $task->id }}">
                            <button type="button" class="drag-handle" aria-label="جابه‌جایی" title="بکش تا جابه‌جا بشه">⋮⋮</button>

                            <div class="kb-body">
                                <span class="kb-title">{{ $task->title }}</span>

                                <div class="task-meta">
                                    @if ($task->category)
                                        <span class="cat-badge" style="--cat: {{ $task->category->color }}">{{ $task->category->name }}</span>
                                    @endif
                                    @if ($task->due_date)
                                        <span class="due-badge">📅 {{ $task->due_date->format('Y-m-d') }}</span>
                                    @endif
                                    <span class="priority priority-{{ $task->priority }}">{{ $priorityLabels[$task->priority] ?? 'متوسط' }}</span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/board.js') }}?v={{ filemtime(public_path('js/board.js')) }}"></script>
@endpush