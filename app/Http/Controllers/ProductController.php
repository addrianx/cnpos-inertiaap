<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    // 📌 Tampilkan daftar produk
    public function index()
    {
        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum mengelola produk.');
        }

        $storeId = $store->id;

        $products = Product::with(['stocks', 'category'])
            ->where('store_id', $storeId)
            ->get();

        $categories = Category::all();

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => $categories,
        ]);
    }



    // 📌 Form tambah produk
    public function create()
    {
        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum mengelola produk.');
        }

        $storeId = $store->id;

        // Ambil semua kategori
        $categories = Category::all();

        return Inertia::render('Products/Create', [
            'categories' => $categories,
        ]);
    }


    // 📌 Simpan produk baru
    public function store(Request $request)
    {
        $request->validate([
            'sku'         => 'required|unique:products',
            'name'        => 'required',
            'cost'        => 'required|numeric',
            'price'       => 'required|numeric',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'category_id' => 'required|exists:categories,id', // tambahkan validasi kategori
        ]);

        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('products.index')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum menambahkan produk.');
        }

        Product::create([
            'sku'         => $request->sku,
            'name'        => $request->name,
            'cost'        => $request->cost,
            'price'       => $request->price,
            'discount'    => $request->discount ?? 0,
            'store_id'    => $store->id,
            'category_id' => $request->category_id, // simpan kategori
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }


    // 📌 Form edit produk
    public function edit(Product $product)
    {
        $categories = Category::all();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    // 📌 Update produk
    public function update(Request $request, Product $product)
    {

        $request->validate([
            'sku'         => 'required|unique:products,sku,' . $product->id,
            'name'        => 'required',
            'cost'        => 'required|numeric',
            'price'       => 'required|numeric',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'category_id' => 'required|exists:categories,id', // tambahkan validasi kategori
        ]);

        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('products.index')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum memperbarui produk.');
        }

        $product->update([
            'sku'         => $request->sku,
            'name'        => $request->name,
            'cost'        => $request->cost,
            'price'       => $request->price,
            'discount'    => $request->discount ?? 0,
            'store_id'    => $store->id,
            'category_id' => $request->category_id, // update kategori
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui');
        
    }


    // 📌 Hapus produk
    public function destroy(Product $product)
    {
        // Ambil semua user toko
        $storeUsers = $product->store->users;

        // Cek apakah user login termasuk di store ini
        if (!$storeUsers->contains(auth()->id())) {
            abort(403, 'Unauthorized');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }


    // ProductController.php
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'Data tidak valid'], 422);
        }

        Product::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

}
