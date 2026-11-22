@extends('layouts.app')

@section('title', 'آمار و پیشرفت')

@section('container-class', 'container-stats')

@section('content')
    @include('partials.side-menu')
    @php
        $fa = fn ($n) => strtr((string) $n, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);

        // Carbon: 0 = یکشنبه ... 6 = شنبه
        $dayNames = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
        $priorityNames = ['high' => 'زیاد', 'medium' => 'متوسط', 'low' => 'کم'];
        $priorityColors = ['high' => '#ef4444', 'medium' => '#f59e0b', 'low' => '#0ea5e9'];

        // دونات
        $circ = round(2 * M_PI * 52, 2);
        $dash = round($circ * $rate / 100, 2);
    @endphp

    <div class="topbar">
        <a href="{{ route('tasks.index') }}" class="back-link">← بازگشت به لیست</a>
        <span>{{ auth()->user()->name }}</span>
    </div>

    <h1>📊 آمار و پیشرفت</h1>
    <p class="subtitle">نگاهی به عملکردت توی هفته‌ی اخیر و کل کارها</p>

    @if ($total === 0)
        <p class="empty stats-empty">هنوز کاری نداری که آماری ازش بسازیم. اول چند تا کار اضافه کن! ✨</p>
    @else
        {{-- کارت‌های بالا --}}
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">📋</span>
                <span class="stat-value">{{ $fa($total) }}</span>
                <span class="stat-label">کل کارها</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">✅</span>
                <span class="stat-value stat-good">{{ $fa($done) }}</span>
                <span class="stat-label">انجام‌شده</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⏳</span>
                <span class="stat-value">{{ $fa($active) }}</span>
                <span class="stat-label">باقی‌مانده</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⚠️</span>
                <span class="stat-value {{ $overdue > 0 ? 'stat-bad' : '' }}">{{ $fa($overdue) }}</span>
                <span class="stat-label">با تأخیر</span>
            </div>
        </div>

        {{-- دونات و رکورد --}}
        <div class="stats-row">
            <div class="panel donut-panel">
                <h2>پیشرفت کلی</h2>
                <div class="donut">
                    <svg viewBox="0 0 120 120" role="img" aria-label="{{ $rate }} درصد کارها انجام شده">
                        <circle class="donut-track" cx="60" cy="60" r="52" />
                        <circle class="donut-fill" cx="60" cy="60" r="52"
                                style="--circ: {{ $circ }}; --dash: {{ $dash }}" />
                    </svg>
                    <div class="donut-center">
                        <strong>{{ $fa($rate) }}٪</strong>
                        <span>انجام‌شده</span>
                    </div>
                </div>
            </div>

            <div class="panel streak-panel">
                <h2>رکورد پشت‌سرهم</h2>
                <div class="streak-number">
                    <span class="streak-fire {{ $streak > 0 ? 'lit' : '' }}">🔥</span>
                    <strong>{{ $fa($streak) }}</strong>
                    <span>روز</span>
                </div>
                <p class="streak-hint">
                    @if ($streak > 0)
                        هر روز حداقل یه کار انجام دادی. ادامه بده! 💪
                    @else
                        امروز یه کار انجام بده تا رکوردت شروع بشه.
                    @endif
                </p>
                <p class="streak-best">بهترین رکورد: {{ $fa($bestStreak) }} روز</p>
            </div>
        </div>

        {{-- نمودار هفتگی --}}
        <div class="panel">
            <div class="panel-head">
                <h2>کارهای انجام‌شده در هفت روز اخیر</h2>
                <span class="panel-badge">{{ $fa($weekTotal) }} کار</span>
            </div>

            <div class="week-chart">
                @foreach ($week as $day)
                    @php
                        $isToday = $day['date']->isToday();
                        $ratio = $day['count'] / $weekMax;
                    @endphp
                    <div class="week-col {{ $isToday ? 'today' : '' }}">
                        <div class="week-area">
                            <span class="week-count">{{ $day['count'] > 0 ? $fa($day['count']) : '' }}</span>
                            <div class="week-bar {{ $day['count'] === 0 ? 'zero' : '' }}"
                                 style="--n: {{ $ratio }}; --i: {{ $loop->index }}"></div>
                        </div>
                        <span class="week-day">{{ $isToday ? 'امروز' : $dayNames[$day['date']->dayOfWeek] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- اولویت و دسته --}}
        <div class="stats-row">
            <div class="panel">
                <h2>به تفکیک اولویت</h2>
                <div class="hbars">
                    @foreach ($byPriority as $row)
                        @php $pct = $row['total'] > 0 ? round($row['done'] / $row['total'] * 100) : 0; @endphp
                        <div class="hbar-row">
                            <div class="hbar-label">
                                <span>{{ $priorityNames[$row['key']] }}</span>
                                <span class="hbar-count">{{ $fa($row['done']) }} از {{ $fa($row['total']) }}</span>
                            </div>
                            <div class="hbar-track">
                                <div class="hbar-fill" style="--w: {{ $pct }}%; --c: {{ $priorityColors[$row['key']] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <h2>به تفکیک دسته</h2>
                <div class="hbars">
                    @foreach ($byCategory as $row)
                        @php $pct = $row['total'] > 0 ? round($row['done'] / $row['total'] * 100) : 0; @endphp
                        <div class="hbar-row">
                            <div class="hbar-label">
                                <span>{{ $row['name'] }}</span>
                                <span class="hbar-count">{{ $fa($row['done']) }} از {{ $fa($row['total']) }}</span>
                            </div>
                            <div class="hbar-track">
                                <div class="hbar-fill" style="--w: {{ $pct }}%; --c: {{ $row['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection