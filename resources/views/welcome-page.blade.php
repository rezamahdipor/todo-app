@extends('layouts.app')

@section('title', 'به لیست کارها خوش آمدی')
@section('container-class', 'container-wide')

@section('content')
    @include('partials.side-menu')

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
        <div class="theme-row">
            <h2 class="theme-title">تم دلخواهت رو انتخاب کن </h2>

            <div class="theme-wheel-wrap">
                <svg class="theme-wheel" viewBox="0 0 200 200" role="group" aria-label="انتخاب تم">
                    <defs>
                        {{-- برق و عمق: روشن بالا-چپ، تیره‌تر لبه‌ها --}}
                        <radialGradient id="wheel-gloss" cx="35%" cy="28%" r="78%">
                            <stop offset="0" stop-color="#fff" stop-opacity="0.6" />
                            <stop offset="0.45" stop-color="#fff" stop-opacity="0.05" />
                            <stop offset="1" stop-color="#000" stop-opacity="0.25" />
                        </radialGradient>
                    </defs>

                    {{-- حلقه‌ی بیرونی --}}
                    <circle class="wheel-rim" cx="100" cy="100" r="95" />

                    <path style="--i:0" data-theme-choice="light" fill="#a5b4fc" role="button" tabindex="0" aria-label="تم روشن"
                          d="M100 100 L100 10 A90 90 0 0 1 170.36 43.89 Z" />
                    <path style="--i:1" data-theme-choice="dark" fill="#312e81" role="button" tabindex="0" aria-label="تم تاریک"
                          d="M100 100 L170.36 43.89 A90 90 0 0 1 187.74 120.03 Z" />
                    <path style="--i:2" data-theme-choice="pink" fill="#ec4899" role="button" tabindex="0" aria-label="تم صورتی"
                          d="M100 100 L187.74 120.03 A90 90 0 0 1 139.05 181.09 Z" />
                    <path style="--i:3" data-theme-choice="mint" fill="#14b8a6" role="button" tabindex="0" aria-label="تم نعنایی"
                          d="M100 100 L139.05 181.09 A90 90 0 0 1 60.95 181.09 Z" />
                    <path style="--i:4" data-theme-choice="ocean" fill="#0284c7" role="button" tabindex="0" aria-label="تم اقیانوسی"
                          d="M100 100 L60.95 181.09 A90 90 0 0 1 12.26 120.03 Z" />
                    <path style="--i:5" data-theme-choice="lavender" fill="#8b5cf6" role="button" tabindex="0" aria-label="تم لاوندر"
                          d="M100 100 L12.26 120.03 A90 90 0 0 1 29.64 43.89 Z" />
                    <path style="--i:6" data-theme-choice="sunset" fill="#f97316" role="button" tabindex="0" aria-label="تم غروب"
                          d="M100 100 L29.64 43.89 A90 90 0 0 1 100 10 Z" />

                    {{-- لایه‌ی براق روی همه (کلیک رو نمی‌گیره) --}}
                    <circle class="wheel-gloss" cx="100" cy="100" r="90" fill="url(#wheel-gloss)" />

                    {{-- جرقه‌ها ✨ --}}
                    <circle class="sparkle s1" cx="52" cy="38" r="2.6" />
                    <circle class="sparkle s2" cx="160" cy="150" r="2" />
                    <circle class="sparkle s3" cx="150" cy="30" r="1.6" />
                </svg>
            </div>
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

    @include('partials.live-stats')
@endsection

@push('scripts')
    <script src="{{ asset('js/wheel-hover.js') }}?v={{ filemtime(public_path('js/wheel-hover.js')) }}"></script>
@endpush