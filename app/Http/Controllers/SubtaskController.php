<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubtaskController extends Controller
{
    private const MAX_PER_TASK = 50;

    public function store(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        if ($task->subtasks()->count() >= self::MAX_PER_TASK) {
            throw ValidationException::withMessages([
                'title' => 'هر کار حداکثر ۵۰ زیرتسک می‌تونه داشته باشه.',
            ]);
        }

        // زیرتسک جدید ته لیست قرار می‌گیره
        $data['position'] = ($task->subtasks()->max('position') ?? -1) + 1;

        $subtask = $task->subtasks()->create($data);

        return response()->json($subtask->refresh(), 201);
    }

    public function update(Request $request, Subtask $subtask)
    {
        $this->authorizeOwner($request, $subtask);

        $data = $request->validate([
            'title'   => 'sometimes|required|string|max:255',
            'is_done' => 'sometimes|boolean',
        ]);

        $subtask->update($data);

        return response()->json($subtask);
    }

    public function destroy(Request $request, Subtask $subtask)
    {
        $this->authorizeOwner($request, $subtask);

        $subtask->delete();

        return response()->json(['deleted' => true]);
    }

    // فقط صاحب کارِ اصلی اجازه‌ی دست زدن به زیرتسک رو داره
    private function authorizeOwner(Request $request, Subtask $subtask): void
    {
        abort_unless($subtask->task->user_id === $request->user()->id, 403);
    }
}