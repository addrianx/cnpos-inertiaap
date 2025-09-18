<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Laporan Pembelian Stok Produk</h2>
      <button class="btn btn-primary" @click="printReport">Cetak</button>
    </div>

    <!-- Tabel laporan stok & modal -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered table-striped align-middle text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>Kode (SKU)</th>
            <th>Nama Produk</th>
            <th>Harga Modal (Rp)</th>
            <th>Total Stok</th>
            <th>Total Modal (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>{{ product.sku }}</td>
            <td>{{ product.name }}</td>
            <td>{{ Number(product.cost).toLocaleString() }}</td>
            <td>
              {{ getTotalStock(product) }}
            </td>
            <td>
              {{ (product.cost * getTotalStock(product)).toLocaleString() }}
            </td>
          </tr>
        </tbody>

        <tfoot>
          <tr class="fw-bold">
            <td colspan="4" class="text-end">Total Modal Semua Produk</td>
            <td>{{ totalModal.toLocaleString() }}</td>
          </tr>
        </tfoot>
        
      </table>
    </div>

    <!-- Informasi Keuangan Tambahan -->
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Informasi Keuangan</h5>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between">
            <span>Total Nilai Modal (Rp)</span>
            <strong>{{ totalModal.toLocaleString() }}</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Total Potensi Omzet (Rp)</span>
            <strong>{{ totalOmzet.toLocaleString() }}</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Total Potensi Laba Kotor (Rp)</span>
            <strong>{{ totalLaba.toLocaleString() }}</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Rata-rata Margin (%)</span>
            <strong>{{ avgMargin.toFixed(2) }}%</strong>
          </li>
        </ul>
      </div>
    </div>

<!-- Informasi Pergerakan Barang -->
<div class="card shadow-sm mt-4">
  <div class="card-header bg-secondary text-white">
    <h5 class="mb-0">Informasi Pergerakan Barang</h5>
  </div>
  <div class="card-body p-0">
    <table class="table table-bordered mb-0 align-middle text-nowrap">
      <thead class="table-light">
        <tr>
          <th style="width: 30%">Kategori</th>
          <th>Daftar Produk</th>
        </tr>
      </thead>
      <tbody>
        <!-- Fast Moving -->
        <tr>
          <td><strong>Barang Paling Laku</strong></td>
          <td>
            <ol class="mb-0 ps-3">
              <li v-for="item in topFastMoving" :key="item.id">
                {{ item.name }} ({{ item.totalOut }} terjual)
              </li>
              <li v-if="!topFastMoving.length">-</li>
            </ol>
          </td>
        </tr>

        <!-- Slow Moving -->
        <tr>
          <td><strong>Barang Jarang Terjual</strong></td>
          <td>
            <ol class="mb-0 ps-3">
              <li v-for="item in topSlowMoving" :key="item.id">
                {{ item.name }} ({{ item.totalOut }} terjual)
              </li>
              <li v-if="!topSlowMoving.length">-</li>
            </ol>
          </td>
        </tr>

        <!-- Dead Stock -->
        <tr>
          <td><strong>Barang Mati (Dead Stock)</strong></td>
          <td>
            <ul class="mb-0 ps-3">
              <li v-for="item in deadStocks" :key="item.id">
                {{ item.name }} (stok {{ item.totalStock }})
              </li>
              <li v-if="!deadStocks.length">-</li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>


  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { defineProps, computed } from 'vue'

const props = defineProps({
  products: Array
})

// Hitung stok in/out
// Hitung total stok tersisa
function getTotalStock(product) {
  return product.stocks.reduce((total, s) => {
    if (s.type === 'in') return total + s.quantity
    if (s.type === 'out') return total - s.quantity
    if (s.type === 'adjustment') return total + s.quantity
    return total
  }, 0)
}

// Hitung total keluar (tanpa batas waktu)
function getTotalOut(product) {
  return product.stocks
    .filter(s => s.type === 'out')
    .reduce((sum, s) => sum + s.quantity, 0)
}

// ---- Informasi Keuangan ----
const totalModal = computed(() => {
  return props.products.reduce((sum, product) => {
    return sum + getTotalStock(product) * product.cost
  }, 0)
})

const totalOmzet = computed(() => {
  return props.products.reduce((sum, product) => {
    return sum + getTotalStock(product) * product.price
  }, 0)
})

const totalLaba = computed(() => {
  return totalOmzet.value - totalModal.value
})

const avgMargin = computed(() => {
  let marginSum = 0
  let count = 0
  props.products.forEach(product => {
    if (product.price > 0 && product.cost > 0) {
      marginSum += ((product.price - product.cost) / product.cost) * 100
      count++
    }
  })
  return count > 0 ? marginSum / count : 0
})

// Hitung total keluar dalam 30 hari terakhir
function getTotalOutLast30Days(product) {
  const cutoff = new Date()
  cutoff.setDate(cutoff.getDate() - 30)

  return product.stocks
    .filter(s => s.type === 'out' && new Date(s.created_at) >= cutoff)
    .reduce((sum, s) => sum + s.quantity, 0)
}

// Hitung total keluar dalam 60 hari terakhir
function getTotalOutLast60Days(product) {
  const cutoff = new Date()
  cutoff.setDate(cutoff.getDate() - 60)

  return product.stocks
    .filter(s => s.type === 'out' && new Date(s.created_at) >= cutoff)
    .reduce((sum, s) => sum + s.quantity, 0)
}

// Fast Moving: produk dengan penjualan > 3 dalam 30 hari terakhir
const topFastMoving = computed(() => {
  return props.products
    .map(p => ({
      ...p,
      totalOut: getTotalOutLast30Days(p)
    }))
    .filter(p => p.totalOut > 3)
    .sort((a, b) => b.totalOut - a.totalOut)
    .slice(0, 10)
})

// Slow Moving: produk dengan penjualan kecil (0 < qty ≤ 3) dalam 60 hari terakhir
const topSlowMoving = computed(() => {
  return props.products
    .map(p => ({
      ...p,
      totalOut: getTotalOutLast60Days(p)
    }))
    .filter(p => p.totalOut > 0 && p.totalOut <= 3)
    .sort((a, b) => a.totalOut - b.totalOut)
})

// Dead Stock: stok masih ada, tapi tidak ada penjualan sama sekali
const deadStocks = computed(() => {
  return props.products
    .map(p => ({
      ...p,
      totalOut: getTotalOut(p),
      totalStock: getTotalStock(p)
    }))
    .filter(p => p.totalStock > 0 && p.totalOut === 0)
})


// Cetak laporan
const printReport = () => {
  window.print()
}
</script>

