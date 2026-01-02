<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Memanfaatkan helper isSuperadmin() yang Komandan buat di model
        if ($role === 'superadmin' && !$request->user()->isSuperadmin()) {
            return response()->json(['message' => 'Akses Terbatas: Khusus Owner!'], 403);
        }

        return $next($request);
    }
}
