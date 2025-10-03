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

    <!-- 🔍 Search & Per Page -->
    <div class="row mb-2 g-2">
      <!-- Filter -->
      <div class="col-12 col-md-auto">
        <div class="d-flex align-items-center">
          <label class="me-2">Tampilkan</label>
          <select v-model.number="perPage" class="form-select w-auto">
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <span class="ms-2">item per halaman</span>
        </div>
      </div>

      <!-- Search -->
      <div class="col-12 col-md">
        <input
          v-model="search"
          type="text"
          class="form-control w-100"
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

    <!-- 🔽 Pagination -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="prevPage">&laquo;</button>
        </li>

        <li v-if="visiblePages[0] > 1" class="page-item">
          <button class="page-link" @click="goToPage(1)">1</button>
        </li>

        <li v-if="visiblePages[0] > 2" class="page-item disabled">
          <span class="page-link">...</span>
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

        <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
          <span class="page-link">...</span>
        </li>

        <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
          <button class="page-link" @click="goToPage(totalPages)">{{ totalPages }}</button>
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

const props = defineProps({ transfers: Array }) // array transfer stok

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
          
          <h6>Cara Kerja:</h6>
          <ol>
            <li>Pilih toko pengirim dan penerima</li>
            <li>Tentukan produk dan jumlah yang akan ditransfer</li>
            <li>Sistem akan mengurangi stok di toko pengirim</li>
            <li>Dan menambah stok di toko penerima</li>
          </ol>
        </div>
      </div>
    `,
    icon: 'info',
    confirmButtonText: 'Mengerti',
    confirmButtonColor: '#3085d6',
    width: '600px'
  })
}

// state
const currentPage = ref(1)
const perPage = ref(10)
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

// truncated pagination
const visiblePages = computed(() => {
  const pages = []
  if (totalPages.value <= 5) {
    for (let i = 1; i <= totalPages.value; i++) pages.push(i)
    return pages
  }
  const delta = 2
  let start = Math.max(2, currentPage.value - delta)
  let end = Math.min(totalPages.value - 1, currentPage.value + delta)
  if (currentPage.value - delta <= 1) end = 5
  if (currentPage.value + delta >= totalPages.value) start = totalPages.value - 4
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

// navigasi
const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) currentPage.value = page }
const nextPage = () => goToPage(currentPage.value + 1)
const prevPage = () => goToPage(currentPage.value - 1)

// reset ke halaman 1 jika perPage atau search berubah
watch([perPage, search], () => { currentPage.value = 1 })
</script>