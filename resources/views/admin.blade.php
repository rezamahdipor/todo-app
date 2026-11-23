@extends('layouts.app')

@section('title', 'پنل مدیریت')
@section('container-class', 'container-admin')

@section('content')
    @include('partials.side-menu')

    <div class="topbar"><span>🛡️ پنل مدیریت</span></div>

    @if (session('status'))
        <div class="flash" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash flash-bad" role="alert">{{ $errors->first() }}</div>
    @endif

    <nav class="adm-tabs" aria-label="بخش‌های مدیریت">
        <a href="{{ route('admin.index') }}" @class(['on' => $tab === 'overview'])>📊 نمای کلی</a>
        <a href="{{ route('admin.users') }}" @class(['on' => $tab === 'users'])>👥 کاربرها</a>
        <a href="{{ route('admin.posts') }}" @class(['on' => $tab === 'posts'])>📰 نوشته‌ها</a>
        <a href="{{ route('admin.chat') }}" @class(['on' => $tab === 'chat'])>💬 چت</a>
        <a href="{{ route('admin.reports') }}" @class(['on' => $tab === 'reports'])>🚩 گزارش‌ها@if (($openReports ?? 0) > 0)<span class="adm-count">{{ $openReports }}</span>@endif</a>
    </nav>

    {{-- ============ نمای کلی ============ --}}
    @if ($tab === 'overview')
        @php
            $cards = [
                ['👥', 'کاربر', 'users', route('admin.users')],
                ['🟢', 'آنلاین', 'online', route('admin.users', ['filter' => 'online'])],
                ['👑', 'مدیر', 'admins', route('admin.users', ['filter' => 'admins'])],
                ['🚫', 'مسدود', 'banned', route('admin.users', ['filter' => 'banned'])],
                ['📰', 'نوشته‌ی منتشرشده', 'posts', route('admin.posts')],
                ['📝', 'پیش‌نویس', 'drafts', route('admin.posts', ['filter' => 'draft'])],
                ['🙈', 'پنهان‌شده', 'hidden', route('admin.posts', ['filter' => 'hidden'])],
                ['📋', 'کل کارها', 'tasks', null],
                ['🔥', 'عادت', 'habits', null],
                ['💬', 'پیام چت', 'messages', route('admin.chat')],
                ['🚩', 'گزارش باز', 'reports', route('admin.reports')],
            ];
        @endphp

        <div class="stats-grid adm-stats">
            @foreach ($cards as [$ico, $label, $key, $url])
                @php $tag = $url ? 'a' : 'div'; @endphp
                <{{ $tag }} @if ($url) href="{{ $url }}" @endif class="stat-card {{ $url ? 'stat-link' : '' }}">
                    <span class="stat-icon">{{ $ico }}</span>
                    <span class="stat-value">{{ number_format($stats[$key]) }}</span>
                    <span class="stat-label">{{ $label }}</span>
                </{{ $tag }}>
            @endforeach
        </div>

        <div class="adm-two">
            <section class="panel">
                <h2>🆕 تازه‌ترین کاربرها</h2>
                <ul class="adm-mini">
                    @foreach ($recentUsers as $u)
                        <li>
                            @include('partials.avatar', ['user' => $u])
                            <div><strong>{{ $u->name }}</strong><small>{{ $u->email }}</small></div>
                            <time datetime="{{ $u->created_at->toDateString() }}" data-fa>{{ $u->created_at->format('Y-m-d') }}</time>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="panel">
                <h2>📰 تازه‌ترین نوشته‌ها</h2>
                <ul class="adm-mini">
                    @forelse ($recentPosts as $p)
                        <li>
                            <div>
                                <strong>{{ $p->title }}</strong>
                                <small>{{ $p->user->name }}</small>
                            </div>
                            @if ($p->hidden_at) <span class="badge bad">پنهان</span>
                            @elseif ($p->isPublished()) <span class="badge live">منتشرشده</span>
                            @else <span class="badge">پیش‌نویس</span>
                            @endif
                        </li>
                    @empty
                        <li class="empty">هنوز نوشته‌ای نیست.</li>
                    @endforelse
                </ul>
            </section>
        </div>

    {{-- ============ کاربرها ============ --}}
    @elseif ($tab === 'users')
        <nav class="adm-tabs adm-sub" aria-label="فیلتر کاربرها">
            <a href="{{ route('admin.users') }}" @class(['on' => ! $filter])>همه</a>
            <a href="{{ route('admin.users', ['filter' => 'admins']) }}" @class(['on' => $filter === 'admins'])>👑 مدیرها</a>
            <a href="{{ route('admin.users', ['filter' => 'banned']) }}" @class(['on' => $filter === 'banned'])>🚫 مسدودها</a>
            <a href="{{ route('admin.users', ['filter' => 'online']) }}" @class(['on' => $filter === 'online'])>🟢 آنلاین‌ها</a>
        </nav>

        <form method="GET" action="{{ route('admin.users') }}" class="adm-search">
            @if ($filter) <input type="hidden" name="filter" value="{{ $filter }}"> @endif
            <input type="text" name="q" value="{{ $q }}" placeholder="جستجوی نام یا ایمیل..." maxlength="60">
            <button type="submit" class="btn-sm">🔍 جستجو</button>
        </form>

        <div class="adm-scroll">
            <table class="adm-table">
                <thead>
                    <tr><th>کاربر</th><th>عضویت</th><th>کار</th><th>نوشته</th><th>وضعیت</th><th>عملیات</th></tr>
                </thead>
                <tbody>
                @forelse ($users as $u)
                    @php
                        $on = $u->last_seen_at && \Illuminate\Support\Carbon::parse($u->last_seen_at)->gt(now()->subMinutes(5));
                        $self = $u->id === auth()->id();
                    @endphp
                    <tr>
                        <td>
                            <div class="adm-user">
                                @include('partials.avatar', ['user' => $u])
                                <div><strong>{{ $u->name }}</strong><small>{{ $u->email }}</small></div>
                            </div>
                        </td>
                        <td><time datetime="{{ $u->created_at->toDateString() }}" data-fa>{{ $u->created_at->format('Y-m-d') }}</time></td>
                        <td>{{ $taskCounts[$u->id] ?? 0 }}</td>
                        <td>{{ $postCounts[$u->id] ?? 0 }}</td>
                        <td>
                            @if ($u->is_admin) <span class="badge live">مدیر</span> @endif
                            @if ($u->is_banned) <span class="badge bad">مسدود</span> @endif
                            @if ($on) <span class="badge live">آنلاین</span> @endif
                        </td>
                        <td>
                            @if ($self)
                                <span class="badge">تو</span>
                            @else
                                <div class="adm-acts">
                                    <form method="POST" action="{{ route('admin.users.admin', $u) }}"
                                          onsubmit="return confirm('{{ $u->is_admin ? 'دسترسی مدیریت این کاربر برداشته بشه؟' : 'این کاربر مدیر بشه؟ مدیرها می‌تونن کاربرها رو مسدود و محتوا رو حذف کنن.' }}')">
                                        @csrf
                                        <button type="submit" class="btn-sm btn-ghost">{{ $u->is_admin ? '⬇️ حذف مدیریت' : '👑 مدیر کن' }}</button>
                                    </form>

                                    @unless ($u->is_admin)
                                        <form method="POST" action="{{ route('admin.users.ban', $u) }}"
                                              onsubmit="return confirm('{{ $u->is_banned ? 'مسدودی این کاربر برداشته بشه؟' : 'این کاربر مسدود بشه؟' }}')">
                                            @csrf
                                            <button type="submit" class="btn-sm {{ $u->is_banned ? '' : 'btn-danger' }}">{{ $u->is_banned ? '✅ آزاد کردن' : '🚫 مسدود' }}</button>
                                        </form>
                                    @endunless

                                    @unless ($u->is_admin)
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="adm-del" data-name="{{ $u->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-sm btn-danger">🗑 حذف کاربر</button>
                                        </form>
                                    @endunless
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">کاربری پیدا نشد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pager">
            @if ($users->previousPageUrl()) <a href="{{ $users->previousPageUrl() }}" class="topbar-link">→ قبلی</a> @else <span></span> @endif
            @if ($users->nextPageUrl()) <a href="{{ $users->nextPageUrl() }}" class="topbar-link">بعدی ←</a> @endif
        </div>

    {{-- ============ نوشته‌ها ============ --}}
    @elseif ($tab === 'posts')
        <nav class="adm-tabs adm-sub" aria-label="فیلتر نوشته‌ها">
            <a href="{{ route('admin.posts') }}" @class(['on' => ! $filter])>همه</a>
            <a href="{{ route('admin.posts', ['filter' => 'hidden']) }}" @class(['on' => $filter === 'hidden'])>🙈 پنهان‌شده</a>
            <a href="{{ route('admin.posts', ['filter' => 'draft']) }}" @class(['on' => $filter === 'draft'])>📝 پیش‌نویس</a>
        </nav>

        <div class="adm-scroll">
            <table class="adm-table">
                <thead>
                    <tr><th>عنوان</th><th>نویسنده</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr>
                </thead>
                <tbody>
                @forelse ($posts as $p)
                    <tr>
                        <td class="adm-title">
                            @if ($p->isPublished())
                                <a href="{{ route('blog.show', [$p, $p->slug]) }}" target="_blank" rel="noopener">{{ $p->title }}</a>
                            @else
                                {{ $p->title }}
                            @endif
                        </td>
                        <td>{{ $p->user->name }}</td>
                        <td>
                            @if ($p->hidden_at) <span class="badge bad">پنهان</span>
                            @elseif ($p->isPublished()) <span class="badge live">منتشرشده</span>
                            @else <span class="badge">پیش‌نویس</span>
                            @endif
                        </td>
                        <td><time datetime="{{ $p->created_at->toDateString() }}" data-fa>{{ $p->created_at->format('Y-m-d') }}</time></td>
                        <td>
                            <div class="adm-acts">
                                <form method="POST" action="{{ route('admin.posts.hide', $p) }}">
                                    @csrf
                                    <button type="submit" class="btn-sm btn-ghost">{{ $p->hidden_at ? '👁️ نمایش' : '🙈 پنهان' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.posts.destroy', $p) }}" onsubmit="return confirm('این نوشته برای همیشه حذف بشه؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger">🗑 حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">نوشته‌ای پیدا نشد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pager">
            @if ($posts->previousPageUrl()) <a href="{{ $posts->previousPageUrl() }}" class="topbar-link">→ قبلی</a> @else <span></span> @endif
            @if ($posts->nextPageUrl()) <a href="{{ $posts->nextPageUrl() }}" class="topbar-link">بعدی ←</a> @endif
        </div>

    {{-- ============ گزارش‌ها ============ --}}
    @elseif ($tab === 'reports')
        @include('partials.admin-reports')

    {{-- ============ چت ============ --}}
    @else
        <nav class="adm-tabs adm-sub" aria-label="اتاق‌ها">
            <a href="{{ route('admin.chat') }}" @class(['on' => ! $room])>همه‌ی اتاق‌ها</a>
            @foreach ([1, 2, 3] as $n)
                <a href="{{ route('admin.chat', ['room' => $n]) }}" @class(['on' => $room === $n])>💬 عمومی {{ $n }}</a>
            @endforeach
        </nav>

        <div class="adm-msgs">
            @forelse ($messages as $m)
                <div class="adm-msg">
                    @include('partials.avatar', ['user' => $m->user])

                    <div class="adm-msg-body">
                        <div class="adm-msg-head">
                            <strong>{{ $m->user->name }}</strong>
                            <span class="badge">اتاق {{ $m->room }}</span>
                            <time datetime="{{ $m->created_at->toIso8601String() }}" data-ft>{{ $m->created_at->format('H:i') }}</time>
                        </div>
                        <p>{{ $m->body }}</p>
                    </div>

                    <form method="POST" action="{{ route('admin.chat.delete', $m) }}" onsubmit="return confirm('این پیام حذف بشه؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-sm btn-danger" aria-label="حذف پیام">🗑</button>
                    </form>
                </div>
            @empty
                <p class="empty">پیامی پیدا نشد.</p>
            @endforelse
        </div>

        <div class="pager">
            @if ($messages->previousPageUrl()) <a href="{{ $messages->previousPageUrl() }}" class="topbar-link">→ جدیدتر</a> @else <span></span> @endif
            @if ($messages->nextPageUrl()) <a href="{{ $messages->nextPageUrl() }}" class="topbar-link">قدیمی‌تر ←</a> @endif
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('time[data-fa]').forEach((t) => {
            const d = new Date(t.getAttribute('datetime') + 'T00:00:00');
            if (!isNaN(d)) {
                t.textContent = d.toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric', month: 'long', day: 'numeric' });
            }
        });

        document.querySelectorAll('time[data-ft]').forEach((t) => {
            const d = new Date(t.getAttribute('datetime'));
            if (!isNaN(d)) {
                t.textContent = d.toLocaleString('fa-IR-u-ca-persian', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            }
        });
    </script>
    <script>
        document.querySelectorAll('form.adm-del').forEach((f) => {
            f.addEventListener('submit', (e) => {
                const name = f.dataset.name;
                const typed = prompt('⚠️ حذف کاربر «' + name + '» دائمیه و همه‌ی کارها، عادت‌ها، نوشته‌ها و پیام‌هاش پاک می‌شن.\n\nبرای تأیید، نام کاربر رو دقیق بنویس:');

                if (typed === null || typed.trim() !== name.trim()) {
                    e.preventDefault();
                    if (typed !== null) alert('نام درست وارد نشد؛ حذف انجام نشد.');
                }
            });
        });
    </script>
@endpush