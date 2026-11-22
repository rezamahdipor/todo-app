<nav class="topbar">
    <a href="{{ route('home') }}" class="topbar-link">🏠 خانه</a>
    <div class="topbar-actions">
        <a href="{{ route('blog.index') }}" class="topbar-link">📰 وبلاگ</a>
        @auth
            <a href="{{ route('posts.index') }}" class="topbar-link">✍️ نوشته‌های من</a>
            <a href="{{ route('dashboard') }}" class="topbar-link">📊 داشبورد</a>
        @else
            <a href="{{ route('login') }}" class="topbar-link">ورود</a>
            <a href="{{ route('register') }}" class="topbar-link">ثبت‌نام</a>
        @endauth
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('time[data-fa]').forEach((t) => {
            const d = new Date(t.getAttribute('datetime') + 'T00:00:00');
            if (!isNaN(d)) {
                t.textContent = d.toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric', month: 'long', day: 'numeric' });
            }
        });
    });
</script>