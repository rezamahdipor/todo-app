@extends('layouts.app')

@section('title', 'لیست کارها')

@section('content')
    <h1>لیست کارها</h1>

    {{-- فرم افزودن --}}
    <form action="{{ route('tasks.store') }}" method="POST" class="add-form">
        @csrf
        <input type="text" name="title" placeholder="کار جدید..." value="{{ old('title') }}" required>
        <button type="submit">افزودن</button>
    </form>

    {{-- نمایش خطا --}}
    @error('title')
        <p class="error">{{ $message }}</p>
    @enderror

    {{-- لیست --}}
    <ul class="task-list">
        @forelse ($tasks as $task)
            <li class="task {{ $task->is_done ? 'done' : '' }}">
                <span class="title">{{ $task->title }}</span>

                <div class="actions">
                    {{-- انجام شد / برگردان --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="is_done" value="{{ $task->is_done ? 0 : 1 }}">
                        <button type="submit" class="btn-done">
                            {{ $task->is_done ? 'برگردان' : 'انجام شد' }}
                        </button>
                    </form>

                    {{-- حذف --}}
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">حذف</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="empty">هنوز کاری اضافه نکردی.</li>
        @endforelse
    </ul>
@endsection