@php $labels = \App\Models\Report::REASONS; @endphp

<nav class="adm-tabs adm-sub" aria-label="وضعیت گزارش‌ها">
    <a href="{{ route('admin.reports') }}" @class(['on' => $state === 'open'])>🚩 باز</a>
    <a href="{{ route('admin.reports', ['state' => 'resolved']) }}" @class(['on' => $state === 'resolved'])>✅ بسته‌شده</a>
</nav>

<div class="adm-msgs">
    @forelse ($reports as $r)
        @php
            $isPost = $r->type === 'post';
            $target = $isPost ? ($posts[$r->target_id] ?? null) : ($msgs[$r->target_id] ?? null);
            $count = $counts[$r->type . ':' . $r->target_id] ?? 1;
        @endphp

        <article class="adm-rep">
            <div class="adm-rep-head">
                <span class="badge">{{ $isPost ? '📰 نوشته' : '💬 پیام چت' }}</span>
                <span class="badge bad">{{ $labels[$r->reason] ?? $r->reason }}</span>
                @if ($state === 'open' && $count > 1)
                    <span class="badge">{{ $count }} گزارش</span>
                @endif
                <small>
                    از {{ $r->reporter->name }} ·
                    <time datetime="{{ $r->created_at->toIso8601String() }}" data-ft>{{ $r->created_at->format('H:i') }}</time>
                </small>
            </div>

            @if ($target)
                <div class="adm-rep-target">
                    @if ($isPost)
                        <strong>{{ $target->title }}</strong>
                        <p>{{ \Illuminate\Support\Str::limit($target->body, 220) }}</p>
                        <small>نویسنده: {{ $target->user->name }}@if ($target->hidden_at) · پنهان‌شده @endif</small>
                    @else
                        <p>{{ $target->body }}</p>
                        <small>فرستنده: {{ $target->user->name }} · اتاق {{ $target->room }}</small>
                    @endif
                </div>
            @else
                <p class="adm-rep-target gone">این محتوا دیگه وجود نداره.</p>
            @endif

            @if ($r->note)
                <p class="adm-rep-note">توضیح گزارش‌دهنده: «{{ $r->note }}»</p>
            @endif

            @if ($state === 'open')
                <form method="POST" action="{{ route('admin.reports.act', $r) }}" class="adm-acts">
                    @csrf

                    @if ($target)
                        @if ($isPost && ! $target->hidden_at)
                            <button type="submit" name="action" value="hide" class="btn-sm btn-ghost">🙈 پنهان</button>
                        @endif

                        <button type="submit" name="action" value="delete" class="btn-sm btn-danger"
                                onclick="return confirm('{{ $isPost ? 'این نوشته برای همیشه حذف بشه؟' : 'این پیام حذف بشه؟' }}')">🗑 حذف</button>

                        @if (! $target->user->is_admin && $target->user_id !== auth()->id())
                            <button type="submit" name="action" value="ban" class="btn-sm btn-danger"
                                    onclick="return confirm('نویسنده مسدود و محتوا برداشته بشه؟')">🚫 مسدود نویسنده</button>
                        @endif
                    @endif

                    <button type="submit" name="action" value="dismiss" class="btn-sm">✅ رد گزارش</button>
                </form>
            @endif
        </article>
    @empty
        <p class="empty">{{ $state === 'open' ? 'گزارشی باز نیست 🎉' : 'هنوز گزارشی بسته نشده.' }}</p>
    @endforelse
</div>

<div class="pager">
    @if ($reports->previousPageUrl()) <a href="{{ $reports->previousPageUrl() }}" class="topbar-link">→ جدیدتر</a> @else <span></span> @endif
    @if ($reports->nextPageUrl()) <a href="{{ $reports->nextPageUrl() }}" class="topbar-link">قدیمی‌تر ←</a> @endif
</div>