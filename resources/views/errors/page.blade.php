<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | لیست کارهای من</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        try { var t = localStorage.getItem('theme'); if (t) document.documentElement.dataset.theme = t; } catch (e) {}
    </script>
</head>
<body>
    <div class="bg-blobs" aria-hidden="true">
        <div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>
    </div>

    <main class="container err">
        <div class="err-code">{{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p class="hero-text">{{ $text }}</p>

        <div class="hero-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">🏠 صفحه‌ی اصلی</a>
            <a href="javascript:history.back()" class="btn btn-outline">← برگشت</a>
        </div>
    </main>
</body>
</html>