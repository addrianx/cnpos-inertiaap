<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Stok</h2>
      <Link href="/stock/adjust" class="btn btn-primary">+ Penyesuaian Stok</Link>
    </div>

    <!-- 🔍 Search & Per Page -->
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <label class="me-2">Tampilkan</label>
        <select v-model.number="perPage" class="form-select d-inline-block w-auto">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
        <span class="ms-2">item per halaman</span>
      </div>
      <div>
        <input
          v-model="search"
          type="text"
          class="form-control"
          placeholder="Cari produk atau catatan..."
        />
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped no-wrap">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Catatan</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <!-- Jika kosong -->
          <tr v-if="paginatedStocks.length === 0">
            <td colspan="5" class="text-center text-muted py-4">
              Tidak ada stok.
            </td>
          </tr>

          <!-- Jika ada data -->
          <tr v-else v-for="(s, i) in paginatedStocks" :key="s.id">
            <td>{{ (currentPage - 1) * perPage + i + 1 }}</td>
            <td>{{ s.product.name }}</td>
            <td>{{ s.quantity }}</td>
            <td>{{ s.note }}</td>
            <td>{{ s.type }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 🔽 Pagination -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <!-- Prev -->
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="prevPage">&laquo;</button>
        </li>

        <!-- Halaman pertama -->
        <li v-if="visiblePages[0] > 1" class="page-item">
          <button class="page-link" @click="goToPage(1)">1</button>
        </li>

        <!-- Ellipsis sebelum -->
        <li v-if="visiblePages[0] > 2" class="page-item disabled">
          <span class="page-link">...</span>
        </li>

        <!-- Loop halaman -->
        <li
          v-for="page in visiblePages"
          :key="page"
          :class="['page-item', { active: currentPage === page }]"
        >
          <button class="page-link" @click="goToPage(page)">
            {{ page }}
          </button>
        </li>

        <!-- Ellipsis sesudah -->
        <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
          <span class="page-link">...</span>
        </li>

        <!-- Halaman terakhir -->
        <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
          <button class="page-link" @click="goToPage(totalPages)">
            {{ totalPages }}
          </button>
        </li>

        <!-- Next -->
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

const props = defineProps({ stocks: Array })

// state
const currentPage = ref(1)
const perPage = ref(10)
const search = ref('')

// filter by search
const filteredStocks = computed(() => {
  if (!search.value) return props.stocks
  return props.stocks.filter(s =>
    s.product.name.toLowerCase().includes(search.value.toLowerCase()) ||
    (s.note && s.note.toLowerCase().includes(search.value.toLowerCase())) ||
    s.type.toLowerCase().includes(search.value.toLowerCase())
  )
})

// total halaman (selalu min 1 biar pagination konsisten)
const totalPages = computed(() => Math.max(1, Math.ceil(filteredStocks.value.length / perPage.value)))

// data sesuai halaman aktif
const paginatedStocks = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredStocks.value.slice(start, end)
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
