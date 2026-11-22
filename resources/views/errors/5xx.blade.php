@php
    $code = $exception->getStatusCode();
    $title = $code === 503 ? 'در حال بروزرسانی 🛠️' : 'مشکلی از سمت ما پیش اومد 😕';
    $text = $code === 503
        ? 'سایت موقتاً در دسترس نیست. چند دقیقه‌ی دیگه دوباره سر بزن.'
        : 'یه خطای غیرمنتظره رخ داد. چند لحظه بعد دوباره امتحان کن.';
@endphp
@include('errors.page', compact('code', 'title', 'text'))