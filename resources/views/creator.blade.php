@extends('layouts.app')

@section('title', 'سازنده')
@section('container-class', 'container-account')

@section('content')
    @include('partials.side-menu')
    <div class="topbar">
        <span>🛠️ سازنده</span>
        <div class="topbar-actions">
            <a href="{{ route('dashboard') }}" class="topbar-link">🏠 داشبورد</a>
        </div>
    </div>

    <h1>درباره‌ی سازنده</h1>

    <div class="creator-card">
        <div class="avatar">ر</div>
        <div>
            <h3>رضا</h3>
            <p>سازنده و توسعه‌دهنده‌ی این پروژه</p>
            <a href="https://github.com/rezamahdipor" target="_blank" rel="noopener">github.com/rezamahdipor</a>
        </div>
    </div>

    <section class="panel" style="margin-top: 14px">
        <h2>📝 درباره‌ی پروژه</h2>
        <p class="about-text">مدیریت کارها، عادت‌ها و کانبان با ظاهر شیشه‌ای و ۷ تم رنگی. ساخته‌شده قدم‌به‌قدم با عشق و هوش مصنوعی.</p>
        <div class="tech">
            <span>Laravel ۱۳</span>
            <span>PHP ۸.۳</span>
            <span>MySQL</span>
            <span>JavaScript</span>
            <span>CSS</span>
        </div>
    </section>

    <p class="tip">🚧 اطلاعات بیشتر به‌زودی اضافه می‌شه.</p>
@endsection