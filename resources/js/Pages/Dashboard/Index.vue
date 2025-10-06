<template>
  <AppLayout>
    <!-- Header -->
    <div class="dashboard-header mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
      <p class="text-gray-600 mt-1">Ringkasan performa toko Anda</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
      <!-- Total Produk -->
      <div class="stats-card" @click="goToProducts">
        <div class="stats-icon bg-blue-50">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
        </div>
        <div class="stats-content">
          <h3 class="stats-title">Total Produk</h3>
          <p class="stats-value">{{ stats.products }}</p>
          <p class="stats-description">Produk aktif di sistem</p>
        </div>
        <div class="stats-arrow">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </div>

      <!-- Total Stok -->
      <div class="stats-card" @click="goToStock">
        <div class="stats-icon bg-green-50">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
        </div>
        <div class="stats-content">
          <h3 class="stats-title">Total Stok</h3>
          <p class="stats-value">{{ stats.stock }}</p>
          <p class="stats-description">Unit tersedia di gudang</p>
        </div>
        <div class="stats-arrow">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </div>

      <!-- Penjualan Hari Ini -->
      <div class="stats-card" @click="goToSales">
        <div class="stats-icon bg-amber-50">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
          </svg>
        </div>
        <div class="stats-content">
          <h3 class="stats-title">Penjualan Hari Ini</h3>
          <p class="stats-value">Rp {{ formatNumber(stats.sales_today) }}</p>
          <p class="stats-description">Total pendapatan hari ini</p>
        </div>
        <div class="stats-arrow">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Additional Stats Row (optional) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 mt-6">
      <!-- Total Kategori -->
      <div class="stats-card" @click="goToProducts">
        <div class="stats-icon bg-gray-50">
          <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
        </div>
        <div class="stats-content">
          <h3 class="stats-title">Total Kategori</h3>
          <p class="stats-value">{{ stats.categories || '0' }}</p>
          <p class="stats-description">Kategori produk aktif</p>
        </div>
        <div class="stats-arrow">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </div>

      <!-- Pelanggan -->
      <div class="stats-card" @click="goToCustomers">
        <div class="stats-icon bg-purple-50">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <div class="stats-content">
          <h3 class="stats-title">Total Pelanggan</h3>
          <p class="stats-value">{{ stats.customers || '0' }}</p>
          <p class="stats-description">Pelanggan terdaftar</p>
        </div>
        <div class="stats-arrow">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

defineProps({
  stats: Object
})

const formatNumber = (number) => {
  return new Intl.NumberFormat('id-ID').format(number)
}

// Navigation methods
const goToProducts = () => {
  router.get('/products')
}

const goToStock = () => {
  router.get('/stock')
}

const goToSales = () => {
  router.get('/sales')
}

const goToCustomers = () => {
  // Jika ada route khusus untuk customers, jika tidak arahkan ke users
  router.get('/customers')
}
</script>

<style scoped>
.dashboard-header {
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.stats-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.stats-card:hover {
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transform: translateY(-2px);
  border-color: #3b82f6;
}

.stats-card:active {
  transform: translateY(0);
}

.stats-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.stats-card:hover .stats-icon {
  transform: scale(1.05);
}

.stats-content {
  flex: 1;
  min-width: 0;
  transition: all 0.3s ease;
}

.stats-card:hover .stats-content {
  transform: translateX(8px);
}

.stats-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 0.25rem;
  line-height: 1.25;
  transition: color 0.3s ease;
}

.stats-card:hover .stats-title {
  color: #374151;
}

.stats-value {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  line-height: 1;
  margin-bottom: 0.25rem;
  transition: all 0.3s ease;
  display: inline-block;
}

.stats-card:hover .stats-value {
  color: #1f2937;
  transform: translateX(4px);
}

.stats-description {
  font-size: 0.75rem;
  color: #9ca3af;
  line-height: 1.25;
  transition: all 0.3s ease;
}

.stats-card:hover .stats-description {
  color: #6b7280;
  transform: translateX(2px);
}

.stats-arrow {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  opacity: 0;
  transform: translateX(-5px);
  transition: all 0.3s ease;
}

.stats-card:hover .stats-arrow {
  opacity: 1;
  transform: translateX(0);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
  }

  .dashboard-header h1 {
    font-size: 1.5rem;
  }

  .stats-card {
    padding: 1.25rem;
    gap: 0.875rem;
  }

  .stats-icon {
    width: 44px;
    height: 44px;
  }

  .stats-value {
    font-size: 1.5rem;
  }

  .stats-title {
    font-size: 0.8125rem;
  }

  .stats-description {
    font-size: 0.6875rem;
  }

  .stats-arrow {
    top: 1.25rem;
    right: 1.25rem;
  }

  .stats-card:hover .stats-content {
    transform: translateX(4px);
  }

  .stats-card:hover .stats-value {
    transform: translateX(2px);
  }

  .stats-card:hover .stats-description {
    transform: translateX(1px);
  }
}

@media (max-width: 640px) {
  .grid-cols-1 {
    grid-template-columns: 1fr;
  }
  
  .stats-card {
    margin-bottom: 0.5rem;
  }
  
  .stats-card:last-child {
    margin-bottom: 0;
  }
}

/* Touch device improvements */
@media (hover: none) {
  .stats-card {
    cursor: pointer;
  }
  
  .stats-arrow {
    opacity: 0.5;
    transform: translateX(0);
  }
  
  .stats-card:active .stats-content {
    transform: translateX(4px);
  }
  
  .stats-card:active .stats-value {
    transform: translateX(2px);
  }
  
  .stats-card:active .stats-description {
    transform: translateX(1px);
  }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
  .stats-card {
    background: #1f2937;
    border-color: #374151;
  }
  
  .stats-card:hover {
    border-color: #60a5fa;
  }
  
  .stats-value {
    color: #f9fafb;
  }
  
  .stats-card:hover .stats-value {
    color: #e5e7eb;
  }
  
  .stats-title {
    color: #d1d5db;
  }
  
  .stats-card:hover .stats-title {
    color: #e5e7eb;
  }
  
  .stats-description {
    color: #9ca3af;
  }
  
  .stats-card:hover .stats-description {
    color: #d1d5db;
  }
}
</style>