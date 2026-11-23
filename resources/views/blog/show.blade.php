@extends('layouts.app')

@section('title', $post->title)
@section('container-class', 'container-blog')

@section('content')
    @include('partials.site-nav')

    <article class="post">
        @unless ($post->isPublished())
<div class="flash">{{ $post->hidden_at ? '🚫 این نوشته توسط مدیریت پنهان شده و برای بقیه دیده نمی‌شه.' : '👁️ این نوشته پیش‌نویسه و فقط خودت می‌بینیش.' }}</div>        @endunless

        @if (session('status'))
            <div class="flash" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash flash-bad" role="alert">{{ $errors->first() }}</div>
        @endif

        <header class="post-head">
            <h1>{{ $post->title }}</h1>

            <div class="by by-lg">
                @include('partials.avatar', ['user' => $post->user])
                <a href="{{ route('blog.author', $post->user) }}">{{ $post->user->name }}</a>
                @if ($post->published_at)
                    · <time datetime="{{ $post->published_at->toDateString() }}" data-fa>{{ $post->published_at->format('Y-m-d') }}</time>
                @endif
                · ⏱️ {{ $post->readingMinutes() }} دقیقه مطالعه
            </div>
        </header>

        <div class="post-body">
            @foreach (preg_split('/\n{2,}/', str_replace("\r", '', trim($post->body))) as $block)
                @if (str_starts_with($block, '## '))
                    <h2>{{ trim(substr($block, 3)) }}</h2>
                @else
                    <p>{!! nl2br(e($block)) !!}</p>
                @endif
            @endforeach
        </div>
    @auth
            @if ($post->isPublished() && auth()->id() !== $post->user_id)
                <details class="report-box">
                    <summary>🚩 گزارش این نوشته</summary>

                    <form method="POST" action="{{ route('reports.store') }}" class="reg-form">
                        @csrf
                        <input type="hidden" name="type" value="post">
                        <input type="hidden" name="id" value="{{ $post->id }}">

                        <div class="field">
                            <label for="rp-reason">دلیل گزارش</label>
                            <select id="rp-reason" name="reason" required>
                                @foreach (\App\Models\Report::REASONS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="rp-note">توضیح (اختیاری)</label>
                            <input type="text" id="rp-note" name="note" maxlength="300">
                        </div>

                        <button type="submit" class="btn-sm btn-danger">ارسال گزارش</button>
                    </form>
                </details>
            @endif
        @else
            <p class="report-hint"><a href="{{ route('login') }}">وارد شو</a> تا بتونی این نوشته رو گزارش کنی.</p>
        @endauth
    </article>

    @if ($more->isNotEmpty())
        <section class="panel" style="margin-top: 18px">
            <h2>📚 بیشتر بخون</h2>
            <ul class="more-list">
                @foreach ($more as $m)
                    <li><a href="{{ route('blog.show', [$m, $m->slug]) }}">{{ $m->title }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection

@push('head')
    <meta name="description" content="{{ $post->summary() }}">
    <link rel="canonical" href="{{ route('blog.show', [$post, $post->slug]) }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ $post->summary() }}">
    <meta property="og:url" content="{{ route('blog.show', [$post, $post->slug]) }}">
    @unless ($post->isPublished())
        <meta name="robots" content="noindex">
    @endunless
    <script type="application/ld+json">{!! json_encode([
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'headline'         => $post->title,
        'description'      => $post->summary(),
        'datePublished'    => optional($post->published_at)->toAtomString(),
        'dateModified'     => $post->updated_at->toAtomString(),
        'author'           => ['@type' => 'Person', 'name' => $post->user->name],
        'mainEntityOfPage' => route('blog.show', [$post, $post->slug]),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush