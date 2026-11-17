<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->with('category:id,name,color')
            ->get();

        $today = Carbon::today();

        // ---- شمارش‌های کلی ----
        $total  = $tasks->count();
        $done   = $tasks->where('is_done', true)->count();
        $active = $total - $done;
        $rate   = $total > 0 ? (int) round($done / $total * 100) : 0;

        $overdue = $tasks->filter(
            fn ($t) => ! $t->is_done && $t->due_date && $t->due_date->lt($today)
        )->count();

        // ---- هفت روز اخیر (قدیمی‌ترین اول، امروز آخر) ----
        $week = collect(range(6, 0))->map(function ($daysAgo) use ($tasks, $today) {
            $date = $today->copy()->subDays($daysAgo);

            $count = $tasks->filter(
                fn ($t) => $t->completed_at && $t->completed_at->isSameDay($date)
            )->count();

            return ['date' => $date, 'count' => $count];
        });

        $weekTotal = $week->sum('count');
        $weekMax   = max(1, $week->max('count'));

        // ---- رکورد پشت‌سرهم (Streak) ----
        $doneDays = $tasks
            ->filter(fn ($t) => $t->completed_at)
            ->map(fn ($t) => $t->completed_at->toDateString())
            ->unique()
            ->sort()
            ->values();

        $daySet = $doneDays->flip();

        // رکورد فعلی: اگه امروز کاری نکردی، از دیروز می‌شماریم
        $streak = 0;
        $cursor = $today->copy();

        if (! isset($daySet[$cursor->toDateString()])) {
            $cursor->subDay();
        }

        while (isset($daySet[$cursor->toDateString()])) {
            $streak++;
            $cursor->subDay();
        }

        // بهترین رکورد
        $bestStreak = 0;
        $run = 0;
        $prev = null;

        foreach ($doneDays as $day) {
            $current = Carbon::parse($day);

            $run = ($prev && $prev->copy()->addDay()->isSameDay($current)) ? $run + 1 : 1;
            $bestStreak = max($bestStreak, $run);
            $prev = $current;
        }

        // ---- به تفکیک اولویت ----
        $byPriority = collect(['high', 'medium', 'low'])->map(function ($p) use ($tasks) {
            $group = $tasks->where('priority', $p);

            return [
                'key'   => $p,
                'total' => $group->count(),
                'done'  => $group->where('is_done', true)->count(),
            ];
        });

        // ---- به تفکیک دسته ----
        $byCategory = $tasks->groupBy('category_id')->map(function ($group) {
            $category = $group->first()->category;

            return [
                'name'  => $category?->name ?? 'بدون دسته',
                'color' => $category?->color ?? '#94a3b8',
                'total' => $group->count(),
                'done'  => $group->where('is_done', true)->count(),
            ];
        })->sortByDesc('total')->values();

        return view('stats', compact(
            'total', 'done', 'active', 'rate', 'overdue',
            'week', 'weekTotal', 'weekMax',
            'streak', 'bestStreak',
            'byPriority', 'byCategory'
        ));
    }
}