<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>LAPORAN STOK PRODUK</h2>
      <button class="btn btn-secondary mb-3" @click="togglePrintMode" v-if="products.length > 0">
        Print
      </button>
    </div>

    <!-- Pesan jika tidak ada toko -->
    <div v-if="!$page.props.store" class="alert alert-warning">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ $page.props.message || 'Anda belum memiliki toko. Silakan buat toko terlebih dahulu untuk melihat laporan.' }}
      <Link href="/stores/create" class="alert-link ms-1">Buat Toko</Link>
    </div>

    <!-- Pesan jika tidak ada produk -->
    <div v-else-if="products.length === 0" class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i>
      Belum ada produk di toko Anda. Silakan tambah produk terlebih dahulu.
    </div>

    <!-- Tabel laporan stok & modal -->
    <div v-else class="table-responsive mb-4">
      <table class="table table-bordered table-striped align-middle text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>Kode</th>
            <th>Barang</th>
            <th>Modal</th>
            <th>Harga Jual</th>
            <th>Stok</th>
            <th>Total Modal</th>
            <th>Terjual</th>
            <th>Pendapatan</th>
            <th>Profit</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in displayedProducts" :key="product.id">
            <td>{{ product.sku }}</td>
            <td>{{ product.name }}</td>
            <td>{{ Number(product.cost).toLocaleString() }}</td>
            <td>{{ Number(product.price).toLocaleString() }}</td>
            <td>{{ product.stock }}</td>
            <td>{{ (product.cost * product.stock).toLocaleString() }}</td>
            <td>{{ product.total_sold }}</td>
            <td>{{ Number(product.total_revenue).toLocaleString() }}</td>
            <td>{{ Number(product.profit).toLocaleString() }}</td>
          </tr>
        </tbody>

        <tfoot>
          <tr class="fw-bold table-secondary">
            <td colspan="5" class="text-end">Total</td>
            <td>{{ totalModal.toLocaleString() }}</td>
            <td>{{ totalSold }}</td>
            <td>{{ Number(totalRevenue).toLocaleString() }}</td>
            <td>{{ Number(totalProfit).toLocaleString() }}</td>
          </tr>
        </tfoot>
      </table>

      <nav v-if="!isPrintMode && products.length > perPage">
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
    <div v-if="products.length > 0" class="card shadow-sm">
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
            <strong>{{ totalPotentialRevenue.toLocaleString() }}</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Total Pendapatan Aktual (Rp)</span>
            <strong>{{ Number(totalRevenue).toLocaleString() }}</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Total Profit Aktual (Rp)</span>
            <strong>{{ Number(totalProfit).toLocaleString() }}</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Rata-rata Margin (%)</span>
            <strong>{{ avgMargin.toFixed(2) }}%</strong>
          </li>
        </ul>
      </div>
    </div>

    <!-- Informasi Pergerakan Barang -->
    <div v-if="products.length > 0" class="card shadow-sm mt-4">
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
                    {{ item.name }} ({{ item.total_sold }} terjual)
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
                    {{ item.name }} ({{ item.total_sold }} terjual)
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
                    {{ item.name }} (stok {{ item.stock }})
                  </li>
                  <li v-if="!deadStocks.length">-</li>
                </ul>
              </td>
            </tr>

            <!-- Low Stock -->
            <tr>
              <td><strong>Stok Rendah (< 10)</strong></td>
              <td>
                <ul class="mb-0 ps-3">
                  <li v-for="item in lowStocks" :key="item.id">
                    {{ item.name }} (stok {{ item.stock }})
                  </li>
                  <li v-if="!lowStocks.length">-</li>
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
import { Link } from '@inertiajs/vue3'
import { computed, ref, nextTick } from 'vue'

const props = defineProps({
  products: Array,
  store: Object,
  message: String
})

// Pagination state
const currentPage = ref(1)
const perPage = ref(20)
const isPrintMode = ref(false)

// Hitung total halaman
const totalPages = computed(() => {
  return Math.max(1, Math.ceil((props.products?.length || 0) / perPage.value))
})

