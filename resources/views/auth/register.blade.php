@extends('layouts.app')

@section('title', 'ثبت‌نام')
@section('container-class', 'container-auth')

@section('content')
    @include('partials.side-menu')

    <div class="reg">
        <aside class="reg-promo">
            <a href="{{ route('home') }}" class="reg-logo"><span>✅</span> لیست کارهای من</a>

            <h2>یه حساب رایگان بساز 🚀</h2>
            <p>کارها، عادت‌ها و نوشته‌هات رو یک‌جا مدیریت کن.</p>

            <ul>
                <li>📋 لیست کارها با دسته و تاریخ سررسید</li>
                <li>🔥 عادت‌ها با رکورد پشت‌سرهم</li>
                <li>📅 تقویم شمسی و 🗂️ کانبان</li>
                <li>💬 چت عمومی و ✍️ وبلاگ</li>
                <li>🎨 ۷ تم رنگی</li>
            </ul>

            <div class="reg-note">🔒 رمزت رمزنگاری می‌شه و لیستت فقط برای خودته.</div>
        </aside>

        <section class="reg-card">
            <h1>ثبت‌نام</h1>
            <p class="reg-sub">چند ثانیه بیشتر وقت نمی‌گیره.</p>

            <form method="POST" action="{{ route('register') }}" class="reg-form" id="reg-form">
                @csrf

                <div class="field">
                    <label for="name">نام</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="60" autocomplete="name" placeholder="مثلاً: رضا" required>
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="email">ایمیل</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" dir="ltr" autocomplete="email" placeholder="you@example.com" required>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password">رمز عبور</label>
                    <div class="pw">
                        <input type="password" id="password" name="password" dir="ltr" minlength="8" autocomplete="new-password" placeholder="حداقل ۸ کاراکتر" required>
                        <button type="button" class="pw-eye" aria-label="نمایش رمز">👁️</button>
                    </div>
                    <div class="pw-meter" aria-hidden="true"><i></i></div>
                    <span class="pw-text" aria-live="polite"></span>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">تکرار رمز عبور</label>
                    <div class="pw">
                        <input type="password" id="password_confirmation" name="password_confirmation" dir="ltr" autocomplete="new-password" required>
                        <button type="button" class="pw-eye" aria-label="نمایش رمز">👁️</button>
                    </div>
                    <span class="pw-match" aria-live="polite"></span>
                </div>

                <label class="reg-terms">
                    <input type="checkbox" required>
                    <span>
                        با
                        <a href="{{ Route::has('terms') ? route('terms') : '#' }}" target="_blank" rel="noopener">قوانین</a>
                        و
                        <a href="{{ Route::has('privacy') ? route('privacy') : '#' }}" target="_blank" rel="noopener">حریم خصوصی</a>
                        موافقم.
                    </span>
                </label>

                <button type="submit" class="reg-submit">🚀 ساخت حساب</button>
            </form>

            <p class="auth-link">حساب داری؟ <a href="{{ route('login') }}">وارد شو</a></p>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('reg-form');
            if (!form) return;

            const pw = form.querySelector('#password');
            const pw2 = form.querySelector('#password_confirmation');
            const bar = form.querySelector('.pw-meter i');
            const txt = form.querySelector('.pw-text');
            const match = form.querySelector('.pw-match');
            const btn = form.querySelector('.reg-submit');
            const label = btn.textContent;
            const LV = [['', ''], ['ضعیف', '#ef4444'], ['متوسط', '#f59e0b'], ['خوب', '#22c55e'], ['قوی', '#16a34a']];

            function score(v) {
                if (!v) return 0;
                if (v.length < 8) return 1;

                let s = 1;
                if (/[a-zA-Z]/.test(v) && /\d/.test(v)) s++;
                if (/[^A-Za-z0-9]/.test(v) || (/[a-z]/.test(v) && /[A-Z]/.test(v))) s++;
                if (v.length >= 12) s++;

                return Math.min(4, s);
            }

            function update() {
                const s = score(pw.value);

                bar.style.width = s * 25 + '%';
                bar.style.background = LV[s][1];
                txt.textContent = s ? 'قدرت رمز: ' + LV[s][0] : '';

                if (pw2.value) {
                    const ok = pw.value === pw2.value;

                    match.textContent = ok ? '✓ رمزها یکسانن' : '✗ رمزها یکسان نیستن';
                    match.className = 'pw-match ' + (ok ? 'ok' : 'bad');
                    pw2.setCustomValidity(ok ? '' : 'رمزها یکسان نیستن');
                } else {
                    match.textContent = '';
                    pw2.setCustomValidity('');
                }
            }

            pw.addEventListener('input', update);
            pw2.addEventListener('input', update);

            form.querySelectorAll('.pw-eye').forEach((b) => b.addEventListener('click', () => {
                const input = b.parentElement.querySelector('input');
                const show = input.type === 'password';

                input.type = show ? 'text' : 'password';
                b.textContent = show ? '🙈' : '👁️';
                b.setAttribute('aria-label', show ? 'پنهان کردن رمز' : 'نمایش رمز');
            }));

            form.addEventListener('submit', () => {
                btn.disabled = true;
                btn.textContent = 'در حال ساخت حساب...';
            });

            // برگشت با دکمه‌ی Back مرورگر
            window.addEventListener('pageshow', () => {
                btn.disabled = false;
                btn.textContent = label;
            });
        })();
    </script>
@endpush