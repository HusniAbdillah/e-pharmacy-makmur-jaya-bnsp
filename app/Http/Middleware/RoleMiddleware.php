<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Periksa kecocokan role pengguna
        if (!$user || !in_array($user->role->value, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        // Lanjutkan request jika cocok
        return $next($request);
    }
}
