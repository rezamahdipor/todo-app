@php $r = fn ($n) => Route::has($n) ? route($n) : '#'; @endphp
<footer class="site-foot">
    <div class="foot-support">
        <div>
            <h2>سؤال یا مشکلی داری؟ 💬</h2>
            <p>ادمین سایت همیشه در دسترسه. پیشنهاد، گزارش باگ یا محتوای نامناسب رو برامون بفرست.</p>
        </div>
        <div class="foot-btns">
            <a href="mailto:contact@YOURDOMAIN.com" class="btn-sm">✉️ ایمیل به ادمین</a>
            <a href="https://t.me/YOUR_TELEGRAM_ID" target="_blank" rel="noopener" class="btn-sm btn-ghost">💬 تلگرام</a>
            <a href="https://github.com/rezamahdipor" target="_blank" rel="noopener" class="btn-sm btn-ghost">🐙 گیت‌هاب</a>
        </div>
    </div>

    <div class="foot-cols">
        <div>
            <strong class="foot-brand">✅ لیست کارهای من</strong>
            <p>کارها، عادت‌ها و نوشته‌هات؛ همه یک‌جا و فارسی، با ظاهر شیشه‌ای و ۷ تم رنگی.</p>
        </div>

        <nav aria-label="دسترسی سریع">
            <h3>دسترسی سریع</h3>
            <a href="{{ route('home') }}">صفحه‌ی اصلی</a>
            <a href="{{ route('blog.index') }}">وبلاگ</a>
            @auth
                <a href="{{ route('tasks.index') }}">لیست کارها</a>
                <a href="{{ route('habits') }}">عادت‌ها</a>
            @else
                <a href="{{ route('login') }}">ورود</a>
                <a href="{{ route('register') }}">ثبت‌نام</a>
            @endauth
        </nav>

        <nav aria-label="اطلاعات سایت">
            <h3>اطلاعات</h3>
            <a href="{{ $r('privacy') }}">حریم خصوصی</a>
            <a href="{{ $r('terms') }}">قوانین استفاده</a>
            <a href="{{ $r('contact') }}">تماس با ما</a>
            <a href="{{ route('home') }}#faq">سؤالات متداول</a>
        </nav>

        <nav aria-label="ارتباط با ادمین">
            <h3>ارتباط با ادمین</h3>
            <a href="mailto:contact@YOURDOMAIN.com">✉️ contact@YOURDOMAIN.com</a>
            <a href="https://t.me/YOUR_TELEGRAM_ID" target="_blank" rel="noopener">💬 تلگرام</a>
            <a href="{{ $r('contact') }}">🚩 گزارش محتوای نامناسب</a>
            <a href="{{ $r('contact') }}">🐞 گزارش باگ یا پیشنهاد</a>
        </nav>
    </div>

    <div class="foot-bottom">
        <span>© <span data-year></span> · ساخته‌شده با ❤️ توسط رضا</span>
        <a href="{{ $r('contact') }}">ارتباط با ادمین</a>
    </div>
</footer>

<script>
    document.querySelectorAll('[data-year]').forEach((el) => {
        el.textContent = new Date().toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric' });
    });
</script>