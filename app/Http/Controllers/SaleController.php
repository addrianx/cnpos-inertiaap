<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\PaymentValidationTrait;

class SaleController extends Controller
{
    use PaymentValidationTrait;

    public function index()
    {
        $store = Store::whereHas('users', function($q) {
            $q->where('users.id', auth()->id());
        })->first();
        
        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum melihat daftar penjualan.');
        }

        $sales = Sale::with(['items.product', 'user'])
            ->where('store_id', $store->id)
            ->latest()
            ->get()
            ->map(function ($sale) {
                $sale->can_return = $sale->canBeReturned();
                $sale->remaining_return_time = $sale->getRemainingReturnTime();
                return $sale;
            });

        return Inertia::render('Sale/Index', [
            'sales' => $sales,
        ]);
    }

    public function create()
    {
        // ✅ FIX: Gunakan many-to-many
        $store = Store::whereHas('users', function($q) {
            $q->where('users.id', auth()->id());
        })->first();
        
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

        // ✅ FIX: Gunakan many-to-many
        $store = Store::whereHas('users', function($q) {
            $q->where('users.id', auth()->id());
        })->firstOrFail();

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

                // ✅ FIX: Tambahkan user_id
                Stock::create([
                    'product_id' => $product->id,
                    'user_id'    => auth()->id(),
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

    public function returnSale(Request $request, Sale $sale)
    {
        // Cek akses toko
        $store = Store::whereHas('users', function($q) {
            $q->where('users.id', auth()->id());
        })->first();

        if (!$store || $sale->store_id !== $store->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        // Validasi bisa di-retur
        if (!$sale->canBeReturned()) {
            return response()->json([
                'success' => false,
                'message' => 'Penjualan ini tidak dapat di-retur. Batas waktu 3 hari telah habis.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update status retur
            $sale->update([
                'is_returned' => true,
                'returned_at' => now(),
                'return_reason' => $request->reason,
                'returned_by' => auth()->id(),
            ]);

            // Kembalikan stok untuk setiap item
            foreach ($sale->items as $item) {
                Stock::create([
                    'product_id' => $item->product_id,
                    'user_id'    => auth()->id(),
                    'type' => 'in', // Stok masuk karena retur
                    'quantity' => $item->quantity,
                    'reference' => 'RETURN-' . $sale->sale_code,
                    'note' => 'Retur penjualan ' . $sale->sale_code . ' - ' . ($request->reason ?? 'Tidak ada alasan'),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur penjualan berhasil. Stok telah dikembalikan.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan retur: ' . $e->getMessage()
            ], 500);
        }
    }
}