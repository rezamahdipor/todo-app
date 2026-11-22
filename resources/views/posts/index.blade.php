@extends('layouts.app')

@section('title', 'نوشته‌های من')
@section('container-class', 'container-blog')

@section('content')
    <div class="topbar">
        <span>✍️ نوشته‌های من</span>
        <div class="topbar-actions">
            <a href="{{ route('dashboard') }}" class="topbar-link">🏠 داشبورد</a>
            <a href="{{ route('blog.index') }}" class="topbar-link">📰 وبلاگ</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">خروج</button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="flash" role="status">{{ session('status') }}</div>
    @endif

    <p><a href="{{ route('posts.create') }}" class="btn btn-primary">➕ نوشته‌ی جدید</a></p>

    <div class="my-posts">
        @forelse ($posts as $p)
            <div class="my-post">
                <strong>{{ $p->title }}</strong>
                <span class="badge {{ $p->isPublished() ? 'live' : '' }}">{{ $p->isPublished() ? 'منتشرشده' : 'پیش‌نویس' }}</span>

                @if ($p->isPublished())
                    <a href="{{ route('blog.show', [$p, $p->slug]) }}" class="topbar-link">مشاهده</a>
                @endif
                <a href="{{ route('posts.edit', $p) }}" class="topbar-link">ویرایش</a>

                <form action="{{ route('posts.destroy', $p) }}" method="POST" onsubmit="return confirm('این نوشته حذف بشه؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">حذف</button>
                </form>
            </div>
        @empty
            <p class="empty">هنوز نوشته‌ای نداری. اولین نوشته‌ات رو بساز ✍️</p>
        @endforelse
    </div>
@endsection