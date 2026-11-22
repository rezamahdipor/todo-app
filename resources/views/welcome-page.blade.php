@extends('layouts.app')

@section('title', 'لیست کارها، عادت‌ها و وبلاگ')
@section('container-class', 'container-wide')

@section('content')
    @include('partials.side-menu')

    @php
        $latest = \App\Models\Post::published()->with('user:id,name,avatar')->latest('published_at')->take(3)->get();
        $go = fn ($name) => auth()->check() ? route($name) : route('login');
    @endphp

    @include('partials.site-header')

    {{-- هیرو --}}
    <section class="lp-hero">
        <div>
            <span class="lp-badge">✨ رایگان و فارسی</span>
            <h1>برنامه‌ریزی، عادت‌ها و نوشتن؛ همه یک‌جا</h1>
            <p>
                کارهات رو مرتب کن، عادت‌های خوب بساز، پیشرفتت رو روی تقویم شمسی ببین
                و تجربه‌هات رو توی وبلاگ با بقیه به اشتراک بذار.
            </p>

            <div class="lp-btns">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">🏠 رفتن به داشبورد</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary">🚀 شروع رایگان</a>
                    <a href="{{ route('login') }}" class="btn btn-outline">ورود</a>
                @endauth
                <a href="{{ route('blog.index') }}" class="btn btn-outline">📰 وبلاگ</a>
            </div>

            <div class="lp-chips">
                <span>🎨 ۷ تم رنگی</span>
                <span>📅 تقویم شمسی</span>
                <span>📱 سازگار با موبایل</span>
                <span>🔒 لیست شخصی</span>
                <span>💬 چت عمومی</span>
            </div>
        </div>

        <div class="lp-mock" aria-hidden="true">
            <div class="lp-m-bar"><i></i><i></i><i></i></div>

            <div class="lp-m-top">
                <svg class="lp-m-ring" viewBox="0 0 64 64">
                    <circle cx="32" cy="32" r="26" fill="none" stroke="var(--border)" stroke-width="7" />
                    <circle cx="32" cy="32" r="26" fill="none" stroke="var(--primary)" stroke-width="7" stroke-linecap="round"
                            stroke-dasharray="117.6 163.4" transform="rotate(-90 32 32)" />
                    <text x="32" y="37" text-anchor="middle" font-size="14" font-weight="800" fill="currentColor">۷۲٪</text>
                </svg>
                <div>
                    <strong>امروز</strong>
                    <div class="lp-m-streak">🔥 ۷ روز پشت‌سرهم</div>
                </div>
            </div>

            <div class="lp-m-task d"><b></b><span>خرید نان</span></div>
            <div class="lp-m-task"><b></b><span>مرور درس لاراول</span></div>
            <div class="lp-m-task d"><b></b><span>ورزش صبحگاهی</span></div>
            <div class="lp-m-task"><b></b><span>نوشتن پست وبلاگ</span></div>
        </div>
    </section>

    @include('partials.live-stats')

    {{-- بخش‌های سایت --}}
    <section class="lp-sec lp-rv" id="features">
        <h2>همه‌چیز برای نظم دادن به روزهات</h2>
        <p class="lp-sub">ابزارهایی که کنار هم کار می‌کنن و با هر تمی که دوست داری جور می‌شن.</p>

        <div class="lp-grid">
            <a href="{{ $go('tasks.index') }}" class="lp-card"><span class="lp-ico">📋</span><h3>لیست کارها</h3><p>کارها رو با اولویت، دسته‌ی رنگی و تاریخ سررسید مرتب کن.</p></a>
            <a href="{{ $go('board') }}" class="lp-card"><span class="lp-ico">🗂️</span><h3>کانبان</h3><p>کارت‌ها رو بین «باقی‌مانده»، «در حال انجام» و «انجام‌شده» بکش.</p></a>
            <a href="{{ $go('habits') }}" class="lp-card"><span class="lp-ico">🔥</span><h3>عادت‌ها</h3><p>رکورد پشت‌سرهم و شبکه‌ی ماهانه برای هر عادت.</p></a>
            <a href="{{ $go('tasks.index') }}" class="lp-card"><span class="lp-ico">📅</span><h3>تقویم شمسی</h3><p>سررسیدها روی ماه شمسی؛ با کشیدن، تاریخ رو عوض کن.</p></a>
            <a href="{{ $go('tasks.index') }}" class="lp-card"><span class="lp-ico">🍅</span><h3>پومودورو</h3><p>تایمر تمرکز ۲۵ دقیقه‌ای کنار لیست کارهات.</p></a>
            <a href="{{ route('blog.index') }}" class="lp-card"><span class="lp-ico">✍️</span><h3>وبلاگ</h3><p>بنویس، منتشر کن و نوشته‌ات رو به همه نشون بده.</p></a>
        </div>
    </section>

    {{-- مراحل --}}
    <section class="lp-sec lp-rv">
        <h2>در سه قدم شروع کن</h2>
        <p class="lp-sub">بدون پیچیدگی؛ ثبت‌نام و استفاده.</p>

        <div class="lp-steps">
            <div class="lp-step"><h3>حساب بساز</h3><p>ثبت‌نام رایگانه و چند ثانیه بیشتر وقت نمی‌گیره.</p></div>
            <div class="lp-step"><h3>برنامه‌ات رو بچین</h3><p>کار، عادت و دسته بساز و تاریخ و اولویت بده.</p></div>
            <div class="lp-step"><h3>پیگیری کن و بنویس</h3><p>پیشرفتت رو توی آمار ببین و تجربه‌هات رو توی وبلاگ منتشر کن.</p></div>
        </div>
    </section>

    {{-- آخرین نوشته‌ها --}}
    <section class="lp-sec lp-rv">
        <h2>📰 تازه‌ترین نوشته‌ها</h2>
        <p class="lp-sub">آخرین مطالبی که کاربرها منتشر کردن.</p>

        @if ($latest->isEmpty())
            <p class="empty">هنوز نوشته‌ای منتشر نشده. اولین نفر باش ✍️</p>
        @else
            <div class="lp-posts">
                @foreach ($latest as $p)
                    <a href="{{ route('blog.show', [$p, $p->slug]) }}" class="post-card">
                        <h3>{{ $p->title }}</h3>
                        <p>{{ $p->summary(110) }}</p>
                        <div class="by">
                            @include('partials.avatar', ['user' => $p->user])
                            <span>{{ $p->user->name }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <p class="lp-more"><a href="{{ route('blog.index') }}" class="btn btn-outline">دیدن همه‌ی نوشته‌ها ←</a></p>
    </section>

    {{-- چت عمومی --}}
    <section class="lp-sec lp-rv" id="chat">
        <div class="lp-chat">
            <div>
                <span class="lp-badge">💬 تازه</span>
                <h2>چت عمومی؛ با بقیه حرف بزن</h2>
                <p>
                    یه دکمه‌ی چت گوشه‌ی بالای هر صفحه هست. با یه کلیک، پنل چت باز می‌شه و می‌تونی
                    توی سه اتاق عمومی با بقیه‌ی کاربرها پیام بدی و بگیری؛ بدون اینکه صفحه‌ای که توشی عوض بشه.
                </p>
                <ul class="lp-list">
                    <li>🏠 سه اتاق عمومی: «عمومی ۱»، «عمومی ۲» و «عمومی ۳»</li>
                    <li>⚡ پیام‌ها تقریباً لحظه‌ای و خودکار بروز می‌شن</li>
                    <li>🔴 نقطه‌ی قرمز نشونت می‌ده کجا پیام تازه اومده</li>
                    <li>🖼️ اسم و عکس پروفایل کنار هر پیام می‌یاد</li>
                    <li>🔒 فقط برای کاربرهای واردشده</li>
                </ul>

                @auth
                    <a href="{{ route('chat.page') }}" class="btn btn-primary">💬 ورود به چت</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary">🚀 ثبت‌نام و شروع چت</a>
                @endauth
            </div>

            <div class="lp-chatmock" aria-hidden="true">
                <div class="lp-cm-rooms"><span class="on">عمومی ۱</span><span>عمومی ۲</span><span>عمومی ۳</span></div>
                <div class="lp-cm-msg"><i>ن</i><div><b>نگار</b><p>سلام! کسی هست درباره‌ی عادت‌ها حرف بزنه؟ 🔥</p></div></div>
                <div class="lp-cm-msg me"><i>ر</i><div><b>رضا</b><p>آره، من امروز ۷ روز پشت‌سرهم شدم 😄</p></div></div>
                <div class="lp-cm-msg"><i>م</i><div><b>مهدی</b><p>عالیه! منم از کانبان استفاده می‌کنم 🗂️</p></div></div>
                <div class="lp-cm-type"><span></span><span></span><span></span></div>
            </div>
        </div>
    </section>

    {{-- چرخ تم --}}
    <section class="section">
        <div class="theme-row">
            <h2 class="theme-title">تم دلخواهت رو انتخاب کن </h2>

            <div class="theme-wheel-wrap">
                <svg class="theme-wheel" viewBox="0 0 200 200" role="group" aria-label="انتخاب تم">
                    <defs>
                        <radialGradient id="wheel-gloss" cx="35%" cy="28%" r="78%">
                            <stop offset="0" stop-color="#fff" stop-opacity="0.6" />
                            <stop offset="0.45" stop-color="#fff" stop-opacity="0.05" />
                            <stop offset="1" stop-color="#000" stop-opacity="0.25" />
                        </radialGradient>
                    </defs>

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

                    <circle class="wheel-gloss" cx="100" cy="100" r="90" fill="url(#wheel-gloss)" />

                    <circle class="sparkle s1" cx="52" cy="38" r="2.6" />
                    <circle class="sparkle s2" cx="160" cy="150" r="2" />
                    <circle class="sparkle s3" cx="150" cy="30" r="1.6" />
                </svg>
            </div>
        </div>
    </section>

    {{-- سؤالات متداول --}}
    <section class="lp-sec lp-rv" id="faq">
        <h2>سؤالات متداول</h2>
        <p class="lp-sub">جواب چند سؤال رایج.</p>

        <div class="lp-faq">
            <details><summary>استفاده از سایت رایگانه؟</summary><p>بله، ثبت‌نام و استفاده از همه‌ی بخش‌ها فعلاً رایگانه.</p></details>
            <details><summary>کارها و عادت‌هام برای بقیه دیده می‌شه؟</summary><p>نه. کارها، عادت‌ها و دسته‌ها کاملاً شخصی‌ان. فقط نوشته‌هایی که توی وبلاگ «منتشر» کنی عمومی هستن.</p></details>
            <details><summary>روی موبایل هم کار می‌کنه؟</summary><p>بله، سایت برای صفحه‌ی کوچیک هم طراحی شده و با هر ۷ تم کار می‌کنه.</p></details>
            <details><summary>چطور با ادمین در ارتباط باشم؟</summary><p>از بخش «ارتباط با ادمین» توی پایین همین صفحه یا از صفحه‌ی <a href="{{ Route::has('contact') ? route('contact') : '#' }}">تماس با ما</a>.</p></details>
        </div>
    </section>

    {{-- بنر دعوت --}}
    <section class="lp-cta lp-rv">
        <h2>{{ auth()->check() ? 'آماده‌ای ادامه بدی؟' : 'همین امروز شروع کن' }}</h2>
        <p>{{ auth()->check() ? 'کارها و عادت‌هات منتظرتن.' : 'یه حساب رایگان بساز و برنامه‌ات رو بچین.' }}</p>
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary">🏠 داشبورد</a>
        @else
            <a href="{{ route('register') }}" class="btn btn-primary">🚀 ساخت حساب رایگان</a>
        @endauth
    </section>

    @include('partials.site-footer')
@endsection

@push('head')
    <meta name="description" content="مدیریت کارها، عادت‌ها و تقویم شمسی و یه وبلاگ برای نوشتن؛ رایگان و فارسی با ۷ تم رنگی.">
@endpush

@push('scripts')
    <script src="{{ asset('js/wheel-hover.js') }}?v={{ filemtime(public_path('js/wheel-hover.js')) }}"></script>
    <script>
        (function () {
            const els = document.querySelectorAll('.lp-rv');
            if (!('IntersectionObserver' in window) || !els.length) return;

            document.documentElement.classList.add('lp-js');

            const io = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
                });
            }, { threshold: 0.12 });

            els.forEach((el) => io.observe(el));
        })();
    </script>
@endpush