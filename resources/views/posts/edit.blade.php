@extends('layouts.app')

@section('title', $post ? 'ویرایش نوشته' : 'نوشته‌ی جدید')
@section('container-class', 'container-blog')

@section('content')
    <div class="topbar">
        <span>{{ $post ? '✏️ ویرایش نوشته' : '➕ نوشته‌ی جدید' }}</span>
        <div class="topbar-actions">
            <a href="{{ route('posts.index') }}" class="topbar-link">← نوشته‌های من</a>
        </div>
    </div>

    <form method="POST" action="{{ $post ? route('posts.update', $post) : route('posts.store') }}" class="auth-form">
        @csrf
        @if ($post)
            @method('PUT')
        @endif

        <label for="title">عنوان</label>
        <input type="text" id="title" name="title" value="{{ old('title', $post?->title) }}" maxlength="150" required>
        @error('title') <div class="error">{{ $message }}</div> @enderror

        <label for="description">توضیح کوتاه برای گوگل (اختیاری، حداکثر ۱۶۰ کاراکتر)</label>
        <input type="text" id="description" name="description" value="{{ old('description', $post?->description) }}" maxlength="160">
        @error('description') <div class="error">{{ $message }}</div> @enderror

        <label for="body">متن</label>
        <textarea id="body" name="body" rows="14" required>{{ old('body', $post?->body) }}</textarea>
        <p class="hint">💡 برای تیتر وسط متن، یه خط رو با <code>## </code> شروع کن و بعدش یه خط خالی بذار.</p>
        @error('body') <div class="error">{{ $message }}</div> @enderror

        <label class="remember">
            <input type="checkbox" name="publish" value="1" @checked($errors->any() ? old('publish') : $post?->published_at)>
            منتشر بشه (اگه تیک نخوره، پیش‌نویس می‌مونه)
        </label>

        <button type="submit">ذخیره</button>
    </form>
@endsection