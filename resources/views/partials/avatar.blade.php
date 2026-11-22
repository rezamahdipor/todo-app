@php $avatarUrl = ! empty($user->avatar) ? asset('uploads/avatars/' . $user->avatar) : null; @endphp
@if ($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="avatar avatar-img {{ $class ?? '' }}" loading="lazy">
@else
    <div class="avatar {{ $class ?? '' }}" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</div>
@endif