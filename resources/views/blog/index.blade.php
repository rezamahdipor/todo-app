@extends('layouts.app')

@section('title', $author ? 'نوشته‌های ' . $author->name : 'وبلاگ')
@section('container-class', 'container-blog')

@section('content')
    @include('partials.site-nav')

    <h1>{{ $author ? 'نوشته‌های ' . $author->name : '📰 وبلاگ' }}</h1>

    @if ($author)
        <div class="by by-lg">
            @include('partials.avatar', ['user' => $author])
            <span>{{ $author->name }}</span>
        </div>
    @else
        <p class="subtitle">نوشته‌های کاربران</p>
    @endif

    @if ($posts->isEmpty())
        <p class="empty">هنوز نوشته‌ای منتشر نشده.</p>
    @else
        <div class="blog-grid">
            @foreach ($posts as $p)
                <article>
                    <a href="{{ route('blog.show', [$p, $p->slug]) }}" class="post-card">
                        <h2>{{ $p->title }}</h2>
                        <p>{{ $p->summary(140) }}</p>
                        <div class="by">
                            @include('partials.avatar', ['user' => $p->user])
                            <span>{{ $p->user->name }}</span> ·
                            <time datetime="{{ $p->published_at->toDateString() }}" data-fa>{{ $p->published_at->format('Y-m-d') }}</time>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

        <div class="pager">
            @if ($posts->previousPageUrl())
                <a href="{{ $posts->previousPageUrl() }}" class="topbar-link">→ جدیدتر</a>
            @else
                <span></span>
            @endif
            @if ($posts->nextPageUrl())
                <a href="{{ $posts->nextPageUrl() }}" class="topbar-link">قدیمی‌تر ←</a>
            @endif
        </div>
    @endif
@endsection

@push('head')
    <meta name="description" content="{{ $author ? 'نوشته‌های ' . $author->name : 'وبلاگ: نوشته‌های کاربران لیست کارهای من' }}">
    <link rel="canonical" href="{{ $posts->currentPage() > 1 ? $posts->url($posts->currentPage()) : url()->current() }}">
@endpush