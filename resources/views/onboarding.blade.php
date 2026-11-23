@extends('layouts.app')

@section('title', $editing ? 'مشخصات تکمیلی' : 'تکمیل مشخصات')
@section('container-class', 'container-auth')

@section('content')
    @include('partials.side-menu')

    @php
        $edu = old('education', $profile->education);
        $picked = (array) old('goals', $profile->goals ?? []);
        $org = (int) old('organized', $profile->organized);
        $fa = fn ($n) => strtr((string) $n, '0123456789', '۰۱۲۳۴۵۶۷۸۹');
        $uniList = ['associate', 'bachelor', 'master', 'phd'];
    @endphp

    <div class="reg">
        <aside class="reg-promo">
            <a href="{{ route('home') }}" class="reg-logo"><span>✅</span> لیست کارهای من</a>

            <h2>{{ $editing ? 'مشخصات تکمیلی تو 🎓' : 'یه قدم تا شروع! 🎉' }}</h2>
            <p>با دونستن کمی درباره‌ات، سایت رو برای آدم‌هایی مثل تو بهتر می‌کنیم؛ مثلاً اگه دانش‌آموزی، ابزارهای درسی بیشتری اضافه می‌کنیم.</p>

            <ul>
                <li>🎓 تحصیلات، رشته و پایه</li>
                <li>🔎 از کجا با سایت آشنا شدی</li>
                <li>🧭 چقدر خودت رو منظم می‌دونی</li>
                <li>🎯 هدفت از استفاده</li>
            </ul>

            <div class="reg-note">🔒 همه‌ی این بخش اختیاریه و برای بقیه‌ی کاربرها نمایش داده نمی‌شه.</div>
        </aside>

        <section class="reg-card">
            @unless ($editing)
                @include('partials.reg-steps', ['step' => 2])
            @endunless

            <h1>{{ $editing ? 'مشخصات تکمیلی' : 'کمی از خودت بگو 👋' }}</h1>
            <p class="reg-sub">هر چقدر دوست داری پر کن؛ هر وقت خواستی می‌تونی عوضش کنی.</p>

            <form method="POST" action="{{ route('onboarding.save') }}" class="reg-form" id="ob-form" autocomplete="off"
                  data-fields="{{ json_encode($fields, JSON_UNESCAPED_UNICODE) }}"
                  data-field="{{ old('field', $profile->field) }}">
                @csrf

                <div class="field">
                    <label for="education">تحصیلات</label>
                    <select id="education" name="education">
                        <option value="">انتخاب کن...</option>
                        @foreach ($levels as $k => $l)
                            <option value="{{ $k }}" @selected($edu === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('education') <div class="error">{{ $message }}</div> @enderror
                </div>

                {{-- دبیرستان و هنرستان --}}
                <div class="ob-sub" id="ob-school" hidden>
                    <div class="field">
                        <label for="field">رشته‌ی تحصیلی</label>
                        <select id="field" name="field" disabled></select>
                        @error('field') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="grade">پایه</label>
                        <select id="grade" name="grade" disabled>
                            <option value="">انتخاب کن...</option>
                            @foreach ($grades as $g => $l)
                                <option value="{{ $g }}" @selected((string) old('grade', $profile->grade) === (string) $g)>{{ $l }}</option>
                            @endforeach
                        </select>
                        @error('grade') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- دانشگاهی --}}
                <div class="ob-sub" id="ob-uni" hidden>
                    <div class="field">
                        <label for="major">رشته‌ی تحصیلی (اختیاری)</label>
                        <input type="text" id="major" name="field" maxlength="60" placeholder="مثلاً: مهندسی کامپیوتر" disabled
                               value="{{ in_array($edu, $uniList, true) ? old('field', $profile->field) : '' }}">
                        @error('field') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field">
                    <label for="referral">از کجا با سایت آشنا شدی؟</label>
                    <select id="referral" name="referral">
                        <option value="">انتخاب کن...</option>
                        @foreach ($sources as $k => $l)
                            <option value="{{ $k }}" @selected(old('referral', $profile->referral) === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('referral') <div class="error">{{ $message }}</div> @enderror
                </div>

                <fieldset class="field ob-set">
                    <legend>چقدر خودت رو منظم می‌دونی؟</legend>
                    <div class="ob-scale">
                        @foreach ($scale as $n => $l)
                            <label class="ob-pill">
                                <input type="radio" name="organized" value="{{ $n }}" @checked($org === $n)>
                                <span><b>{{ $fa($n) }}</b>{{ $l }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('organized') <div class="error">{{ $message }}</div> @enderror
                </fieldset>

                <fieldset class="field ob-set">
                    <legend>برای چی از سایت استفاده می‌کنی؟ (چندتا می‌تونی انتخاب کنی)</legend>
                    <div class="ob-chips">
                        @foreach ($goals as $k => $l)
                            <label class="ob-chip">
                                <input type="checkbox" name="goals[]" value="{{ $k }}" @checked(in_array($k, $picked, true))>
                                <span>{{ $l }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('goals') <div class="error">{{ $message }}</div> @enderror
                    @error('goals.*') <div class="error">{{ $message }}</div> @enderror
                </fieldset>

                <button type="submit" class="reg-submit">{{ $editing ? '💾 ذخیره' : '✅ ذخیره و ورود به سایت' }}</button>
            </form>

            @if ($editing)
                <p class="auth-link"><a href="{{ route('account') }}">← برگشت به حساب کاربری</a></p>
            @else
                <form method="POST" action="{{ route('onboarding.skip') }}" class="ob-skip">
                    @csrf
                    <button type="submit" class="btn-sm btn-ghost btn-full">فعلاً رد کن، بعداً پر می‌کنم</button>
                </form>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('ob-form');
            if (!form) return;

            const FIELDS = JSON.parse(form.dataset.fields);
            const saved = form.dataset.field;
            const edu = form.querySelector('#education');
            const school = form.querySelector('#ob-school');
            const uni = form.querySelector('#ob-uni');
            const sel = school.querySelector('select[name="field"]');
            const SCHOOL = ['high', 'vocational'];
            const UNI = ['associate', 'bachelor', 'master', 'phd'];

            // بخش مخفی غیرفعال می‌شه تا موقع ارسال فرم، مقدارش نره
            function toggle(box, on) {
                box.hidden = !on;
                box.querySelectorAll('input, select').forEach((c) => { c.disabled = !on; });
            }

            function update(first) {
                const v = edu.value;

                if (SCHOOL.includes(v)) {
                    sel.replaceChildren(
                        new Option('انتخاب کن...', ''),
                        ...Object.entries(FIELDS[v]).map(([k, l]) => new Option(l, k)),
                    );

                    if (first && [...sel.options].some((o) => o.value === saved)) sel.value = saved;
                }

                toggle(school, SCHOOL.includes(v));
                toggle(uni, UNI.includes(v));
            }

            edu.addEventListener('change', () => update(false));
            update(true);

            form.addEventListener('submit', () => {
                const b = form.querySelector('.reg-submit');
                b.disabled = true;
                b.textContent = 'در حال ذخیره...';
            });

            window.addEventListener('pageshow', () => { form.querySelector('.reg-submit').disabled = false; });
        })();
    </script>
@endpush