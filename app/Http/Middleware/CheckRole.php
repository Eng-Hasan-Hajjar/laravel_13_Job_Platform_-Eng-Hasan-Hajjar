<?php

// ==========================================
// app/Http/Middleware/CheckRole.php
// ==========================================
namespace App\Http\Middleware;
 
use Illuminate\Http\Request;
use Closure;
 
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}
 

