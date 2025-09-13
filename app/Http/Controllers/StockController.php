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
        // Tampilkan semua log stok, join dengan product
        $stocks = Stock::with('product')
            ->latest()
            ->get();

        return Inertia::render('Stock/Index', [
            'stocks' => $stocks
        ]);
    }

    public function adjustForm()
    {
        $products = Product::all();

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

        // Simpan log stok baru, tidak menimpa stok lama
        Stock::create([
            'product_id' => $request->product_id,
            'type'       => $request->type,
            'quantity'   => $request->quantity,
            'reference'  => $request->reference,
            'note'       => $request->note,
        ]);

        return redirect()->route('stock.index')
            ->with('success', 'Stok berhasil ditambahkan');
    }
}
