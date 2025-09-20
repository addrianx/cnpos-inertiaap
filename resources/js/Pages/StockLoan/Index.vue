<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Pinjam Stok</h2>
      <Link href="/stock-loan/create" class="btn btn-primary">+ Pinjam Stok</Link>
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
            <th>Toko Peminjam</th>
            <th>Toko Pemberi</th>
            <th>Catatan</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <!-- Jika kosong -->
          <tr v-if="paginatedLoans.length === 0">
            <td colspan="7" class="text-center text-muted py-4">
              Belum ada pinjaman stok.
            </td>
          </tr>

          <!-- Jika ada data -->
          <tr v-else v-for="(loan, i) in paginatedLoans" :key="loan.id">
            <td>{{ (currentPage - 1) * perPage + i + 1 }}</td>
            <td>
              <ul class="mb-0 ps-3">
                <li v-for="item in loan.items" :key="item.id">
                  {{ item.product.name }} ({{ item.quantity }})
                </li>
              </ul>
            </td>
            <td>{{ loan.items.reduce((sum, it) => sum + it.quantity, 0) }}</td>
            <td>{{ loan.from_store ? loan.from_store.name : '-' }}</td>
            <td>{{ loan.to_store ? loan.to_store.name : '-' }}</td>
            <td>{{ loan.notes }}</td>
            <td>
              <span
                class="badge"
                :class="{
                  'bg-warning': loan.status === 'pending',
                  'bg-success': loan.status === 'returned',
                  'bg-danger': loan.status === 'cancelled'
                }"
              >
                {{ loan.status }}
              </span>
            </td>
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

const props = defineProps({ loans: Array }) // array pinjam stok

// state
const currentPage = ref(1)
const perPage = ref(10)
const search = ref('')

// filter by search
const filteredLoans = computed(() => {
  if (!search.value) return props.loans
  return props.loans.filter(l =>
    (l.notes && l.notes.toLowerCase().includes(search.value.toLowerCase())) ||
    l.status.toLowerCase().includes(search.value.toLowerCase()) ||
    (l.from_store && l.from_store.name.toLowerCase().includes(search.value.toLowerCase())) ||
    (l.to_store && l.to_store.name.toLowerCase().includes(search.value.toLowerCase())) ||
    l.items.some(item => item.product.name.toLowerCase().includes(search.value.toLowerCase()))
  )
})

// total halaman (minimal 1)
const totalPages = computed(() => Math.max(1, Math.ceil(filteredLoans.value.length / perPage.value)))

// data sesuai halaman aktif
const paginatedLoans = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredLoans.value.slice(start, end)
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
