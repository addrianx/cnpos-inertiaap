<?php

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
        
        // ✅ FIX: Gunakan many-to-many untuk mendapatkan store user
        $userStore = Store::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })->first();

        // ✅ FIX: Tampilkan hanya loan yang terkait dengan user
        $loans = StockLoan::with(['fromStore', 'toStore', 'items.product'])
            ->where(function($query) use ($userStore) {
                $query->where('from_store_id', $userStore->id)
                      ->orWhere('to_store_id', $userStore->id);
            })
            ->latest()
            ->get();

        return inertia('StockLoan/Index', [
            'loans' => $loans,
            'userStoreId' => $userStore ? $userStore->id : null,
        ]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        
        // ✅ FIX: Gunakan many-to-many
        $store = Store::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })->first();

        if (!$store) {
            return redirect()->back()->withErrors([
                'store' => 'User ini belum memiliki store atau tidak memiliki akses ke store manapun.'
            ]);
        }

        // ✅ FIX: Ambil daftar toko lain (yang tidak dimiliki user)
        $stores = Store::whereHas('users', function($q) use ($user) {
            $q->where('users.id', '!=', $user->id);
        })->get();

        $products = collect();

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
            'from_store_id' => $request->from_store_id,
        ]);
    }

    public function getProducts(Store $store)
    {
        // ✅ FIX: Authorization - cek apakah user punya akses ke store ini
        $user = auth()->user();
        $hasAccess = Store::where('id', $store->id)
            ->whereHas('users', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
        $data = $request->validate([
            'from_store_id'        => 'required|exists:stores,id',
            'loan_date'            => 'required|date',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        
        // ✅ FIX: Gunakan many-to-many
        $store = Store::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })->first();

        if (!$store) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'store' => 'User ini belum memiliki store, tidak bisa membuat peminjaman stok.'
                ]);
        }

        // ✅ FIX: Cek apakah from_store_id valid (user punya akses)
        $hasAccessToFromStore = Store::where('id', $data['from_store_id'])
            ->whereHas('users', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })->exists();

        if (!$hasAccessToFromStore) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'from_store_id' => 'Anda tidak memiliki akses ke toko sumber ini.'
                ]);
        }

        if ($data['from_store_id'] == $store->id) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'from_store_id' => 'Tidak bisa meminjam stok dari toko sendiri.'
                ]);
        }

        $loan = StockLoan::create([
            'from_store_id' => $data['from_store_id'],
            'to_store_id'   => $store->id,
            'loan_date'     => $data['loan_date'],
            'notes'         => $data['notes'] ?? null,
        ]);

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

        // ✅ FIX: Gunakan many-to-many untuk authorization
        $userStore = Store::whereHas('users', function($q) {
            $q->where('users.id', Auth::id());
        })->first();

        if (!$userStore || $loan->from_store_id !== $userStore->id) {
            abort(403, 'Anda tidak berhak approve pinjaman ini.');
        }

        // ... rest of approve logic remains the same
        foreach ($loan->items as $item) {
            $product = $item->product;

            $targetProduct = Product::where('sku', $product->sku)
                ->where('store_id', $loan->to_store_id)
                ->first();

            if (!$targetProduct) {
                $targetProduct = Product::create([
                    'sku'         => $product->sku,
                    'name'        => $product->name,
                    'cost'        => $product->cost,
                    'price'       => $product->price,
                    'discount'    => $product->discount,
                    'store_id'    => $loan->to_store_id,
                    'category_id' => $product->category_id,
                ]);
            }

            Stock::create([
                'product_id'  => $item->product_id,
                'user_id'     => Auth::id(), // ✅ Jangan lupa user_id
                'type'        => 'out',
                'quantity'    => $item->quantity,
                'reference'   => 'Stock Loan #' . $loan->id,
                'note'        => 'Dipinjam oleh ' . $loan->toStore->name,
                'source_type' => 'loan',
                'source_id'   => $loan->id,
            ]);

            Stock::create([
                'product_id'  => $targetProduct->id,
                'user_id'     => Auth::id(), // ✅ Jangan lupa user_id
                'type'        => 'in',
                'quantity'    => $item->quantity,
                'reference'   => 'Stock Loan #' . $loan->id,
                'note'        => 'Diterima dari ' . $loan->fromStore->name,
                'source_type' => 'loan',
                'source_id'   => $loan->id,
            ]);
        }

        $loan->status = 'approved';
        $loan->save();

        return redirect()->back()->with('success', 'Pinjaman stok berhasil diterima.');
    }

    public function reject($id)
    {
        $loan = StockLoan::findOrFail($id);

        // ✅ FIX: Gunakan many-to-many
        $userStore = Store::whereHas('users', function($q) {
            $q->where('users.id', Auth::id());
        })->first();

        if (!$userStore || $loan->from_store_id !== $userStore->id) {
            abort(403, 'Anda tidak berhak menolak pinjaman ini.');
        }

        $loan->status = 'rejected';
        $loan->save();

        return redirect()->back()->with('success', 'Pinjaman stok ditolak.');
    }

    public function returnLoan($id)
    {
        $loan = StockLoan::with('items.product')->findOrFail($id);

        // ✅ FIX: Gunakan many-to-many
        $userStore = Store::whereHas('users', function($q) {
            $q->where('users.id', Auth::id());
        })->first();

        if (!$userStore || $loan->to_store_id !== $userStore->id) {
            abort(403, 'Anda tidak berhak mengembalikan pinjaman ini.');
        }

        if ($loan->status === 'returned') {
            return back()->with('error', 'Pinjaman ini sudah dikembalikan sebelumnya.');
        }

        foreach ($loan->items as $item) {
            $borrowedProduct = Product::where('sku', $item->product->sku)
                ->where('store_id', $loan->to_store_id)
                ->first();

            $originalProduct = $item->product;

            if (!$borrowedProduct || !$originalProduct) {
                return back()->with('error', 'Produk tidak ditemukan untuk proses pengembalian.');
            }

            Stock::create([
                'product_id'  => $borrowedProduct->id,
                'user_id'     => Auth::id(), // ✅ Jangan lupa user_id
                'type'        => 'out',
                'quantity'    => $item->quantity,
                'reference'   => 'Return Loan #' . $loan->id,
                'note'        => 'Pengembalian ke ' . $loan->fromStore->name,
                'source_type' => 'return_out',
                'source_id'   => $loan->id,
            ]);

            Stock::create([
                'product_id'  => $originalProduct->id,
                'user_id'     => Auth::id(), // ✅ Jangan lupa user_id
                'type'        => 'in',
                'quantity'    => $item->quantity,
                'reference'   => 'Return Loan #' . $loan->id,
                'note'        => 'Pengembalian dari ' . $loan->toStore->name,
                'source_type' => 'return_in',
                'source_id'   => $loan->id,
            ]);
        }

        $loan->status = 'returned';
        $loan->save();

        return redirect()->back()->with('success', 'Pinjaman berhasil dikembalikan.');
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
        return redirect()->route('stock-loan.index')->with('success','Peminjaman stok berhasil dihapus');
    }
}
