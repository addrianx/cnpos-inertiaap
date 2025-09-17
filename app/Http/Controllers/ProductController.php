<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    // 📌 Tampilkan daftar produk
    public function index()
    {
        $products = Product::with('stocks')
            ->whereHas('store', function ($query) {
                $query->where('user_id', auth()->id());
            })->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    // 📌 Form tambah produk
    public function create()
    {
        return Inertia::render('Products/Create');
    }

    // 📌 Simpan produk baru
    public function store(Request $request)
    {
        $request->validate([
            'sku'      => 'required|unique:products',
            'name'     => 'required',
            'cost'     => 'required|numeric',
            'price'    => 'required|numeric',
            'discount' => 'nullable|numeric|min:0|max:100',
        ]);

        // 🔥 cek apakah user punya store
       $store = auth()->user()->store()->first();
       //dd(auth()->user()->store);

        // $store = auth()->user()->stores()->first(); // kalau hasMany

        if (!$store || !$store->id) {
            return redirect()->route('products.index')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum menambahkan produk.');
        }

        Product::create([
            'sku'      => $request->sku,
            'name'     => $request->name,
            'cost'     => $request->cost,
            'price'    => $request->price,
            'discount' => $request->discount ?? 0,
            'store_id' => $store->id,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }


    // 📌 Form edit produk
    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'sku'      => 'required|unique:products,sku,' . $product->id,
            'name'     => 'required',
            'cost'     => 'required|numeric',
            'price'    => 'required|numeric',
            'discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('products.index')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum memperbarui produk.');
        }

        $product->update([
            'sku'      => $request->sku,
            'name'     => $request->name,
            'cost'     => $request->cost,
            'price'    => $request->price,
            'discount' => $request->discount ?? 0,
            'store_id' => $store->id,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui');
    }


    // 📌 Hapus produk
    public function destroy(Product $product)
    {
        if ($product->store->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }
}
