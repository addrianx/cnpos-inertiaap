<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            // user tidak ditemukan, biarkan LoginRequest handle error
            return back()->withErrors(['email' => 'Email tidak ditemukan']);
        }

        if (!$user->password) {
            // Redirect ke halaman set password via Inertia
            return Inertia::render('Auth/SetPassword', [
                'userId' => $user->id,
                'email' => $user->email,
            ]);
        }

        // Jika password sudah ada, lanjutkan login biasa
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Cek jika request dari Inertia
        if ($request->inertia()) {
            return Inertia::location(route('login'));
        }

        // ✅ Untuk request non-Inertia (API, form submit biasa)
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logged out successfully',
                'redirect_url' => route('login')
            ]);
        }

        // ✅ Default redirect untuk form submission biasa
        return redirect()->route('login');
    }
}
