<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index()
    {
        // Ambil semua stok hanya untuk produk milik store user login
        $stocks = Stock::with('product')
            ->whereHas('product.store', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->latest()
            ->get();

        return Inertia::render('Stock/Index', [
            'stocks' => $stocks
        ]);
    }

    public function adjustForm()
    {
        // Ambil produk milik user login
        $products = Product::whereHas('store', function ($q) {
            $q->where('user_id', auth()->id());
        })->get();

        if ($products->isEmpty()) {
            return redirect()->route('products.create')
                ->with('error', 'Belum ada produk di toko Anda, buat produk terlebih dahulu sebelum mengatur stok.');
        }

        return Inertia::render('Stock/Adjust', [
            'products' => $products
        ]);
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer',
            'type'       => 'required|in:in,out,adjustment',
            'reference'  => 'nullable|string',
            'note'       => 'nullable|string',
        ]);

        // 🔒 Pastikan produk milik user login
        $product = Product::where('id', $request->product_id)
            ->whereHas('store', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->firstOrFail();

        // Simpan log stok
        Stock::create([
            'product_id' => $product->id,
            'type'       => $request->type,
            'quantity'   => $request->quantity,
            'reference'  => $request->reference,
            'note'       => $request->note,
        ]);

        return redirect()->route('stock.index')
            ->with('success', 'Stok berhasil ditambahkan');
    }
}
