<div class="chat" data-chat
     data-poll="{{ route('chat.poll') }}"
     data-send="{{ url('/chat') }}"
     data-me="{{ auth()->id() }}"
     data-admin="{{ auth()->user()->is_admin ? 1 : 0 }}">

    <div class="chat-rooms" role="tablist" aria-label="اتاق‌ها">
        @foreach ([1 => '۱', 2 => '۲', 3 => '۳'] as $n => $label)
            <button type="button" role="tab" class="chat-room" data-room="{{ $n }}" aria-selected="{{ $n === 1 ? 'true' : 'false' }}">
                💬 عمومی {{ $label }}<i class="chat-new" hidden></i>
            </button>
        @endforeach
    </div>

    <div class="chat-list" role="log" aria-live="polite"></div>

    <p class="chat-err" role="alert" hidden></p>

    <form class="chat-form" autocomplete="off">
        <input type="text" maxlength="500" placeholder="پیامت رو بنویس..." aria-label="متن پیام" required>
        <button type="submit" class="btn-sm">ارسال</button>
    </form>
</div>