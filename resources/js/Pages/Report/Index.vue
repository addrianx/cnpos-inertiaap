<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>LAPORAN STOK PRODUK</h2>
          <button class="btn btn-secondary mb-3" @click="togglePrintMode">
            Print
          </button>
    </div>

    <!-- Tabel laporan stok & modal -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered table-striped align-middle text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>Kode</th>
            <th>Barang</th>
            <th>Modal</th>
            <th>Stok</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in displayedProducts" :key="product.id">
            <td>{{ product.sku }}</td>
            <td>{{ product.name }}</td>
            <td>{{ Number(product.cost).toLocaleString() }}</td>
            <td>{{ getTotalStock(product) }}</td>
            <td>{{ (product.cost * getTotalStock(product)).toLocaleString() }}</td>
          </tr>
        </tbody>

        <tfoot>
          <tr class="fw-bold">
            <td colspan="4" class="text-end">Total Modal Semua Produk</td>
            <td>{{ totalModal.toLocaleString() }}</td>
          </tr>
        </tfoot>
      </table>

      <nav v-if="!isPrintMode">
        <ul class="pagination justify-content-center">

        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">«</a>
        </li>

        <li
          v-for="(page, idx) in pagesToShow"
          :key="idx"
          class="page-item"
          :class="{ active: page === currentPage, disabled: page === '...' }"
        >
          <span v-if="page === '...'" class="page-link">…</span>
          <a
            v-else
            class="page-link"
            href="#"
            @click.prevent="goToPage(page)"
          >{{ page }}</a>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">»</a>
        </li>

        </ul>
      </nav>


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
import { defineProps, computed, ref, nextTick } from 'vue'

const props = defineProps({
  products: Array
})

// Pagination state
const currentPage = ref(1)
const perPage = ref(20)
const isPrintMode = ref(false) // default: aplikasi mode biasa

// Hitung total halaman (paling kecil 1)
const totalPages = computed(() => {
  return Math.max(1, Math.ceil((props.products?.length || 0) / perPage.value))
})

// Data yang ditampilkan: paginasi saat normal, semua saat print
const displayedProducts = computed(() => {
  if (isPrintMode.value) {
    return props.products || []
  }
  const start = (currentPage.value - 1) * perPage.value
  return (props.products || []).slice(start, start + perPage.value)
})

// Truncated pages (optional, tidak dipakai di template saat ini,
// tapi berguna kalau ingin tampilkan beberapa nomor di pagination)
const pagesToShow = computed(() => {
  const tp = totalPages.value
  const cp = currentPage.value
  const pages = []

  if (tp <= 7) {
    // kalau total halaman sedikit, tampilkan semua
    for (let i = 1; i <= tp; i++) {
      pages.push(i)
    }
  } else {
    pages.push(1) // halaman pertama selalu ada

    if (cp > 3) {
      pages.push('...') // ellipsis sebelum currentPage
    }

    // halaman sekitar currentPage (max 2 kiri, 2 kanan)
    for (let i = cp - 2; i <= cp + 2; i++) {
      if (i > 1 && i < tp) {
        pages.push(i)
      }
    }

    if (cp < tp - 2) {
      pages.push('...') // ellipsis setelah currentPage
    }

    pages.push(tp) // halaman terakhir selalu ada
  }

  return pages
})


// fungsi ganti page (dipakai di template)
function goToPage(page) {
  if (!page || isNaN(page)) return
  const p = Math.min(Math.max(1, Number(page)), totalPages.value)
  currentPage.value = p
}

// Fungsi print: tunggu render ulang DOM dulu agar semua baris tampil
async function togglePrintMode() {
  // set ke print mode -> tampilkan semua produk
  isPrintMode.value = true

  // tunggu Vue render DOM (agar displayedProducts = semua produk)
  await nextTick()

  // panggil print (browser akan memproses page-break dsb)
  window.print()

  // kembalikan ke mode normal
  // (setelah dialog print ditutup, code ini akan dieksekusi)
  isPrintMode.value = false
}

// ================== FUNGSI PENDUKUNG ==================

// Hitung total stok tersisa
function getTotalStock(product) {
  return (product.stocks || []).reduce((total, s) => {
    if (s.type === 'in') return total + s.quantity
    if (s.type === 'out') return total - s.quantity
    if (s.type === 'adjustment') return total + s.quantity
    return total
  }, 0)
}

// Hitung total keluar
function getTotalOut(product) {
  return (product.stocks || [])
    .filter(s => s.type === 'out')
    .reduce((sum, s) => sum + s.quantity, 0)
}

// ---- Informasi Keuangan ----
const totalModal = computed(() => {
  return (props.products || []).reduce((sum, product) => {
    return sum + getTotalStock(product) * (product.cost || 0)
  }, 0)
})

const totalOmzet = computed(() => {
  return (props.products || []).reduce((sum, product) => {
    return sum + getTotalStock(product) * (product.price || 0)
  }, 0)
})

const totalLaba = computed(() => {
  return totalOmzet.value - totalModal.value
})

const avgMargin = computed(() => {
  let marginSum = 0
  let count = 0
  ;(props.products || []).forEach(product => {
    if (product.price > 0 && product.cost > 0) {
      marginSum += ((product.price - product.cost) / product.cost) * 100
      count++
    }
  })
  return count > 0 ? marginSum / count : 0
})

// ================== ANALISA STOK ==================

function getTotalOutLast30Days(product) {
  const cutoff = new Date()
  cutoff.setDate(cutoff.getDate() - 30)
  return (product.stocks || [])
    .filter(s => s.type === 'out' && new Date(s.created_at) >= cutoff)
    .reduce((sum, s) => sum + s.quantity, 0)
}

function getTotalOutLast60Days(product) {
  const cutoff = new Date()
  cutoff.setDate(cutoff.getDate() - 60)
  return (product.stocks || [])
    .filter(s => s.type === 'out' && new Date(s.created_at) >= cutoff)
    .reduce((sum, s) => sum + s.quantity, 0)
}

const topFastMoving = computed(() => {
  return (props.products || [])
    .map(p => ({ ...p, totalOut: getTotalOutLast30Days(p) }))
    .filter(p => p.totalOut > 3)
    .sort((a, b) => b.totalOut - a.totalOut)
    .slice(0, 10)
})

const topSlowMoving = computed(() => {
  return (props.products || [])
    .map(p => ({ ...p, totalOut: getTotalOutLast60Days(p) }))
    .filter(p => p.totalOut > 0 && p.totalOut <= 3)
    .sort((a, b) => a.totalOut - b.totalOut)
})

const deadStocks = computed(() => {
  return (props.products || [])
    .map(p => ({ ...p, totalOut: getTotalOut(p), totalStock: getTotalStock(p) }))
    .filter(p => p.totalStock > 0 && p.totalOut === 0)
})
</script>



<style>
@media print {
  /* Hilangkan tombol, navigasi, card tambahan */
  button, .pagination, .card, .d-flex.justify-content-between {
    display: none !important;
  }

  /* Supaya tabel penuh halaman */
  table {
    page-break-inside: auto;
  }
  tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }
  thead {
    display: table-header-group; /* header ulang di setiap halaman */
  }
  tfoot {
    display: table-footer-group; /* footer ulang di setiap halaman */
  }
}
</style>
