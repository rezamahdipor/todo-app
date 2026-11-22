<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TouchLastSeen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LiveStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        // کاربری که صفحه‌ی ولکام بازه هم «آنلاین» حساب می‌شه
        TouchLastSeen::touch($request->user()?->id);

        // ۱۰ ثانیه کش می‌شه تا دیتابیس زیر فشار نره
        $data = Cache::remember('live-stats-v2', 10, function () {
            $published = fn () => DB::table('posts')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());

            return [
                'online'     => DB::table('users')->where('last_seen_at', '>=', now()->subMinutes(5))->count(),
                'users'      => DB::table('users')->count(),
                'tasks'      => DB::table('tasks')->count(),
                'done'       => DB::table('tasks')->where('is_done', true)->count(),
                'today'      => DB::table('tasks')->where('completed_at', '>=', now()->startOfDay())->count(),
                'habits'     => DB::table('habits')->count(),
                'posts'      => $published()->count(),
                'authors'    => $published()->distinct()->count('user_id'),
                'posts_week' => $published()->where('published_at', '>=', now()->subDays(7))->count(),
            ];
        });

        return response()->json($data)->header('Cache-Control', 'no-store');
    }
}