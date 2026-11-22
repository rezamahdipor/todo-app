@extends('layouts.app')

@section('title', 'عادت‌ها')
@section('container-class', 'container-habits')

@section('content')
    @include('partials.side-menu')
    <div class="topbar">
        <span>سلام {{ auth()->user()->name }} 👋</span>
        <div class="topbar-actions">
            <a href="{{ route('tasks.index') }}" class="topbar-link">📋 لیست</a>
            <a href="{{ route('board') }}" class="topbar-link">🗂️ کانبان</a>
            <a href="{{ route('stats') }}" class="topbar-link">📊 آمار</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">خروج</button>
            </form>
        </div>
    </div>

    <h1>🔥 عادت‌های من</h1>
    <p class="subtitle" id="hb-sub">&nbsp;</p>

    <div class="progress-wrap">
        <div class="progress-info">
            <span>پیشرفت امروز</span>
            <span id="hb-percent">۰٪</span>
        </div>
        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div id="hb-bar" class="progress-bar" style="width: 0%"></div>
        </div>
    </div>

    <div class="stats-grid hb-stats">
        <div class="stat-card"><span class="stat-icon">✅</span><span class="stat-value stat-good" id="hs-today">۰</span><span class="stat-label">امروز</span></div>
        <div class="stat-card"><span class="stat-icon">🔥</span><span class="stat-value" id="hs-streak">۰</span><span class="stat-label">بهترین رکورد فعلی</span></div>
        <div class="stat-card"><span class="stat-icon">📈</span><span class="stat-value" id="hs-rate">۰٪</span><span class="stat-label">میانگین ۳۰ روز</span></div>
    </div>

    <div class="fold" id="fold-habit">
        <button type="button" class="fold-toggle" aria-expanded="false" aria-controls="habit-body">
            <span>➕ عادت جدید</span>
            <span class="fold-arrow" aria-hidden="true">▾</span>
        </button>
        <div class="fold-body" id="habit-body">
            <div class="fold-inner">
                <form id="hb-form" class="add-form hb-form" autocomplete="off">
                    <div class="add-row">
                        <input type="text" name="name" placeholder="مثلاً: ۸ لیوان آب" maxlength="60" required>
                        <button type="submit">افزودن</button>
                    </div>
                    <div class="add-options">
                        <select name="icon" aria-label="آیکون">
                            <option value="💧">💧 آب</option>
                            <option value="🏃">🏃 ورزش</option>
                            <option value="📚">📚 مطالعه</option>
                            <option value="🧘">🧘 مدیتیشن</option>
                            <option value="🍎">🍎 تغذیه</option>
                            <option value="😴">😴 خواب</option>
                            <option value="✍️">✍️ نوشتن</option>
                            <option value="💊">💊 دارو</option>
                            <option value="🧹">🧹 نظافت</option>
                            <option value="🎯" selected>🎯 هدف</option>
                        </select>
                        <input type="color" name="color" value="#8b5cf6" aria-label="رنگ عادت">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="hb-list" class="hb-list"></div>

    <p class="tip">💡 دکمه‌ی گرد = انجام شدن امروز. روی هر خونه‌ی شبکه کلیک کن تا اون روز تیک بخوره یا برداشته بشه. ستون‌ها از شنبه شروع می‌شن.</p>
@endsection

@push('scripts')
    <script>
        window.APP_HABITS = @json($habits);
    </script>
    <script src="{{ asset('js/habits.js') }}?v={{ filemtime(public_path('js/habits.js')) }}"></script>
@endpush