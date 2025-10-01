<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\PaymentValidationTrait; // ✅ import trait

class SaleController extends Controller
{
    use PaymentValidationTrait; // ✅ pakai trait

    /** 
     * Daftar semua transaksi milik store user login 
     */
    public function index()
    {
        $store = Store::where('user_id', auth()->id())->first();
        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum melihat daftar penjualan.');
        }

        $sales = Sale::with('items.product')
            ->where('store_id', $store->id)
            ->latest()
            ->get();

        return Inertia::render('Sale/Index', [
            'sales' => $sales,
        ]);
    }

    /** 
     * Halaman buat penjualan baru 
     */
    public function create()
    {
        $store = Store::where('user_id', auth()->id())->first();
        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum menambahkan penjualan.');
        }

        $products = Product::with('stocks')
            ->where('store_id', $store->id)
            ->get();

        if ($products->isEmpty()) {
            return redirect()->route('products.index')
                ->with('error', 'Belum ada produk di toko Anda, buat produk terlebih dahulu sebelum membuat penjualan.');
        }

        return Inertia::render('Sale/Create', [
            'products' => $products->map(fn($p) => $p->append('stock')),
        ]);
    }

    /** 
     * Simpan transaksi penjualan 
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'paid' => 'required|numeric',
        ]);

        $store = Store::where('user_id', auth()->id())->firstOrFail();

        DB::beginTransaction();
        try {
            $subtotal = 0;

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                $stokTersedia = $product->stock;
                if ($stokTersedia < $item['quantity']) {
                    return back()->withErrors([
                        'items.'.$loop->index.'.quantity' => "Stok {$product->name} tidak cukup. Sisa stok: {$stokTersedia}"
                    ])->withInput();
                }

                $price = $product->price;
                $lineSubtotal = ($price * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $lineSubtotal;
            }

            $discount = $request->discount ?? 0;
            $total = max($subtotal - $discount, 0);

            // ✅ Validasi dibayar >= total
            $request->validate([
                'paid' => 'gte:'.$total
            ], [
                'paid.gte' => 'Jumlah bayar tidak mencukupi, minimal Rp '.number_format($total,0,',','.')
            ]);

            $sale = Sale::create([
                'user_id' => auth()->id(),
                'store_id' => $store->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => $request->paid,
                'change' => max($request->paid - $total, 0),
            ]);

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                $price = $product->price;
                $lineSubtotal = ($price * $item['quantity']) - ($item['discount'] ?? 0);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $lineSubtotal,
                ]);

                Stock::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference' => $sale->sale_code,
                    'note' => 'Penjualan ' . $sale->sale_code,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('sales.index')
                ->with('success', 'Transaksi ' . $sale->sale_code . ' berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'msg' => $e->getMessage()
            ])->withInput();
        }
    }

}
