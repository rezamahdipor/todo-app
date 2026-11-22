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
@include('partials.avatar', ['user' => $user])

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-icon">📋</span><span class="stat-value">{{ $stats['tasks'] }}</span><span class="stat-label">کل کارها</span></div>
        <div class="stat-card"><span class="stat-icon">✅</span><span class="stat-value stat-good">{{ $stats['done'] }}</span><span class="stat-label">انجام‌شده</span></div>
        <div class="stat-card"><span class="stat-icon">🔥</span><span class="stat-value">{{ $stats['habits'] }}</span><span class="stat-label">عادت</span></div>
        <div class="stat-card"><span class="stat-icon">🏷️</span><span class="stat-value">{{ $stats['categories'] }}</span><span class="stat-label">دسته</span></div>
    </div>

    <section class="panel" style="margin-bottom: 14px">
        <h2>🖼️ عکس پروفایل</h2>
        <form method="POST" action="{{ route('account.avatar') }}" enctype="multipart/form-data" class="auth-form">
            @csrf
            @method('PUT')
            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" required>
            <p class="hint">jpg، png یا webp، حداکثر ۲ مگابایت، حداقل ۱۰۰×۱۰۰</p>
            @error('avatar', 'avatar')
                <div class="error">{{ $message }}</div>
            @enderror
            <button type="submit">آپلود عکس</button>
        </form>
        @if ($user->avatar)
            <form method="POST" action="{{ route('account.avatar.remove') }}" onsubmit="return confirm('عکس پروفایل حذف بشه؟')" style="margin-top: 8px">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-logout">حذف عکس</button>
            </form>
        @endif
    </section>

    <div class="acc-cols">
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