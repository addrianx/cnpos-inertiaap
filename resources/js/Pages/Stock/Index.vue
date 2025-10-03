<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="d-flex align-items-center gap-2">
        Daftar Stok
        <button 
          @click="showInfo" 
          class="btn btn-sm btn-outline-info p-1 d-flex align-items-center justify-content-center"
          style="width: 24px; height: 24px; border-radius: 50%;"
          title="Klik untuk info"
        >
          <i class="fas fa-info" style="font-size: 12px;"></i>
        </button>
      </h2>
      <Link href="/stock/adjust" class="btn btn-primary">Tambah Stok</Link>
    </div>

    <!-- 🔍 Search & Filter -->
    <div class="row mb-3 g-2">
      <div class="col-12 col-md-6">
        <input
          v-model="searchInput"
          type="text"
          class="form-control"
          placeholder="Cari produk, catatan, atau keterangan..."
        />
      </div>
      <div class="col-12 col-md-6">
        <select v-model="filterType" class="form-select">
          <option value="">Semua Tipe</option>
          <option value="in">Stok Masuk</option>
          <option value="out">Stok Keluar</option>
          <option value="adjustment">Adjustment</option>
        </select>
      </div>
    </div>

    <div class="table-responsive position-relative">
      <!-- 🔄 Loader overlay -->
      <div
        v-if="loading"
        class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75"
        style="z-index:10"
      >
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <table class="table table-bordered table-striped table-nowrap">
        <thead class="table-dark">
          <tr>
            <th class="text-nowrap">#</th>
            <th class="text-nowrap">Produk</th>
            <th class="text-nowrap">Jumlah</th>
            <th class="text-nowrap">Catatan</th>
            <th class="text-nowrap">Keterangan</th>
            <th class="text-nowrap">Diubah Oleh</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!loading && paginatedStocks.length === 0">
            <td colspan="6" class="text-center text-muted py-4">
              Tidak ada data stok.
            </td>
          </tr>

          <tr v-else v-for="(s, i) in paginatedStocks" :key="s.id">
            <td class="text-nowrap">{{ (currentPage - 1) * perPage + i + 1 }}</td>
            <td class="text-nowrap">{{ s.product?.name || 'Produk tidak ditemukan' }}</td>
            <td class="text-nowrap">
              <span :class="getQuantityClass(s)">
                {{ formatQuantity(s) }}
              </span>
            </td>
            <td class="text-nowrap">{{ s.note || '-' }}</td>
            <td class="text-nowrap">
              <span class="badge" :class="getTypeBadgeClass(s.type)">
                {{ getTypeLabel(s.type) }}
              </span>
            </td>
            <td class="text-nowrap">{{ s.user ? s.user.name : 'System' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 🔽 Pagination Sederhana -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="prevPage">&laquo;</button>
        </li>
        
        <li
          v-for="page in visiblePages"
          :key="page"
          :class="['page-item', { active: currentPage === page }]"
        >
          <button class="page-link" @click="goToPage(page)">
            {{ page }}
          </button>
        </li>
        
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="nextPage">&raquo;</button>
        </li>
      </ul>
    </nav>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({ 
  stocks: {
    type: Array,
    default: () => []
  }
})

// state
const currentPage = ref(1)
const perPage = ref(20) // Fixed 20 items per page
const searchInput = ref('')
const search = ref('')
const filterType = ref('')
const loading = ref(false)

// Fungsi untuk menampilkan informasi tentang sistem stok terpisah
const showInfo = () => {
  Swal.fire({
    title: '📊 Keunggulan Sistem Stok Terpisah',
    html: `
      <div class="text-start">
        <p><strong>Sistem Stok Terpisah</strong> memisahkan data stok dari produk untuk tracking yang lebih detail dan akurat.</p>
        
        <div class="mt-3">
          <h6>🔄 <strong>Perbandingan dengan Sistem Tradisional:</strong></h6>
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Stok Terpisah</th>
                <th>Stok di Produk</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>✅ <strong>Audit Trail Lengkap</strong><br><small>Setiap perubahan tercatat</small></td>
                <td>❌ <strong>Hanya total</strong><br><small>Tidak tahu riwayat</small></td>
              </tr>
              <tr>
                <td>✅ <strong>Multiple Entry</strong><br><small>Stok masuk/keluar terpisah</small></td>
                <td>❌ <strong>Single Value</strong><br><small>Hanya angka akhir</small></td>
              </tr>
              <tr>
                <td>✅ <strong>Tracking User</strong><br><small>Tahu siapa yang ubah</small></td>
                <td>❌ <strong>Anonim</strong><br><small>Tidak ada accountability</small></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `,
    icon: 'info',
    confirmButtonText: 'Mengerti',
    confirmButtonColor: '#3085d6',
    width: '700px'
  })
}

// Helper functions
const getTypeBadgeClass = (type) => {
  const classes = {
    'in': 'bg-success',
    'out': 'bg-danger',
    'adjustment': 'bg-warning text-dark'
  }
  return classes[type] || 'bg-secondary'
}

const getTypeLabel = (type) => {
  const labels = {
    'in': 'Masuk',
    'out': 'Keluar',
    'adjustment': 'Adjust'
  }
  return labels[type] || type
}

const formatQuantity = (stock) => {
  if (stock.type === 'out') {
    return `-${Math.abs(stock.quantity)}`
  } else {
    return `+${stock.quantity}`
  }
}

const getQuantityClass = (stock) => {
  if (stock.type === 'out') {
    return 'text-danger fw-bold'
  } else if (stock.type === 'in') {
    return 'text-success fw-bold'
  } else {
    return 'text-warning fw-bold'
  }
}

// debounce search
let timeout
watch(searchInput, (val) => {
  loading.value = true
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    search.value = val
    currentPage.value = 1
    loading.value = false
  }, 300)
})

