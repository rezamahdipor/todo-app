@php $r = fn ($n) => Route::has($n) ? route($n) : '#'; @endphp
<header class="site-head">
    <a href="{{ route('home') }}" class="site-logo"><span aria-hidden="true">✅</span> لیست کارهای من</a>

    <nav class="site-nav" aria-label="ناوبری اصلی">
        <a href="{{ route('home') }}">خانه</a>
        <a href="{{ route('blog.index') }}">وبلاگ</a>
        <a href="{{ route('home') }}#features">امکانات</a>
        <a href="{{ route('home') }}#faq">سؤالات</a>
        <a href="{{ $r('contact') }}">تماس</a>
    </nav>

    <div class="site-cta">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-sm">🏠 داشبورد</a>
        @else
            <a href="{{ route('login') }}" class="btn-sm btn-ghost">ورود</a>
            <a href="{{ route('register') }}" class="btn-sm">ثبت‌نام</a>
        @endauth
    </div>
</header>