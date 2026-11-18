@extends('layouts.app')

@section('title', 'حساب کاربری')
@section('container-class', 'container-account')

@section('content')
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

    <div class="acc-head">
        <div class="avatar">{{ mb_substr($user->name, 0, 1) }}</div>
        <div>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <p>عضو از <span data-date="{{ $user->created_at?->format('Y-m-d') }}"></span></p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-icon">📋</span><span class="stat-value">{{ $stats['tasks'] }}</span><span class="stat-label">کل کارها</span></div>
        <div class="stat-card"><span class="stat-icon">✅</span><span class="stat-value stat-good">{{ $stats['done'] }}</span><span class="stat-label">انجام‌شده</span></div>
        <div class="stat-card"><span class="stat-icon">🔥</span><span class="stat-value">{{ $stats['habits'] }}</span><span class="stat-label">عادت</span></div>
        <div class="stat-card"><span class="stat-icon">🏷️</span><span class="stat-value">{{ $stats['categories'] }}</span><span class="stat-label">دسته</span></div>
    </div>

    <div class="acc-cols">
        <section class="panel">
            <h2>✏️ ویرایش نام</h2>
            <form method="POST" action="{{ route('account.profile') }}" class="auth-form">
                @csrf
                @method('PUT')

                <label for="name">نام</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="60" required>
                @error('name', 'profile')
                    <div class="error">{{ $message }}</div>
                @enderror

                <button type="submit">ذخیره</button>
            </form>
        </section>

        <section class="panel">
            <h2>🔒 تغییر رمز عبور</h2>
            <form method="POST" action="{{ route('account.password') }}" class="auth-form">
                @csrf
                @method('PUT')

                <label for="current_password">رمز فعلی</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                @error('current_password', 'password')
                    <div class="error">{{ $message }}</div>
                @enderror

                <label for="password">رمز جدید (حداقل ۸ کاراکتر)</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required>
                @error('password', 'password')
                    <div class="error">{{ $message }}</div>
                @enderror

                <label for="password_confirmation">تکرار رمز جدید</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>

                <button type="submit">تغییر رمز</button>
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
    </script>
@endpush