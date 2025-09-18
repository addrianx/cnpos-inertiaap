<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role = null)
    {
        if (! $request->user() || ! $request->user()->roles()->where('name', $role)->exists()) {
            abort(403, 'Akses ditolak: khusus '.$role);
        }

        return $next($request);
    }
}
