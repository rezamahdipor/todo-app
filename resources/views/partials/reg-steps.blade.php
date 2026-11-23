@php $step = $step ?? 1; @endphp
<ol class="reg-steps s{{ $step }}" aria-label="مراحل ثبت‌نام">
    <li @class(['on' => $step >= 1, 'now' => $step === 1])><b>{{ $step > 1 ? '✓' : '۱' }}</b><span>حساب کاربری</span></li>
    <li @class(['on' => $step >= 2, 'now' => $step === 2])><b>۲</b><span>مشخصات تکمیلی</span></li>
</ol>