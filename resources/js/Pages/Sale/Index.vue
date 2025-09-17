<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Penjualan</h2>
      <Link href="/sales/create" class="btn btn-primary">+ Tambah Penjualan</Link>
    </div>

    <!-- Filter & show per page -->
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
          type="text"
          v-model="search"
          placeholder="Kode produk..."
          class="form-control"
          style="width: 250px"
        />
      </div>
    </div>

    <!-- Jika kosong -->
    <div v-if="filteredSales.length === 0" class="alert alert-info">
      Belum ada penjualan.
    </div>

    <!-- Jika ada data -->
    <div v-else class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Kode Invoice</th>
            <th>Items</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sale in paginatedSales" :key="sale.id">
            <td>{{ sale.id }}</td>
            <td>{{ new Date(sale.created_at).toLocaleString() }}</td>
            <td>{{ sale.sale_code }}</td>
            <td>
              <ul class="mb-0">
                <li v-for="item in sale.items" :key="item.id">
                  {{ item.product.name }} x {{ item.quantity }} =
                  Rp {{ Number(item.subtotal).toLocaleString() }}
                </li>
              </ul>
            </td>
            <td>Rp {{ Number(sale.total).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav v-if="totalPages > 1">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="prevPage">Sebelumnya</button>
        </li>

        <li
          v-for="page in totalPages"
          :key="page"
          class="page-item"
          :class="{ active: currentPage === page }"
        >
          <button class="page-link" @click="goToPage(page)">{{ page }}</button>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="nextPage">Berikutnya</button>
        </li>
      </ul>
    </nav>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ sales: Array })

// state
const currentPage = ref(1)
const perPage = ref(10)
const search = ref('')

// filter pelanggan / produk
const filteredSales = computed(() => {
  if (!search.value) return props.sales
  return props.sales.filter(sale => {
    return sale.sale_code
      ? sale.sale_code.toLowerCase().includes(search.value.toLowerCase())
      : false
  })
})

// total halaman
const totalPages = computed(() =>
  Math.ceil(filteredSales.value.length / perPage.value) || 1
)

// data halaman aktif
const paginatedSales = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredSales.value.slice(start, end)
})

// 🔥 truncated pagination robust
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

// reset ke page 1 jika search / perPage berubah
watch([search, perPage], () => { currentPage.value = 1 })
</script>