// filter data
const filteredStocks = computed(() => {
  let filtered = props.stocks || []
  
  // Filter by search
  if (search.value) {
    filtered = filtered.filter(s => {
      const productName = s.product?.name || ''
      const note = s.note || ''
      const type = s.type || ''
      
      return productName.toLowerCase().includes(search.value.toLowerCase()) ||
             note.toLowerCase().includes(search.value.toLowerCase()) ||
             type.toLowerCase().includes(search.value.toLowerCase())
    })
  }
  
  // Filter by type
  if (filterType.value) {
    filtered = filtered.filter(s => s.type === filterType.value)
  }
  
  return filtered
})

// Pagination computed properties
const totalPages = computed(() => Math.max(1, Math.ceil(filteredStocks.value.length / perPage.value)))

const paginatedStocks = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredStocks.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5 // Jumlah halaman yang ditampilkan
  
  if (totalPages.value <= maxVisible) {
    // Tampilkan semua halaman jika total halaman <= maxVisible
    for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  } else {
    // Tampilkan halaman dengan ellipsis di tengah
    let start = Math.max(1, currentPage.value - 2)
    let end = Math.min(totalPages.value, start + maxVisible - 1)
    
    // Adjust start jika mendekati akhir
    if (end === totalPages.value) {
      start = totalPages.value - maxVisible + 1
    }
    
    for (let i = start; i <= end; i++) pages.push(i)
  }
  
  return pages
})

// Navigation
const goToPage = (page) => { 
  if (page >= 1 && page <= totalPages.value) currentPage.value = page 
}
const nextPage = () => goToPage(currentPage.value + 1)
const prevPage = () => goToPage(currentPage.value - 1)

// Reset page when filter changes
watch([filterType], () => { 
  currentPage.value = 1 
})
</script>

<style scoped>
.badge {
  font-size: 0.75rem;
  padding: 0.35em 0.65em;
}

.table-nowrap {
  white-space: nowrap;
  min-width: 800px;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Optimasi untuk mobile */
@media (max-width: 768px) {
  .table-nowrap {
    min-width: 1000px;
    font-size: 0.875rem;
  }
  
  .btn {
    font-size: 0.875rem;
  }
  
  .badge {
    font-size: 0.7rem;
  }
}

.text-nowrap {
  white-space: nowrap;
}

.table-dark th {
  position: sticky;
  top: 0;
  background: #343a40;
  z-index: 10;
}
</style>