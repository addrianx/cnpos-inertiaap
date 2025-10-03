<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Tampilkan daftar toko
     */
    public function index()
    {
        $stores = Store::with(['users.roles'])->get();
        return Inertia::render('Store/Index', [
            'stores' => $stores,
        ]);
    }

    /**
     * Form tambah toko
     */
    public function create()
    {
        // Hanya ambil user yang belum memiliki toko
        $users = User::whereDoesntHave('stores')->get();
        
        return Inertia::render('Store/Create', [
            'users' => $users,
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
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Auto-set user_id dari user pertama yang dipilih atau user yang login
        $user_id = !empty($request->user_ids) ? $request->user_ids[0] : auth()->id();
        
        $store = Store::create([
            'name' => $request->name,
            'address' => $request->address,
            'user_id' => $user_id
        ]);
        
        if ($request->has('user_ids')) {
            $store->users()->sync($request->user_ids);
        }

        return redirect()->route('stores.index')->with('success', 'Toko berhasil ditambahkan!');
    }

    /**
     * Form edit toko
     */
    public function edit(Store $store)
    {
        $store->load(['users' => function($query) {
            $query->with('roles'); // Load roles relationship
        }]);
        
        // Ambil user yang belum memiliki toko ATAU sudah menjadi bagian dari toko ini
        $users = User::where(function($query) use ($store) {
            $query->whereDoesntHave('stores')
                ->orWhereHas('stores', function($q) use ($store) {
                    $q->where('stores.id', $store->id);
                });
        })
        ->with('roles') // ✅ LOAD RELATIONSHIP ROLES
        ->get();

        return Inertia::render('Store/Edit', [
            'store' => $store,
            'users' => $users,
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
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $store->update($request->only('name', 'address'));
        $store->users()->sync($request->user_ids ?? []);

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