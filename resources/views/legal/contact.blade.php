@extends('layouts.app')

@section('title', 'تماس با ما')
@section('container-class', 'container-blog')

@section('content')
    @include('partials.side-menu')

    <header class="post-head"><h1>✉️ تماس با ما</h1></header>

    <div class="post-body">
        <p>برای سؤال، پیشنهاد، گزارش محتوای نامناسب یا درخواست حذف حساب:</p>
        <ul>
            <li>ایمیل: <a href="mailto:contact@YOURDOMAIN.com">contact@YOURDOMAIN.com</a></li>
            <li>گیت‌هاب: <a href="https://github.com/rezamahdipor" target="_blank" rel="noopener">github.com/rezamahdipor</a></li>
        </ul>
        <p>سعی می‌کنیم هر چه زودتر جواب بدیم 🙏</p>
    </div>
@endsection

@push('head')
    <meta name="description" content="راه‌های تماس با ما برای سؤال، پیشنهاد و گزارش محتوا.">
@endpush