<?php
// app/Http/Controllers/PCAssemblyController.php

namespace App\Http\Controllers;

use App\Models\PCAssembly;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PCAssemblyController extends Controller
{
    // Halaman simulasi rakitan
    public function create()
    {
        // Ambil produk dengan kategori komponen PC dan stok > 0
        $components = Product::with(['category', 'stocks'])
            ->whereHas('category', function($query) {
                $query->whereIn('name', [
                    'Processor', 'Motherboard', 'RAM', 'VGA', 
                    'SSD', 'HDD', 'Power Supply', 'Casing', 'Cooler'
                ]);
            })
            ->whereHas('stocks', function($query) {
                $query->where('quantity', '>', 0);
            })
            ->get() // ✅ HAPUS SELECT, BIARKAN MODEL HANDLE APPENDS
            ->groupBy('category.name');

        return Inertia::render('PCAssembly/Create', [
            'components' => $components,
        ]);
    }

    // ... method store, history, show tetap sama

    // Simpan rakitan ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'components' => 'required|array',
            'total_price' => 'required|numeric|min:0',
        ]);

        // Cek ketersediaan stok untuk semua komponen
        foreach ($request->components as $component) {
            $product = Product::find($component['id']);
            $totalStock = $product->stocks->sum('quantity');
            
            if ($totalStock <= 0) {
                return back()->withErrors([
                    'components' => "Produk {$product->name} stok habis."
                ]);
            }
        }

        $assembly = PCAssembly::create([
            'name' => $request->name,
            'description' => $request->description,
            'components' => $request->components,
            'total_price' => $request->total_price,
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->stores->first()->id,
        ]);

        return redirect()->route('pc-assembly.history')
            ->with('success', 'Rakitan PC berhasil disimpan!');
    }

    // History rakitan
    public function history()
    {
        $assemblies = PCAssembly::with(['user', 'store'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('PCAssembly/History', [
            'assemblies' => $assemblies,
        ]);
    }

    // Detail rakitan
    public function show(PCAssembly $assembly)
    {
        if ($assembly->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('PCAssembly/Show', [
            'assembly' => $assembly,
        ]);
    }
}