@extends('layouts.app')

@section('title', 'ورود')

@section('content')
    @include('partials.side-menu')
    <h1>خوش برگشتی 👋</h1>
    <p class="subtitle">برای دیدن و مدیریت کارهات وارد حسابت شو.</p>

    <form action="{{ route('login') }}" method="POST" class="auth-form">
        @csrf

        <label for="email">ایمیل</label>
        <input id="email" type="email" name="email" placeholder="example@mail.com" value="{{ old('email') }}" required autofocus>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <label for="password">رمز عبور</label>
        <input id="password" type="password" name="password" placeholder="رمز عبورت رو وارد کن" required>

        <label class="remember">
            <input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار
        </label>

        <button type="submit">ورود</button>
    </form>

    <p class="auth-link">هنوز حساب نداری؟ <a href="{{ route('register') }}">ثبت‌نام کن</a></p>
    <p class="auth-link"><a href="{{ route('home') }}">← بازگشت به صفحه‌ی اصلی</a></p>
@endsection