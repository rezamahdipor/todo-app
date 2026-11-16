<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->orderBy('position')
            ->orderByDesc('id')
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        $data['priority'] = $data['priority'] ?? 'medium';

        // تسک جدید بالای لیست: یکی کمتر از کوچک‌ترین position
        $min = $request->user()->tasks()->min('position');
        $data['position'] = is_null($min) ? 0 : $min - 1;

        $task = $request->user()->tasks()->create($data);

        if ($request->expectsJson()) {
            return response()->json($task, 201);
        }

        return redirect()->route('tasks.index');
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'title'    => 'sometimes|required|string|max:255',
            'is_done'  => 'sometimes|boolean',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        $task->update($data);

        if ($request->expectsJson()) {
            return response()->json($task);
        }

        return redirect()->route('tasks.index');
    }

    public function destroy(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $task->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('tasks.index');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order'   => 'required|array|max:1000',
            'order.*' => 'integer',
        ]);

        $ids = array_values(array_unique($data['order']));

        // همه‌ی تسک‌ها باید مال همین کاربر باشن
        $ownedCount = $request->user()->tasks()->whereIn('id', $ids)->count();
        abort_unless($ownedCount === count($ids), 403);

        DB::transaction(function () use ($ids, $request) {
            foreach ($ids as $index => $id) {
                $request->user()->tasks()->whereKey($id)->update(['position' => $index]);
            }
        });

        return response()->json(['ok' => true]);
    }
}