<?php

// app/Http/Controllers/StockLoanController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\StockLoan;
use App\Models\StockLoanItem;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockLoanController extends Controller
{
    public function index() 
    {
        $user = auth()->user();
        $loans = StockLoan::with(['fromStore', 'toStore', 'items.product'])->latest()->get();
        $userStore = Store::where('user_id', $user->id)->first();

        return inertia('StockLoan/Index', [
            'loans' => $loans,
            'userStoreId' => $userStore ? $userStore->id : null, // kirim ke Inertia
        ]);
    }


    public function create(Request $request)
    {
        $user = auth()->user();
        $store = Store::where('user_id', $user->id)->first();

        if (! $store) {
            return redirect()->back()->withErrors([
                'store' => 'User ini belum memiliki store.'
            ]);
        }

        // Ambil daftar toko lain
        $stores = Store::where('id', '!=', $store->id)->get();

        $products = collect(); // default kosong

        // Jika user sudah pilih toko sumber
        if ($request->filled('from_store_id')) {
            $fromStoreId = $request->from_store_id;

            $products = Product::select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.store_id',
                DB::raw("
                    COALESCE(SUM(CASE WHEN stocks.type = 'in' THEN stocks.quantity END), 0)
                    - COALESCE(SUM(CASE WHEN stocks.type = 'out' THEN stocks.quantity END), 0)
                    as stock
                ")
            )
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->where('products.store_id', $fromStoreId)
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.store_id')
            ->get();
        }

        return inertia('StockLoan/Create', [
            'products' => $products,
            'stores'   => $stores,
            'my_store' => $store,
            'from_store_id' => $request->from_store_id, // kirim biar bisa prefill
        ]);
    }

public function getProducts(Store $store)
{
    $products = Product::select(
        'products.id',
        'products.name',
        'products.sku',
        'products.price',
        'products.store_id',
        DB::raw("
            COALESCE(SUM(CASE WHEN stocks.type = 'in' THEN stocks.quantity END), 0)
            - COALESCE(SUM(CASE WHEN stocks.type = 'out' THEN stocks.quantity END), 0)
            as stock
        ")
    )
    ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
    ->where('products.store_id', $store->id)
    ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.store_id')
    ->get();

    return response()->json($products);
}


    public function store(Request $request)
    {
        // ✅ Validasi input dari user
        $data = $request->validate([
            'from_store_id'        => 'required|exists:stores,id',
            'loan_date'            => 'required|date',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        // ✅ Ambil store milik user login
        $user = auth()->user();
        $store = Store::where('user_id', $user->id)->first();

        if (! $store) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'store' => 'User ini belum memiliki store, tidak bisa membuat peminjaman stok.'
                ]);
        }

        // 🚨 Cegah pinjam ke toko sendiri
        if ($data['from_store_id'] == $store->id) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'from_store_id' => 'Tidak bisa meminjam stok dari toko sendiri.'
                ]);
        }

        // ✅ Buat record pinjaman stok
        $loan = StockLoan::create([
            'from_store_id' => $data['from_store_id'],
            'to_store_id'   => $store->id, // isi otomatis, bukan dari input user
            'loan_date'     => $data['loan_date'],
            'notes'         => $data['notes'] ?? null,
        ]);

        // ✅ Simpan detail item pinjaman
        foreach ($data['items'] as $item) {
            StockLoanItem::create([
                'stock_loan_id' => $loan->id,
                'product_id'    => $item['product_id'],
                'quantity'      => $item['quantity'],
            ]);
        }

        return redirect()->route('stockloan.index')
            ->with('success', 'Peminjaman stok berhasil dibuat');
    }


    public function approve($id)
    {
        $loan = StockLoan::with('items.product')->findOrFail($id);

        // pastikan yang approve adalah store tujuan
        $userStore = Store::where('user_id', Auth::id())->first();
        if ($loan->to_store_id !== $userStore->id) {
            abort(403);
        }

        // kurangi stok di store pemberi
        foreach ($loan->items as $item) {
            Stock::create([
                'product_id' => $item->product_id,
                'store_id'   => $loan->from_store_id,
                'type'       => 'out',
                'quantity'   => $item->quantity,
                'reference'  => 'Stock Loan #' . $loan->id,
                'note'       => 'Pinjaman ke ' . $loan->toStore->name,
            ]);

            // tambahkan stok di store penerima
            Stock::create([
                'product_id' => $item->product_id,
                'store_id'   => $loan->to_store_id,
                'type'       => 'in',
                'quantity'   => $item->quantity,
                'reference'  => 'Stock Loan #' . $loan->id,
                'note'       => 'Diterima dari ' . $loan->fromStore->name,
            ]);
        }

        $loan->status = 'returned';
        $loan->save();

        return redirect()->back()->with('success', 'Pinjaman stok diterima.');
    }


    public function reject($id)
    {
        $loan = StockLoan::findOrFail($id);

        $userStore = Store::where('user_id', Auth::id())->first();
        if ($loan->to_store_id !== $userStore->id) {
            abort(403);
        }

        $loan->status = 'cancelled';
        $loan->save();

        return redirect()->back()->with('success', 'Pinjaman stok ditolak.');
    }


    public function show(StockLoan $stockLoan) {
        $stockLoan->load(['fromStore','toStore','items.product']);
        return inertia('StockLoan/Show', compact('stockLoan'));
    }


    public function edit(StockLoan $stockLoan) {
        $stockLoan->load('items');
        $stores = Store::all();
        $products = Product::all();
        return inertia('StockLoan/Edit', compact('stockLoan','stores','products'));
    }


    public function update(Request $request, StockLoan $stockLoan) {
        $data = $request->validate([
            'loan_date'     => 'required|date',
            'status'        => 'required|in:pending,returned,cancelled',
            'notes'         => 'nullable|string',
        ]);

        $stockLoan->update($data);

        return redirect()->route('stockloan.index')->with('success','Peminjaman stok berhasil diperbarui');
    }


    public function destroy(StockLoan $stockLoan) {
        $stockLoan->delete();
        return redirect()->route('stock-loans.index')->with('success','Peminjaman stok berhasil dihapus');
    }
}
