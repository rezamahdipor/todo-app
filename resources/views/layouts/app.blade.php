<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'لیست کارها')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <script>
        (function () {
            var theme = 'light';
            try {
                var saved = localStorage.getItem('theme');
                if (['light', 'dark', 'pink', 'mint', 'ocean', 'lavender', 'sunset'].indexOf(saved) !== -1) theme = saved;
            } catch (e) {}
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>

<body>
    <div class="bg-blobs" aria-hidden="true">
        <span class="blob b1"></span>
        <span class="blob b2"></span>
        <span class="blob b3"></span>
    </div>

    <div class="container @yield('container-class')">
        @yield('content')
    </div>

    <footer class="footer">
        ساخته شده توسط رضا مهدی‌پور · {{ date('Y') }}
    </footer>

    {{-- فقط توی تم صورتی دیده می‌شه --}}
    <p class="dedication">✨ تقدیم به M 💗 ✨</p>

    <script src="{{ asset('js/theme.js') }}?v={{ filemtime(public_path('js/theme.js')) }}"></script>
    @stack('scripts')
</body>
</html>