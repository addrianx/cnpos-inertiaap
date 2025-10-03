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
        $stores = Store::all();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'stores' => $stores,
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

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        $user->update($updateData);

        // Update role
        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    /**
     * Suspend a user.
     */
    public function suspend(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent suspending yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update([
            'is_active' => false,
            'suspended_at' => now(),
        ]);

        return back()->with('success', 'User berhasil ditangguhkan');
    }

    /**
     * Activate a user.
     */
    public function activate(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $user->update([
            'is_active' => true,
            'suspended_at' => null,
        ]);

        return back()->with('success', 'User berhasil diaktifkan');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus');
    }
}