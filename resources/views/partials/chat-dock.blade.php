{{-- دکمه و پنل چت عمومی (ستون بالا راست، زیر دکمه‌ی پروفایل) --}}
<button type="button" id="chat-btn" class="chat-btn" aria-expanded="false" aria-controls="chat-panel" aria-label="چت عمومی" data-tip="چت عمومی">
    <svg class="ci ci-chat" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M5 4h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-7l-4.5 3.6c-.6.5-1.5.1-1.5-.7V17H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" />
        <circle class="d1" cx="8" cy="10.5" r="1.3" />
        <circle class="d2" cx="12" cy="10.5" r="1.3" />
        <circle class="d3" cx="16" cy="10.5" r="1.3" />
    </svg>
    <svg class="ci ci-x" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
    <i class="chat-dot" hidden></i>
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