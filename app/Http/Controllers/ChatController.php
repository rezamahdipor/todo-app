<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private const ROOMS = [1, 2, 3];
    private const KEEP = 300; // آخرین پیام‌هایی که برای هر اتاق نگه داشته می‌شن

    public function page()
    {
        return view('chat');
    }

    // پیام‌های تازه‌ی یه اتاق + آخرین شماره‌ی پیام هر سه اتاق (برای نقطه‌ی قرمز)
    public function poll(Request $request)
    {
        $data = $request->validate([
            'room'  => 'required|integer|in:1,2,3',
            'after' => 'nullable|integer|min:0',
        ]);

        $after = (int) ($data['after'] ?? 0);

        $query = ChatMessage::with('user:id,name,avatar')->where('room', $data['room']);

        $messages = $after > 0
            ? $query->where('id', '>', $after)->orderBy('id')->limit(60)->get()
            : $query->orderByDesc('id')->limit(40)->get()->reverse()->values();

        $rows = ChatMessage::query()
            ->selectRaw('room, MAX(id) as last_id')
            ->groupBy('room')
            ->pluck('last_id', 'room');

        $latest = [];
        foreach (self::ROOMS as $r) {
            $latest[$r] = (int) ($rows[$r] ?? 0);
        }

        return response()->json([
            'messages' => $messages->map(fn ($m) => $this->payload($m))->values(),
            'latest'   => $latest,
        ])->header('Cache-Control', 'no-store');
    }

    public function send(Request $request, int $room)
    {
        abort_unless(in_array($room, self::ROOMS, true), 404);

        $data = $request->validate([
            'body' => 'required|string|max:500',
        ]);

        $message = new ChatMessage(['body' => $data['body']]);
        $message->room = $room;
        $message->user_id = $request->user()->id;
        $message->save();

        // گاهی پیام‌های خیلی قدیمی پاک می‌شن تا جدول سبک بمونه
        if (random_int(1, 20) === 1) {
            $edge = ChatMessage::where('room', $room)->orderByDesc('id')->skip(self::KEEP)->value('id');

            if ($edge) {
                ChatMessage::where('room', $room)->where('id', '<=', $edge)->delete();
            }
        }

        return response()->json(['id' => $message->id], 201);
    }

    private function payload(ChatMessage $m): array
    {
        return [
            'id'      => $m->id,
            'user_id' => $m->user_id,
            'name'    => $m->user->name,
            'avatar'  => $m->user->avatar ? asset('uploads/avatars/' . $m->user->avatar) : null,
            'body'    => $m->body,
            'at'      => $m->created_at->toIso8601String(),
        ];
    }
}