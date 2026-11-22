<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HabitController extends Controller
{
    private const MAX_HABITS = 30;

    public function index(Request $request)
    {
        $habits = $this->listFor($request);

        return view('habits', compact('habits'));
    }

    // برای بخش عادت‌ها توی صفحه‌ی لیست کارها
    public function data(Request $request)
    {
        return response()->json($this->listFor($request));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:60',
            'icon'  => 'nullable|string|max:16',
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $query = Habit::where('user_id', $request->user()->id);

        if ($query->count() >= self::MAX_HABITS) {
            throw ValidationException::withMessages([
                'name' => 'حداکثر ۳۰ عادت می‌تونی داشته باشی.',
            ]);
        }

        $habit = Habit::create([
            'user_id'  => $request->user()->id,
            'name'     => $data['name'],
            'icon'     => $data['icon'] ?? '🎯',
            'color'    => $data['color'] ?? '#8b5cf6',
            'position' => ($query->max('position') ?? -1) + 1,
        ]);

        return response()->json($this->payload($habit->load('logs')), 201);
    }

    public function toggle(Request $request, Habit $habit)
    {
        abort_unless($habit->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d|after:2000-01-01|before_or_equal:tomorrow',
        ]);

        $deleted = $habit->logs()->where('done_on', $data['date'])->delete();

        if (! $deleted) {
            $habit->logs()->firstOrCreate(['done_on' => $data['date']]);
        }

        return response()->json(['done' => ! $deleted]);
    }

    public function destroy(Request $request, Habit $habit)
    {
        abort_unless($habit->user_id === $request->user()->id, 403);

        $habit->delete();

        return response()->json(['deleted' => true]);
    }

    private function listFor(Request $request)
    {
        return Habit::where('user_id', $request->user()->id)
            ->with('logs')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn (Habit $h) => $this->payload($h))
            ->values();
    }

    private function payload(Habit $h): array
    {
        return [
            'id'    => $h->id,
            'name'  => $h->name,
            'icon'  => $h->icon,
            'color' => $h->color,
            'dates' => $h->logs->map(fn ($l) => $l->done_on->format('Y-m-d'))->values(),
        ];
    }
}