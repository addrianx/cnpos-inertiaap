<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="d-flex align-items-center gap-2">
        Daftar Transfer Stok
        <button 
          @click="showInfo" 
          class="btn btn-sm btn-outline-info p-1 d-flex align-items-center justify-content-center"
          style="width: 24px; height: 24px; border-radius: 50%;"
          title="Klik untuk info"
        >
          <i class="fas fa-info" style="font-size: 12px;"></i>
        </button>
      </h2>
      <Link href="/stock-transfers/create" class="btn btn-primary">+ Transfer Stok</Link>
    </div>

    <!-- 🔍 Search -->
    <div class="row mb-3 g-2">
      <div class="col-12 col-md-6">
        <input
          v-model="search"
          type="text"
          class="form-control"
          placeholder="Cari produk, catatan, atau toko..."
        />
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Toko Pengirim</th>
            <th>Toko Penerima</th>
            <th>Catatan</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <!-- Jika kosong -->
          <tr v-if="paginatedTransfers.length === 0">
            <td colspan="7" class="text-center text-muted py-4">
              Belum ada transfer stok.
            </td>
          </tr>

          <!-- Jika ada data -->
          <tr v-else v-for="(t, i) in paginatedTransfers" :key="t.id">
            <td>{{ (currentPage - 1) * perPage + i + 1 }}</td>
            <td>{{ t.product.name }}</td>
            <td>{{ t.quantity }}</td>
            <td>{{ t.from_store ? t.from_store.name : '-' }}</td>
            <td>{{ t.to_store ? t.to_store.name : '-' }}</td>
            <td>{{ t.note }}</td>
            <td>{{ t.type }}</td>
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

const props = defineProps({ transfers: Array })

// Fungsi untuk menampilkan informasi stock transfer
const showInfo = () => {
  Swal.fire({
    title: '📦 Tentang Transfer Stok',
    html: `
      <div class="text-start">
        <p><strong>Transfer Stok</strong> adalah fitur untuk memindahkan produk antar toko dalam satu sistem.</p>
        
        <div class="mt-3">
          <h6>Fungsi Utama:</h6>
          <ul class="mb-3">
            <li>Memindahkan stok dari toko yang kelebihan ke toko yang kekurangan</li>
            <li>Mengoptimalkan persediaan produk di semua cabang</li>
            <li>Mencatat riwayat perpindahan barang</li>
          </ul>
        </div>
      </div>
    `,
    icon: 'info',
    confirmButtonText: 'Mengerti',
    confirmButtonColor: '#3085d6',
    width: '500px'
  })
}

// state
const currentPage = ref(1)
const perPage = ref(20) // Fixed 20 items per page
const search = ref('')

// filter by search
const filteredTransfers = computed(() => {
  if (!search.value) return props.transfers
  return props.transfers.filter(t =>
    t.product.name.toLowerCase().includes(search.value.toLowerCase()) ||
    (t.note && t.note.toLowerCase().includes(search.value.toLowerCase())) ||
    t.type.toLowerCase().includes(search.value.toLowerCase()) ||
    (t.from_store && t.from_store.name.toLowerCase().includes(search.value.toLowerCase())) ||
    (t.to_store && t.to_store.name.toLowerCase().includes(search.value.toLowerCase()))
  )
})

// total halaman (minimal 1)
const totalPages = computed(() => Math.max(1, Math.ceil(filteredTransfers.value.length / perPage.value)))

// data sesuai halaman aktif
const paginatedTransfers = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredTransfers.value.slice(start, end)
})

// simplified pagination
const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  
  if (totalPages.value <= maxVisible) {
    for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  } else {
    let start = Math.max(1, currentPage.value - 2)
    let end = Math.min(totalPages.value, start + maxVisible - 1)
    
    if (end === totalPages.value) {
      start = totalPages.value - maxVisible + 1
    }
    
    for (let i = start; i <= end; i++) pages.push(i)
  }
  
  return pages
})

// navigasi
const goToPage = (page) => { 
  if (page >= 1 && page <= totalPages.value) currentPage.value = page 
}
const nextPage = () => goToPage(currentPage.value + 1)
const prevPage = () => goToPage(currentPage.value - 1)

// reset ke halaman 1 jika search berubah
watch(search, () => { 
  currentPage.value = 1 
})
</script>

<style scoped>
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
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

/* Optimasi untuk mobile */
@media (max-width: 768px) {
  .table {
    font-size: 0.875rem;
  }
  
  .btn {
    font-size: 0.875rem;
  }
}
</style>