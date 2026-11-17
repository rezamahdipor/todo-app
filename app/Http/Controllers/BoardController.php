<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $columns = $request->user()
            ->tasks()
            ->with('category')
            ->orderBy('position')
            ->orderByDesc('id')
            ->get()
            ->groupBy('status');

        return view('board', compact('columns'));
    }

    public function move(Request $request)
    {
        $data = $request->validate([
            'status'  => 'required|in:todo,doing,done',
            'order'   => 'required|array|max:1000',
            'order.*' => 'integer',
        ]);

        $ids = array_values(array_unique($data['order']));

        $tasks = $request->user()->tasks()->whereIn('id', $ids)->get()->keyBy('id');
        abort_unless($tasks->count() === count($ids), 403);

        // همون جایگاه‌های position قبلی رو به ترتیب جدید می‌دیم تا ترتیب لیست اصلی بهم نریزه
        $slots = $tasks->pluck('position')->sort()->values();

        DB::transaction(function () use ($ids, $tasks, $slots, $data) {
            foreach ($ids as $i => $id) {
                $task = $tasks[$id];

                $task->status = $data['status'];
                $task->is_done = $data['status'] === 'done';
                $task->position = $slots[$i];

                if ($task->isDirty('is_done')) {
                    $task->completed_at = $task->is_done ? now() : null;
                }

                $task->save();
            }
        });

        return response()->json(['ok' => true]);
    }
}