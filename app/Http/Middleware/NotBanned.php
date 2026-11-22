<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_banned) {
            $msg = 'حساب شما مسدود شده و امکان ارسال پیام یا انتشار نوشته ندارید.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg, 'errors' => ['body' => [$msg]]], 403);
            }

            return back()->withInput()->withErrors(['title' => $msg]);
        }

        return $next($request);
    }
}