@extends('layouts.app')

@section('title', 'حساب کاربری')
@section('container-class', 'container-account')

@section('content')
    @include('partials.side-menu')
    <div class="topbar">
        <span>👤 حساب کاربری</span>
        <div class="topbar-actions">
            <a href="{{ route('dashboard') }}" class="topbar-link">🏠 داشبورد</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">خروج</button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="flash" role="status">{{ session('status') }}</div>
    @endif

    <section class="acc-hero">
        @include('partials.avatar', ['user' => $user])

        <div class="acc-hero-info">
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <p>عضو از <span data-date="{{ $user->created_at?->format('Y-m-d') }}"></span></p>

            <div class="acc-actions">
                <form method="POST" action="{{ route('account.avatar') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="file" id="avatar-file" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>
                    <label for="avatar-file" class="btn-sm btn-ghost">📷 انتخاب عکس</label>
                    <button type="submit" class="btn-sm">⬆ ذخیره عکس</button>
                </form>

                @if ($user->avatar)
                    <form method="POST" action="{{ route('account.avatar.remove') }}" onsubmit="return confirm('عکس پروفایل حذف بشه؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-sm btn-danger">🗑 حذف عکس</button>
                    </form>
                @endif
            </div>

            <p class="hint" id="avatar-name">jpg، png یا webp · حداکثر ۲ مگابایت · حداقل ۱۰۰×۱۰۰</p>
            @error('avatar', 'avatar')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
    </section>

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-icon">📋</span><span class="stat-value">{{ $stats['tasks'] }}</span><span class="stat-label">کل کارها</span></div>
        <div class="stat-card"><span class="stat-icon">✅</span><span class="stat-value stat-good">{{ $stats['done'] }}</span><span class="stat-label">انجام‌شده</span></div>
        <div class="stat-card"><span class="stat-icon">🔥</span><span class="stat-value">{{ $stats['habits'] }}</span><span class="stat-label">عادت</span></div>
        <div class="stat-card"><span class="stat-icon">🏷️</span><span class="stat-value">{{ $stats['categories'] }}</span><span class="stat-label">دسته</span></div>
    </div>

    <div class="acc-cols">
        <section class="panel">
            <h2>✏️ ویرایش نام</h2>
            <form method="POST" action="{{ route('account.profile') }}" class="acc-form">
                @csrf
                @method('PUT')

                <div class="field">
                    <label for="name">نام</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="60" required>
                    @error('name', 'profile')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-sm btn-full">ذخیره</button>
            </form>
        </section>

        <section class="panel">
            <h2>🔒 تغییر رمز عبور</h2>
            <form method="POST" action="{{ route('account.password') }}" class="acc-form">
                @csrf
                @method('PUT')

                <div class="field">
                    <label for="current_password">رمز فعلی</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    @error('current_password', 'password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">رمز جدید (حداقل ۸ کاراکتر)</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" required>
                    @error('password', 'password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">تکرار رمز جدید</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn-sm btn-full">تغییر رمز</button>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-date]').forEach((el) => {
            if (!el.dataset.date) return;
            el.textContent = new Date(el.dataset.date + 'T00:00:00')
                .toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric', month: 'long', day: 'numeric' });
        });

        document.getElementById('avatar-file').addEventListener('change', (e) => {
            document.getElementById('avatar-name').textContent = e.target.files[0]?.name ?? 'عکسی انتخاب نشده';
        });
    </script>
@endpush