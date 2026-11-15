@extends('layouts.app')

@section('title', 'ثبت‌نام')

@section('content')
    <h1>ثبت‌نام</h1>

    <form action="{{ route('register') }}" method="POST" class="auth-form">
        @csrf

        <input type="text" name="name" placeholder="نام" value="{{ old('name') }}" required>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <input type="email" name="email" placeholder="ایمیل" value="{{ old('email') }}" required>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <input type="password" name="password" placeholder="رمز عبور (حداقل ۸ کاراکتر)" required>
        @error('password') <p class="error">{{ $message }}</p> @enderror

        <input type="password" name="password_confirmation" placeholder="تکرار رمز عبور" required>

        <button type="submit">ثبت‌نام</button>
    </form>

    <p class="auth-link">حساب داری؟ <a href="{{ route('login') }}">وارد شو</a></p>
@endsection