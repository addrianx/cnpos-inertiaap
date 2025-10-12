<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        // Only admin can access
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Log untuk debugging
        \Log::info('Users index accessed', [
            'refresh_param' => $request->input('_refresh'),
            'timestamp' => $request->input('_t')
        ]);

        // Data selalu fresh dari database
        $users = User::with(['roles', 'stores'])
            ->latest()
            ->get();

        $roles = Role::all();
        $stores = Store::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'stores' => $stores,
            // ✅ Cache busting parameters
            'timestamp' => now()->timestamp,
            'refresh_id' => $request->input('_refresh', uniqid()),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::all();
        $stores = Store::all();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'stores' => $stores,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Assign role
        $user->roles()->attach($request->role_id);


        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // GUNAKAN fresh() ATAU find() UNTUK MEMASTIKAN DATA TERBARU
        $user = User::with(['roles', 'stores'])
            ->findOrFail($user->id); // Gunakan findOrFail untuk memastikan data fresh

        $roles = Role::all();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            // Tambahkan timestamp untuk force re-render
            'timestamp' => now()->timestamp,
        ]);
    }


    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);

                $user->roles()->sync([$request->role_id]);
            });

            // ✅ Tetap redirect ke INDEX untuk konsistensi
            return redirect()->route('users.index')
                ->with('success', 'User ' . $user->name . ' berhasil diperbarui');

        } catch (\Exception $e) {
            // Untuk request Axios, return JSON response
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    'errors' => []
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withErrors([]);
        }
    }

    

    /**
     * Suspend a user.
     */
    public function suspend(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Prevent suspending yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menonaktifkan akun sendiri.'
            ], 400);
        }

        $user->update([
            'is_active' => false,
            'suspended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditangguhkan',
            'user' => $user->fresh()
        ]);
    }

    public function activate(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $user->update([
            'is_active' => true,
            'suspended_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diaktifkan',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri.'
            ], 400);
        }

        $userName = $user->name;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User ' . $userName . ' berhasil dihapus'
        ]);
    }
}