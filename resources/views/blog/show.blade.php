@extends('layouts.app')

@section('title', $post->title)
@section('container-class', 'container-blog')

@section('content')
    @include('partials.site-nav')

    <article class="post">
        @unless ($post->isPublished())
            <div class="flash">👁️ این نوشته پیش‌نویسه و فقط خودت می‌بینیش.</div>
        @endunless

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