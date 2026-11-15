@extends('layouts.app')

@section('title', 'لیست کارها')

@section('content')
<div class="topbar">
    <span>سلام {{ auth()->user()->name }} 👋</span>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn-logout">خروج</button>
    </form>
</div>
    <h1>لیست کارها</h1>

    <form id="add-form" action="{{ route('tasks.store') }}" method="POST" class="add-form">
        @csrf
        <input type="text" name="title" placeholder="کار جدید..." value="{{ old('title') }}" required>
        <button type="submit">افزودن</button>
    </form>

    <p id="error" class="error">
        @error('title') {{ $message }} @enderror
    </p>

  <ul id="task-list" class="task-list">
    @forelse ($tasks as $task)
        <li class="task {{ $task->is_done ? 'done' : '' }}"
            data-id="{{ $task->id }}"
            data-done="{{ $task->is_done ? 1 : 0 }}">
            <span class="title">{{ $task->title }}</span>

            <div class="actions">
                <button type="button" class="btn-done">
                    {{ $task->is_done ? 'برگردان' : 'انجام شد' }}
                </button>
                <button type="button" class="btn-delete">حذف</button>
            </div>
        </li>
    @empty
        <li class="empty">هنوز کاری اضافه نکردی.</li>
    @endforelse
</ul>
@endsection