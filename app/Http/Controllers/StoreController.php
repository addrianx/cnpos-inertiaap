<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Tampilkan daftar toko
     */
    public function index()
    {
        $stores = Store::with(['user.roles'])->get(); // load role manager
        return Inertia::render('Store/Index', [
            'stores' => $stores,
        ]);
    }

    /**
     * Form tambah toko
     */
    public function create()
    {
        $users = User::all();  // untuk pilih manager toko
        $roles = Role::all();  // untuk dropdown role saat tambah user baru
        return Inertia::render('Store/Create', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Simpan toko baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Store::create($request->only('name', 'address', 'user_id'));

        return redirect()->route('stores.index')->with('success', 'Toko berhasil ditambahkan!');
    }

    /**
     * Form edit toko
     */
    public function edit(Store $store)
    {
        $users = User::all();
        $roles = Role::all();
        return Inertia::render('Store/Edit', [
            'store' => $store,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Update toko
     */
    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $store->update($request->only('name', 'address', 'user_id'));

        return redirect()->route('stores.index')->with('success', 'Toko berhasil diperbarui!');
    }

    /**
     * Hapus toko
     */
    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Toko berhasil dihapus!');
    }
}
