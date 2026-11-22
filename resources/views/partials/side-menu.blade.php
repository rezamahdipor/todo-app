{{-- منوی سه‌خط سمت راست (همه‌ی صفحه‌ها) --}}
<button type="button" id="menu-btn" class="menu-btn" aria-expanded="false" aria-controls="menu-drawer" aria-label="باز کردن منو">
    <span></span><span></span><span></span>
</button>

<div id="menu-overlay" class="menu-overlay"></div>

<aside id="menu-drawer" class="menu-drawer" aria-label="منو" tabindex="-1">
    @auth
        <div class="menu-user">
            @include('partials.avatar', ['user' => auth()->user()])
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->email }}</span>
            </div>
        </div>
    @endauth

    <nav class="menu-links" aria-label="صفحه‌ها">
        <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>🌐 صفحه‌ی اصلی</a>
        <a href="{{ route('blog.index') }}" @class(['active' => request()->routeIs('blog.*')])>📰 وبلاگ</a>

        @auth
            <h2>ابزارها</h2>
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>🏠 داشبورد</a>
            <a href="{{ route('tasks.index') }}" @class(['active' => request()->routeIs('tasks.*')])>📋 لیست کارها</a>
            <a href="{{ route('board') }}" @class(['active' => request()->routeIs('board')])>🗂️ کانبان</a>
            <a href="{{ route('habits') }}" @class(['active' => request()->routeIs('habits')])>🔥 عادت‌ها</a>
            <a href="{{ route('stats') }}" @class(['active' => request()->routeIs('stats')])>📊 آمار</a>
            <a href="{{ route('posts.index') }}" @class(['active' => request()->routeIs('posts.*')])>✍️ نوشته‌های من</a>
            <a href="{{ route('account') }}" @class(['active' => request()->routeIs('account')])>👤 حساب کاربری</a>
        @else
            <a href="{{ route('login') }}" @class(['active' => request()->routeIs('login')])>🔑 ورود</a>
            <a href="{{ route('register') }}" @class(['active' => request()->routeIs('register')])>📝 ثبت‌نام</a>
        @endauth
    </nav>

    @auth
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-sm btn-danger btn-full">🚪 خروج</button>
        </form>
    @endauth

    <h2>🛠️ سازنده</h2>
    <div class="creator-card">
        <div class="avatar">ر</div>
        <div>
            <h3>رضا</h3>
            <p>سازنده و توسعه‌دهنده‌ی این پروژه</p>
            <a href="https://github.com/rezamahdipor" target="_blank" rel="noopener">github.com/rezamahdipor</a>
        </div>
    </div>

    <h2>📝 درباره‌ی پروژه</h2>
    <p class="about-text">مدیریت کارها، عادت‌ها و کانبان با ظاهر شیشه‌ای و ۷ تم رنگی. ساخته‌شده قدم‌به‌قدم با عشق و هوش مصنوعی.</p>
    <div class="tech">
        <span>Laravel ۱۳</span>
        <span>PHP ۸.۳</span>
        <span>MySQL</span>
        <span>JavaScript</span>
        <span>CSS</span>
    </div>
</aside>

<script>
    (function () {
        const btn = document.getElementById('menu-btn');
        const overlay = document.getElementById('menu-overlay');
        const drawer = document.getElementById('menu-drawer');
        if (!btn || !overlay || !drawer) return;

        // کارت اصلی افکت شیشه‌ای داره و المان‌های ثابت داخلش جابه‌جا می‌شن؛ پس می‌ریم بیرونش
        document.body.append(btn, overlay, drawer);
        document.body.classList.add('has-menu');

        function setOpen(open) {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'بستن منو' : 'باز کردن منو');
            overlay.classList.toggle('open', open);
            drawer.classList.toggle('open', open);
            document.body.classList.toggle('menu-open', open);
            if (open) drawer.focus();
        }

        btn.addEventListener('click', () => setOpen(!drawer.classList.contains('open')));
        overlay.addEventListener('click', () => setOpen(false));
        drawer.addEventListener('click', (e) => { if (e.target.closest('.menu-links a')) setOpen(false); });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && drawer.classList.contains('open')) {
                setOpen(false);
                btn.focus();
            }
        });
    })();
</script>