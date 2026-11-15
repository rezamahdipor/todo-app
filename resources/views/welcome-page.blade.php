@extends('layouts.app')

@section('title', 'به لیست کارها خوش آمدی')

@section('content')
    <section class="hero">
        <div class="hero-icon">✅</div>
        <h1>لیست کارهای من</h1>
        <p class="hero-text">
            یه برنامه‌ی ساده و زیبا برای مدیریت کارهای روزانه.
            کارهات رو بنویس، ویرایش کن، انجام‌شده‌ها رو علامت بزن و همه‌چی رو منظم نگه دار.
        </p>

        <div class="hero-actions">
            @auth
                <a href="{{ route('tasks.index') }}" class="btn btn-primary">رفتن به لیست کارها</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">ورود</a>
                <a href="{{ route('register') }}" class="btn btn-outline">ثبت‌نام</a>
            @endauth
        </div>
    </section>

    <section class="section">
        <h2>این برنامه چی کار می‌کنه؟</h2>
        <div class="features">
            <div class="feature">
                <span class="feature-icon">➕</span>
                <h3>افزودن سریع</h3>
                <p>کارهات رو در چند ثانیه اضافه کن، بدون رفرش صفحه.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">✏️</span>
                <h3>ویرایش آسان</h3>
                <p>با دابل‌کلیک روی عنوان، کارت رو ویرایش کن.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🔒</span>
                <h3>لیست شخصی</h3>
                <p>هر کاربر فقط کارهای خودش رو می‌بینه.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🌙</span>
                <h3>تم روشن و تاریک</h3>
                <p>ظاهر برنامه خودکار با تنظیم دستگاهت هماهنگ می‌شه.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <h2>درباره‌ی پروژه</h2>
        <p class="about-text">
            این پروژه برای تمرین و مرور برنامه‌نویسی وب ساخته شده و با
            <strong>لاراول</strong> (بکند)، <strong>MySQL</strong> (دیتابیس)،
            <strong>Blade</strong>، <strong>CSS</strong> و <strong>جاوااسکریپت</strong> (فرانت) نوشته شده.
        </p>

        <div class="tech">
            <span>Laravel</span>
            <span>PHP</span>
            <span>MySQL</span>
            <span>JavaScript</span>
            <span>CSS</span>
        </div>
    </section>

    <section class="section creator">
        <h2>سازنده</h2>
        <div class="creator-card">
            <div class="avatar">ر</div>
            <div>
                <h3>رضا مهدی‌پور</h3>
                <p>علاقه‌مند به برنامه‌نویسی وب</p>
                <a href="https://github.com/rezamahdipor" target="_blank" rel="noopener">
                    گیت‌هاب من ↗
                </a>
            </div>
        </div>
    </section>
@endsection