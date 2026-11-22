@extends('layouts.app')

@section('title', 'ثبت‌نام')

@section('content')
    @include('partials.side-menu')
    <h1>ساخت حساب جدید ✨</h1>
    <p class="subtitle">در چند ثانیه ثبت‌نام کن و لیست کارهای شخصی خودت رو بساز.</p>

    <form action="{{ route('register') }}" method="POST" class="auth-form">
        @csrf

        <label for="name">نام</label>
        <input id="name" type="text" name="name" placeholder="نام شما" value="{{ old('name') }}" required autofocus>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label for="email">ایمیل</label>
        <input id="email" type="email" name="email" placeholder="example@mail.com" value="{{ old('email') }}" required>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <label for="password">رمز عبور</label>
        <input id="password" type="password" name="password" placeholder="حداقل ۸ کاراکتر" required>
        <small class="hint">برای امنیت بیشتر از ترکیب حروف و عدد استفاده کن.</small>
        @error('password') <p class="error">{{ $message }}</p> @enderror

        <label for="password_confirmation">تکرار رمز عبور</label>
        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="رمز عبور رو دوباره وارد کن" required>

        <button type="submit">ثبت‌نام</button>
    </form>

    <p class="auth-link">قبلاً ثبت‌نام کردی؟ <a href="{{ route('login') }}">وارد شو</a></p>
    <p class="auth-link"><a href="{{ route('home') }}">← بازگشت به صفحه‌ی اصلی</a></p>
@endsection