@extends('layouts.app')

@section('title', 'داشبورد')
@section('container-class', 'container-home')

@section('content')
    <div class="topbar">
        <span>🏠 داشبورد</span>
        <div class="topbar-actions">
            <a href="{{ route('home') }}" class="topbar-link">🌐 صفحه‌ی اصلی</a>
            <a href="{{ route('tasks.index') }}" class="topbar-link">📋 کارها</a>
            <a href="{{ route('habits') }}" class="topbar-link">🔥 عادت‌ها</a>
            <a href="{{ route('posts.index') }}" class="topbar-link">✍️ نوشته‌ها</a>
            <a href="{{ route('blog.index') }}" class="topbar-link">📰 وبلاگ</a>
            <a href="{{ route('stats') }}" class="topbar-link">📊 آمار</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">خروج</button>
            </form>
        </div>
    </div>

    <div class="hero">
        @include('partials.avatar', ['user' => auth()->user(), 'class' => 'home-avatar'])
        <h1>سلام {{ auth()->user()->name }} 👋</h1>
        <p class="hero-text">به داشبورد خوش اومدی. از اینجا به بخش‌های مختلف سر بزن.</p>
    </div>

    <div class="home-grid single">
        <a href="{{ route('account') }}" class="home-tile">
            <span class="home-ico">👤</span>
            <h2>حساب کاربری</h2>
            <p>اطلاعات، آمار و رمز عبور</p>
        </a>
    </div>

    <p class="tip">🚧 بخش‌های بیشتر به‌زودی اضافه می‌شن.</p>
@endsection