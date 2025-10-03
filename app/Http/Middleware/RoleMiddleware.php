<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        
        if (!$user) {
            abort(403, 'Anda harus login untuk mengakses halaman ini.');
        }

        // Cek jika user memiliki salah satu role yang diizinkan
        $hasRole = $user->roles()->whereIn('name', $roles)->exists();
        
        if (!$hasRole) {
            $roleNames = implode(' atau ', $roles);
            abort(403, "Akses ditolak: Hanya {$roleNames} yang dapat mengakses halaman ini.");
        }

        return $next($request);
    }
}