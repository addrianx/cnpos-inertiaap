<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    // Tampilkan halaman buat penjualan
    public function create()
    {
        $products = Product::all(); // ambil daftar produk
        return Inertia::render('Sale/Create', [
            'products' => $products,
        ]);
    }

    // Simpan transaksi penjualan
    public function store(Request $request)
    {
        $request->validate([
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric',
            'items.*.subtotal'   => 'required|numeric',
            'subtotal' => 'required|numeric',
            'discount' => 'required|numeric',
            'total'    => 'required|numeric',
            'paid'     => 'required|numeric',
        ]);

        $sale = Sale::create([
            'user_id' => auth()->id(),
            'subtotal' => $request->subtotal,
            'discount' => $request->discount,
            'total'    => $request->total,
            'paid'     => $request->paid,
            'change'   => max($request->paid - $request->total, 0),
        ]);

        foreach ($request->items as $item) {
            $sale->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'discount'   => $item['discount'],
                'subtotal'   => $item['subtotal'],
            ]);
        }

        return redirect()->route('sales.index')->with('success', 'Transaksi berhasil disimpan');
    }


    // Daftar semua transaksi
    public function index()
    {
        $sales = Sale::with('items.product')->latest()->get();
        return Inertia::render('Sale/Index', [
            'sales' => $sales,
        ]);
    }
}
