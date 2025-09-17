<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Produk</h2>
      <Link href="/products/create" class="btn btn-primary">+ Tambah Produk</Link>
    </div>

    <!-- 🔥 Dropdown pilih jumlah data -->
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <label class="me-2">Tampilkan</label>
        <select v-model="perPage" class="form-select d-inline-block w-auto">
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
        <span class="ms-2">data per halaman</span>
      </div>

      <div>
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Cari produk..."
          class="form-control"
          style="width: 250px"
        />
      </div>

    </div>

    <!-- @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif -->

    <!-- 🔥 Table responsive -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>Kode (SKU)</th>
            <th>Nama</th>
            <th>Modal</th>
            <th>Harga Jual</th>
            <th>Diskon</th>
            <th>Stok</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!products || products.length === 0">
            <td colspan="7" class="text-center text-muted py-4">
              Tidak ada produk.
            </td>
          </tr>

          <tr v-else v-for="product in paginatedProducts" :key="product.id">
            <td>{{ product.sku }}</td>
            <td>{{ product.name }}</td>
            <td>Rp {{ Number(product.cost).toLocaleString() }}</td>
            <td>Rp {{ Number(product.price).toLocaleString() }}</td>
            <td>{{ product.discount }}%</td>
            <td>
              {{
                product.stocks
                  ? product.stocks.reduce((total, s) => {
                      if (s.type === 'in') return total + s.quantity
                      if (s.type === 'out') return total - s.quantity
                      if (s.type === 'adjustment') return total + s.quantity
                      return total
                    }, 0)
                  : 0
              }}
            </td>
            <td>
              <Link
                :href="`/products/${product.id}/edit`"
                class="btn btn-sm btn-warning me-2"
              >
                Edit
              </Link>
              <button
                class="btn btn-sm btn-danger"
                @click="confirmDelete(product.id)"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 🔽 Pagination -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <!-- Tombol prev -->
        <li :class="['page-item', { disabled: currentPage === 1 }]">
          <button class="page-link" @click="prevPage" :disabled="currentPage === 1">
            &laquo;
          </button>
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

        <!-- Tombol next -->
        <li :class="['page-item', { disabled: currentPage === totalPages }]">
          <button class="page-link" @click="nextPage" :disabled="currentPage === totalPages">
            &raquo;
          </button>
        </li>
      </ul>
    </nav>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import { ref, computed, watch } from 'vue'

const props = defineProps({ products: Array })

// state pagination
const perPage = ref(10)
const currentPage = ref(1)
const searchQuery = ref('')

// hitung total halaman
const totalPages = computed(() => Math.ceil(filteredProducts.value.length / perPage.value) || 1)
const filteredProducts = computed(() => {
  if (!searchQuery.value) return props.products
  const query = searchQuery.value.toLowerCase()
  return props.products.filter(
    p =>
      p.name.toLowerCase().includes(query) ||
      p.sku.toLowerCase().includes(query)
  )
})
// ambil data sesuai halaman
const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredProducts.value.slice(start, end)
})

// truncated pagination yang robust
const visiblePages = computed(() => {
  const pages = []

  if (totalPages.value <= 5) {
    // tampilkan semua halaman jika total <= 5
    for (let i = 1; i <= totalPages.value; i++) pages.push(i)
    return pages
  }

  const delta = 2
  let start = Math.max(2, currentPage.value - delta)
  let end = Math.min(totalPages.value - 1, currentPage.value + delta)

  // geser window jika dekat awal
  if (currentPage.value - delta <= 1) {
    end = 5
  }
  // geser window jika dekat akhir
  if (currentPage.value + delta >= totalPages.value) {
    start = totalPages.value - 4
  }

  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

// navigasi
const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) currentPage.value = page }
const prevPage = () => goToPage(currentPage.value - 1)
const nextPage = () => goToPage(currentPage.value + 1)

// reset ke halaman 1 jika perPage berubah
watch([searchQuery, perPage], () => {
  currentPage.value = 1
})


// konfirmasi hapus produk
const confirmDelete = (id) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Produk yang dihapus tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then(result => {
    if (result.isConfirmed) {
      router.delete(`/products/${id}`, {
        onSuccess: () => Swal.fire('Berhasil!', 'Produk berhasil dihapus.', 'success'),
        onError: () => Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus produk.', 'error'),
      })
    }
  })
}
</script>

