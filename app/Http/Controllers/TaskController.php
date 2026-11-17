<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->with('subtasks')
            ->orderBy('position')
            ->orderByDesc('id')
            ->get();

        $categories = $request->user()
            ->categories()
            ->orderBy('id')
            ->get(['id', 'name', 'color']);

        // زیرتسک‌ها به تفکیک شماره‌ی کار، برای فرستادن به جاوااسکریپت
        $subtasksByTask = $tasks->mapWithKeys(fn ($task) => [
            $task->id => $task->subtasks->map(fn ($sub) => [
                'id'      => $sub->id,
                'title'   => $sub->title,
                'is_done' => $sub->is_done,
            ])->values(),
        ]);

        return view('tasks.index', compact('tasks', 'categories', 'subtasksByTask'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'priority'    => 'sometimes|in:low,medium,high',
            'category_id' => ['nullable', 'integer', $this->ownCategory($request)],
            'due_date'    => 'nullable|date_format:Y-m-d',
        ]);

        $data['priority'] = $data['priority'] ?? 'medium';

        // کار جدید بالای لیست قرار می‌گیره: کمترین position منهای ۱
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
            'title'       => 'sometimes|required|string|max:255',
            'is_done'     => 'sometimes|boolean',
            'priority'    => 'sometimes|in:low,medium,high',
            'category_id' => ['sometimes', 'nullable', 'integer', $this->ownCategory($request)],
            'due_date'    => 'sometimes|nullable|date_format:Y-m-d',
        ]);

        // اگه وضعیت انجام‌شدن عوض شد، زمان انجامش رو ثبت یا پاک می‌کنیم
        if (array_key_exists('is_done', $data) && (bool) $data['is_done'] !== $task->is_done) {
            $data['completed_at'] = $data['is_done'] ? now() : null;
        }

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

        $ownedCount = $request->user()->tasks()->whereIn('id', $ids)->count();
        abort_unless($ownedCount === count($ids), 403);

        DB::transaction(function () use ($ids, $request) {
            foreach ($ids as $index => $id) {
                $request->user()->tasks()->whereKey($id)->update(['position' => $index]);
            }
        });

        return response()->json(['ok' => true]);
    }

    // قانونی که می‌گه دسته‌بندی باید مال خود کاربر باشه
    private function ownCategory(Request $request)
    {
        return Rule::exists('categories', 'id')->where('user_id', $request->user()->id);
    }
}