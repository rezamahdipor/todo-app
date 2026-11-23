<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'   => 'required|in:post,message',
            'id'     => 'required|integer|min:1',
            'reason' => 'required|in:' . implode(',', array_keys(Report::REASONS)),
            'note'   => 'nullable|string|max:300',
        ]);

        $owner = $data['type'] === 'post'
            ? Post::published()->where('id', $data['id'])->value('user_id')
            : ChatMessage::where('id', $data['id'])->value('user_id');

        abort_if($owner === null, 404);

        if ((int) $owner === $request->user()->id) {
            throw ValidationException::withMessages(['reason' => 'محتوای خودت رو نمی‌تونی گزارش کنی.']);
        }

        // هر نفر برای هر محتوا فقط یک گزارش دارد
        Report::firstOrCreate(
            ['user_id' => $request->user()->id, 'type' => $data['type'], 'target_id' => $data['id']],
            ['reason' => $data['reason'], 'note' => $data['note'] ?? null],
        );

        if ($request->expectsJson()) {
            return response()->json(['ok' => true], 201);
        }

        return back()->with('status', 'گزارشت ثبت شد؛ ممنون 🙏');
    }
}