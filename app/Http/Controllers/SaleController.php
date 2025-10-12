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
        \Log::info('Sale Store Request:', $request->all());

        $request->validate([
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.subtotal' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|in:product,service',
            'items.*.product_id' => 'nullable|required_if:items.*.item_type,product|exists:products,id',
            'items.*.service_name' => 'nullable|required_if:items.*.item_type,service|string|max:255',
            'items.*.service_description' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'change' => 'required|numeric',
        ]);

        // Validasi pembayaran
        if ($request->paid < $request->total) {
            return back()->withErrors([
                'paid' => 'Jumlah pembayaran harus lebih besar atau sama dengan total transaksi.'
            ]);
        }

        DB::beginTransaction();
        try {
            // Dapatkan store_id dari user
            $store = Store::whereHas('users', function($q) {
                $q->where('users.id', auth()->id());
            })->first();

            if (!$store) {
                throw new \Exception('Toko tidak ditemukan.');
            }

            // Buat transaksi penjualan
            $sale = Sale::create([
                'sale_date' => $request->sale_date,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount,
                'total' => $request->total,
                'paid' => $request->paid,
                'change' => $request->change,
                'user_id' => auth()->id(),
                'store_id' => $store->id,
            ]);

            // Proses setiap item
            foreach ($request->items as $item) {
                $saleItemData = [
                    'sale_id' => $sale->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'item_type' => $item['item_type'],
                ];

                if ($item['item_type'] === 'product') {
                    $saleItemData['product_id'] = $item['product_id'];
                    $saleItemData['service_name'] = null;
                    $saleItemData['service_description'] = null;
                    
                    // Update stok produk
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        Stock::create([
                            'product_id' => $product->id,
                            'type' => 'out',
                            'quantity' => $item['quantity'],
                            'user_id' => auth()->id(),
                            'reference' => 'sale_' . $sale->id,
                            'note' => 'Penjualan #' . $sale->id,
                        ]);
                    }
                } else {
                    $saleItemData['product_id'] = null;
                    $saleItemData['service_name'] = $item['service_name'];
                    $saleItemData['service_description'] = $item['service_description'] ?? null;
                }

                SaleItem::create($saleItemData);
            }

            DB::commit();

            // ✅ FIX: Return Inertia response dengan redirect
            return redirect()->route('sales.index')->with([
                'success' => 'Transaksi berhasil disimpan!',
                'sale_id' => $sale->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Sale Store Error: ' . $e->getMessage());

            // ✅ FIX: Return Inertia response dengan error
            return back()->withErrors([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
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
                if ($item->product_id) {
                    Stock::create([
                        'product_id' => $item->product_id,
                        'user_id'    => auth()->id(),
                        'type' => 'in', // Stok masuk karena retur
                        'quantity' => $item->quantity,
                        'reference' => 'RETURN-' . $sale->sale_code,
                        'note' => 'Retur penjualan ' . $sale->sale_code . ' - ' . ($request->reason ?? 'Tidak ada alasan'),
                    ]);
                }
            }

            DB::commit();

            // ✅ FIX: Return Inertia response untuk retur juga
            return redirect()->route('sales.index')->with([
                'success' => 'Retur penjualan berhasil. Stok telah dikembalikan.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // ✅ FIX: Return Inertia response dengan error
            return back()->withErrors([
                'error' => 'Gagal melakukan retur: ' . $e->getMessage()
            ]);
        }
    }
}