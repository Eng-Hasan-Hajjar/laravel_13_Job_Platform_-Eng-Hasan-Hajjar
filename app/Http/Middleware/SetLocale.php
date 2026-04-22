<?php

// ==========================================
// app/Http/Middleware/SetLocale.php
// ==========================================
namespace App\Http\Middleware;
 
use Illuminate\Http\Request;
use Closure;
 
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale')
            ?? auth()->user()?->locale
            ?? $request->cookie('locale')
            ?? config('app.locale', 'en');
 
        if (in_array($locale, ['ar', 'en'])) {
            app()->setLocale($locale);
        }
 
        return $next($request);
    }
}