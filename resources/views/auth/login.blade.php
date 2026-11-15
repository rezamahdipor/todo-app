@extends('layouts.app')

@section('title', 'ورود')

@section('content')
    <h1>ورود</h1>

    <form action="{{ route('login') }}" method="POST" class="auth-form">
        @csrf

        <input type="email" name="email" placeholder="ایمیل" value="{{ old('email') }}" required>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <input type="password" name="password" placeholder="رمز عبور" required>

        <label class="remember">
            <input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار
        </label>

        <button type="submit">ورود</button>
    </form>

    <p class="auth-link">حساب نداری؟ <a href="{{ route('register') }}">ثبت‌نام کن</a></p>
@endsection