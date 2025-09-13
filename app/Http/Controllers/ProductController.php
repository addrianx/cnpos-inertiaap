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
        $products = Product::with('stocks')->get();

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

        Product::create([
            'sku'      => $request->sku,
            'name'     => $request->name,
            'cost'     => $request->cost,
            'price'    => $request->price,
            'discount' => $request->discount ?? 0,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan');
    }


    // 📌 Form edit produk
    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    // 📌 Update produk
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'sku'      => 'required|unique:products,sku,' . $product->id,
            'name'     => 'required',
            'cost'     => 'required|numeric',
            'price'    => 'required|numeric',
            'discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $product->update([
            'sku'      => $request->sku,
            'name'     => $request->name,
            'cost'     => $request->cost,
            'price'    => $request->price,
            'discount' => $request->discount ?? 0,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui');
    }


    // 📌 Hapus produk
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }
}
