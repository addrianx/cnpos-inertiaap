<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Route;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'role_id' => $request->user()->roles()->first()->id ?? null, // ✅ TAMBAHKAN INI
                        'role_name' => $request->user()->roles()->first()->name ?? null, // ✅ TAMBAHKAN INI
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            // ✅ ADD THIS: Title management
            'title' => fn () => $this->getPageTitle($request),
            'appName' => config('app.name', 'CNPOS'),
        ];
    }

    /**
     * Generate page title based on route name
     */
    private function getPageTitle(Request $request): string
    {
        $routeName = Route::currentRouteName();
        $defaultTitle = config('app.name', 'CNPOS');

        $titles = [
            // Dashboard
            'dashboard' => 'Dashboard',
            
            // Products
            'products.index' => 'Daftar Produk',
            'products.create' => 'Tambah Produk',
            'products.edit' => 'Edit Produk',
            'products.show' => 'Detail Produk',
            
            // Sales
            'sales.index' => 'Daftar Penjualan',
            'sales.create' => 'Buat Penjualan',
            'sales.show' => 'Detail Penjualan',
            
            // Stock
            'stock.index' => 'Manajemen Stok',
            'stock.adjust.form' => 'Penyesuaian Stok',
            
            // Stock Loan
            'stockloan.index' => 'Pinjaman Stok',
            'stockloan.create' => 'Ajukan Pinjaman Stok',
            
            // Stock Transfer
            'stock-transfers.index' => 'Transfer Stok',
            'stock-transfers.create' => 'Buat Transfer Stok',
            
            // Stores
            'stores.index' => 'Manajemen Toko',
            'stores.create' => 'Tambah Toko',
            'stores.edit' => 'Edit Toko',
            'stores.show' => 'Detail Toko',
            
            // Users
            'users.index' => 'Manajemen User',
            'users.create' => 'Tambah User',
            'users.edit' => 'Edit User',
            
            // Categories
            'categories.index' => 'Kategori Produk',
            'categories.create' => 'Tambah Kategori',
            'categories.edit' => 'Edit Kategori',
            
            // Reports
            'reports.index' => 'Laporan',
            
            // Profile
            'profile.edit' => 'Edit Profil',
            
            // Register
            'register' => 'Daftar User Baru',
            
            // Stock Loan Actions
            'stock-loan.approve' => 'Setujui Pinjaman Stok',
            'stock-loan.reject' => 'Tolak Pinjaman Stok',
            'stock-loan.return' => 'Kembalikan Pinjaman Stok',
        ];

        return $titles[$routeName] ?? $defaultTitle;
    }
}