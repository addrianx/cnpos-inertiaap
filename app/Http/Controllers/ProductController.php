<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    // 📌 Tampilkan daftar produk
    public function index()
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum mengelola produk.');
        }

        // ✅ ambil produk beserta stok & kategori
        $products = Product::with(['stocks', 'category'])
            ->where('store_id', $store->id)
            ->get();

        // ✅ ambil semua kategori (untuk filter/sortir di UI)
        $categories = Category::all();

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => $categories,
        ]);
    }


    // 📌 Form tambah produk
    public function create()
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum menambahkan produk.');
        }

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

        $store = auth()->user()->store;

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

        $store = auth()->user()->store;

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
        if ($product->store->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }
}
