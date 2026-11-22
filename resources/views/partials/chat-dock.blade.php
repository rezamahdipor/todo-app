{{-- دکمه و پنل چت عمومی (بالا راست، کنار دکمه‌ی پروفایل) --}}
<button type="button" id="chat-btn" class="chat-btn" aria-expanded="false" aria-controls="chat-panel" aria-label="چت عمومی" title="چت عمومی">
    💬<i class="chat-dot" hidden></i>
</button>

<section id="chat-panel" class="chat-panel" aria-label="چت عمومی">
    <div class="chat-head">
        <h2>💬 چت عمومی</h2>
        <a href="{{ route('chat.page') }}">صفحه‌ی کامل ←</a>
        <button type="button" class="chat-close" aria-label="بستن چت">✕</button>
    </div>

    @include('partials.chat-box')
</section>

<script src="{{ asset('js/chat.js') }}?v={{ filemtime(public_path('js/chat.js')) }}" defer></script>