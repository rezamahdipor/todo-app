<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboarded
{
    // کاربر تازه‌ثبت‌نام‌شده اول به مرحله‌ی دوم می‌ره (فقط برای باز کردن صفحه‌ها)
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && $user->onboarded_at === null
            && $request->isMethod('GET')
            && ! $request->expectsJson()
            && ! $request->routeIs('onboarding')) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}