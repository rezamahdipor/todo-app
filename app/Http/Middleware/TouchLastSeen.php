<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TouchLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        self::touch($request->user()?->id);

        return $next($request);
    }

    // حداکثر هر ۶۰ ثانیه یک بار برای هر کاربر توی دیتابیس می‌نویسه
    public static function touch(?int $id): void
    {
        if (! $id) {
            return;
        }

        if (Cache::add("seen:{$id}", 1, 60)) {
            DB::table('users')->where('id', $id)->update(['last_seen_at' => now()]);
        }
    }
}