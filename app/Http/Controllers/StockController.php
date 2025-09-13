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
        $stocks = Stock::with('product')->get();

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
        ]);

        $stock = Stock::where('product_id', $request->product_id)->first();

        if ($stock) {
            $stock->update([
                'quantity' => $stock->quantity + $request->quantity
            ]);
        } else {
            Stock::create([
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity
            ]);
        }

        return redirect()->route('stock.index')
            ->with('success', 'Stok berhasil disesuaikan');
    }
}
