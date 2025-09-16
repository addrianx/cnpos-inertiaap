<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    // Tampilkan halaman buat penjualan
    public function create()
    {
        // Ambil hanya produk milik store user yang login
        $store = Store::where('user_id', auth()->id())->firstOrFail();

        $products = Product::where('store_id', $store->id)->get();

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
            'items.*.discount'   => 'nullable|numeric',
            'discount'           => 'nullable|numeric',
            'paid'               => 'required|numeric',
        ]);

        $store = Store::where('user_id', auth()->id())->firstOrFail();

        DB::beginTransaction();
        try {
            $subtotal = 0;

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                $price = $product->price;
                $lineSubtotal = ($price * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $lineSubtotal;
            }

            $discount = $request->discount ?? 0;
            $total = max($subtotal - $discount, 0);

            $sale = Sale::create([
                'user_id'  => auth()->id(),
                'store_id' => $store->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total'    => $total,
                'paid'     => $request->paid,
                'change'   => max($request->paid - $total, 0),
            ]);

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                $price = $product->price;
                $lineSubtotal = ($price * $item['quantity']) - ($item['discount'] ?? 0);

                // Simpan item penjualan
                $saleItem = $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $price,
                    'discount'   => $item['discount'] ?? 0,
                    'subtotal'   => $lineSubtotal,
                ]);

                // 🔹 Simpan pergerakan stok (OUT)
                Stock::create([
                    'product_id' => $product->id,
                    'type'       => 'out',
                    'quantity'   => $item['quantity'],
                    'reference'  => 'SALE-' . $sale->id,
                    'note'       => 'Penjualan #' . $sale->id,
                ]);
            }

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Transaksi berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }

    // Daftar semua transaksi hanya untuk store user login
    public function index()
    {
        $store = Store::where('user_id', auth()->id())->firstOrFail();

        $sales = Sale::with('items.product')
            ->where('store_id', $store->id)
            ->latest()
            ->get();

        return Inertia::render('Sale/Index', [
            'sales' => $sales,
        ]);
    }
}
