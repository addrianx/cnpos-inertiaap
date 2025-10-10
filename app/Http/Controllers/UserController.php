<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        // Only admin can access
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with(['roles', 'stores'])
            ->latest()
            ->get();

        $roles = Role::all();
        $stores = Store::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'stores' => $stores,
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

        $user->load(['roles', 'stores']);
        $roles = Role::all();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
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
            // Update user data
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update role
            $user->roles()->sync([$request->role_id]);

            // Untuk Inertia, gunakan redirect dengan session flash
            return redirect()->route('users.index')
                ->with('success', 'User berhasil diperbarui');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui user: ' . $e->getMessage());
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