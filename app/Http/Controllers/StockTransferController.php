<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    /**
     * 📌 Daftar semua transfer stok yang terkait dengan toko user login
     */
    public function index()
    {
        // ✅ FIX: Gunakan many-to-many
        $store = Store::whereHas('users', function($q) {
            $q->where('users.id', auth()->id());
        })->first();

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu.');
        }

        $transfers = StockTransfer::with(['product', 'fromStore', 'toStore'])
            ->where(function ($q) use ($store) {
                $q->where('from_store_id', $store->id)
                ->orWhere('to_store_id', $store->id);
            })
            ->latest()
            ->get();

        return Inertia::render('StockTransfers/Index', [
            'transfers' => $transfers
        ]);
    }

    /**
     * 📌 Form transfer stok
     */
    public function create()
    {
        // ✅ FIX: Gunakan many-to-many
        $store = Store::whereHas('users', function($q) {
            $q->where('users.id', auth()->id());
        })->first();

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu.');
        }

        // ✅ FIX: Ambil produk dari toko user (many-to-many compatible)
        $products = Product::where('store_id', $store->id)
            ->get()
            ->map(function ($p) {
                return [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'stock' => $p->stock, // Gunakan accessor stock, bukan current_stock
                ];
            });

        // ✅ FIX: Ambil toko lain yang bisa di-transfer
        $stores = Store::where('id', '!=', $store->id)
            ->whereHas('users') // Hanya toko yang punya user (aktif)
            ->get();

        return Inertia::render('StockTransfers/Create', [
            'products' => $products,
            'stores'   => $stores,
        ]);
    }

    /**
     * 📌 Proses transfer stok
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'to_store_id' => 'required|exists:stores,id',
            'quantity'    => 'required|integer|min:1',
            'reference'   => 'nullable|string',
            'note'        => 'nullable|string',
        ]);

        // ✅ FIX: Gunakan many-to-many untuk authorization
        $fromStore = Store::whereHas('users', function($q) {
            $q->where('users.id', auth()->id());
        })->firstOrFail();

        $toStore = Store::findOrFail($request->to_store_id);

        // ✅ Pastikan produk milik toko pengirim & user punya akses
        $product = Product::where('id', $request->product_id)
            ->where('store_id', $fromStore->id)
            ->firstOrFail();

        // ✅ Gunakan accessor stock dari model Product
        if ($request->quantity > $product->stock) {
            return back()->with('error', 'Stok tidak cukup untuk dikirim. Stok tersedia: ' . $product->stock);
        }

        DB::transaction(function () use ($product, $fromStore, $toStore, $request) {
            // 1️⃣ Kurangi stok di toko pengirim
            Stock::create([
                'product_id' => $product->id,
                'user_id'    => auth()->id(), // ✅ Jangan lupa user_id
                'type'       => 'out',
                'quantity'   => $request->quantity,
                'reference'  => $request->reference,
                'note'       => 'Transfer ke ' . $toStore->name . ' - ' . ($request->note ?? ''),
            ]);

            // 2️⃣ Tambah stok di toko penerima
            $toProduct = Product::firstOrCreate(
                ['sku' => $product->sku, 'store_id' => $toStore->id],
                [
                    'name'        => $product->name,
                    'cost'        => $product->cost,
                    'price'       => $product->price,
                    'discount'    => $product->discount,
                    'category_id' => $product->category_id,
                ]
            );

            Stock::create([
                'product_id' => $toProduct->id,
                'user_id'    => auth()->id(), // ✅ Jangan lupa user_id
                'type'       => 'in',
                'quantity'   => $request->quantity,
                'reference'  => $request->reference,
                'note'       => 'Transfer dari ' . $fromStore->name . ' - ' . ($request->note ?? ''),
            ]);

            // 3️⃣ Catat ke tabel stock_transfers
            StockTransfer::create([
                'product_id'    => $product->id,
                'from_store_id' => $fromStore->id,
                'to_store_id'   => $toStore->id,
                'quantity'      => $request->quantity,
                'reference'     => $request->reference,
                'note'          => $request->note,
                'type'          => 'transfer',
            ]);
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Transfer stok berhasil.');
    }
}