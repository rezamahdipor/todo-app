@extends('layouts.app')

@section('title', 'چت عمومی')
@section('container-class', 'container-chat')

@section('content')
    @include('partials.side-menu')

    <div class="topbar"><span>💬 چت عمومی</span></div>

    <h1>💬 چت عمومی</h1>
    <p class="subtitle">سه اتاق عمومی؛ پیام‌ها هر چند ثانیه خودکار بروز می‌شن.</p>

    @include('partials.chat-box')
@endsection

@push('scripts')
    <script src="{{ asset('js/chat.js') }}?v={{ filemtime(public_path('js/chat.js')) }}"></script>
@endpush