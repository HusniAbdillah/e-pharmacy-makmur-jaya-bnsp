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

        if (!$user) {
            return redirect()->route('login');
        }

        $userRole = $user->role instanceof \App\Enums\RolePengguna ? $user->role->value : $user->role;

        // Periksa kecocokan role pengguna
        if (!in_array($userRole, $roles)) {
            // Jika role tidak cocok, kembalikan ke dashboard masing-masing sesuai role
            return match ($user->role) {
                \App\Enums\RolePengguna::Admin => redirect()->route("admin.dashboard"),
                \App\Enums\RolePengguna::Apoteker => redirect()->route("apoteker.dashboard"),
                \App\Enums\RolePengguna::Kasir => redirect()->route("kasir.pos"),
                default => redirect()->route("katalog"),
            };
        }

        // Lanjutkan request jika cocok
        return $next($request);
    }
}