// Data yang ditampilkan
const displayedProducts = computed(() => {
  if (isPrintMode.value) {
    return props.products || []
  }
  const start = (currentPage.value - 1) * perPage.value
  return (props.products || []).slice(start, start + perPage.value)
})

// Truncated pages untuk pagination
const pagesToShow = computed(() => {
  const tp = totalPages.value
  const cp = currentPage.value
  const pages = []

  if (tp <= 7) {
    for (let i = 1; i <= tp; i++) {
      pages.push(i)
    }
  } else {
    pages.push(1)

    if (cp > 3) {
      pages.push('...')
    }

    for (let i = cp - 2; i <= cp + 2; i++) {
      if (i > 1 && i < tp) {
        pages.push(i)
      }
    }

    if (cp < tp - 2) {
      pages.push('...')
    }

    pages.push(tp)
  }

  return pages
})

// Fungsi navigasi
function goToPage(page) {
  if (!page || isNaN(page)) return
  const p = Math.min(Math.max(1, Number(page)), totalPages.value)
  currentPage.value = p
}

// Fungsi print
async function togglePrintMode() {
  isPrintMode.value = true
  await nextTick()
  window.print()
  isPrintMode.value = false
}

// ================== INFORMASI KEUANGAN ==================

const totalModal = computed(() => {
  return (props.products || []).reduce((sum, product) => {
    return sum + (product.stock * (product.cost || 0))
  }, 0)
})

const totalPotentialRevenue = computed(() => {
  return (props.products || []).reduce((sum, product) => {
    return sum + (product.stock * (product.price || 0))
  }, 0)
})

const totalRevenue = computed(() => {
  return (props.products || []).reduce((sum, product) => {
    return sum + (product.total_revenue || 0)
  }, 0)
})

const totalProfit = computed(() => {
  return (props.products || []).reduce((sum, product) => {
    return sum + (product.profit || 0)
  }, 0)
})

const totalSold = computed(() => {
  return (props.products || []).reduce((sum, product) => {
    return sum + (product.total_sold || 0)
  }, 0)
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

// ================== ANALISA PERGERAKAN BARANG ==================

const topFastMoving = computed(() => {
  return (props.products || [])
    .filter(p => p.total_sold > 3)
    .sort((a, b) => b.total_sold - a.total_sold)
    .slice(0, 10)
})

const topSlowMoving = computed(() => {
  return (props.products || [])
    .filter(p => p.total_sold > 0 && p.total_sold <= 3)
    .sort((a, b) => a.total_sold - b.total_sold)
    .slice(0, 10)
})

const deadStocks = computed(() => {
  return (props.products || [])
    .filter(p => p.stock > 0 && p.total_sold === 0)
    .slice(0, 10)
})

const lowStocks = computed(() => {
  return (props.products || [])
    .filter(p => p.stock > 0 && p.stock < 10)
    .sort((a, b) => a.stock - b.stock)
    .slice(0, 10)
})
</script>

<style>
@media print {
  /* Hilangkan elemen yang tidak perlu saat print */
  button, .pagination, .card .card-header, .alert, .d-flex.justify-content-between {
    display: none !important;
  }

  /* Tampilkan informasi penting saat print */
  .card {
    border: 1px solid #000 !important;
    box-shadow: none !important;
  }

  .card-body {
    padding: 0 !important;
  }

  /* Supaya tabel penuh halaman */
  table {
    page-break-inside: auto;
    font-size: 12px;
  }
  
  tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }
  
  thead {
    display: table-header-group;
  }
  
  tfoot {
    display: table-footer-group;
  }

  /* Header untuk setiap halaman print */
  @page {
    margin: 1cm;
    @top-center {
      content: "LAPORAN STOK PRODUK - {{ $page.props.store?.name || 'CNPOS' }}";
      font-size: 14px;
      font-weight: bold;
    }
    @bottom-center {
      content: "Halaman " counter(page) " dari " counter(pages);
      font-size: 12px;
    }
  }
}

/* Styling untuk stok rendah */
.text-stock-low {
  color: #dc3545;
  font-weight: bold;
}

.text-stock-medium {
  color: #ffc107;
  font-weight: bold;
}

.text-stock-good {
  color: #198754;
}
</style>