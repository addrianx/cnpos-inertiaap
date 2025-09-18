<template>
  <AppLayout>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
      <h2>Daftar Produk</h2>

      <div class="mt-2 mt-md-0">
        <Link href="/products/create" class="btn btn-primary me-2 mb-2 mb-md-0">
          + Tambah Produk
        </Link>
        <button class="btn btn-success me-2 mb-2 mb-md-0" @click="openManageCategory">
          Manage Kategori
        </button>
      </div>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3 g-2">
      <!-- Tampilkan per halaman -->
      <div class="col-12 col-md-auto">
        <div class="d-flex align-items-center">
          <label class="me-2">Tampilkan</label>
          <select v-model="perPage" class="form-select w-auto">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
          <span class="ms-2">data per halaman</span>
        </div>
      </div>

      <!-- Search -->
      <div class="col-12 col-md">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Cari produk..."
          class="form-control w-100"
        />
      </div>

      <!-- Filter Kategori + Quick Add -->
      <div class="col-12 col-md-auto">
        <div class="d-flex align-items-center">
          <label class="me-2">Kategori</label>
          <select v-model="selectedCategory" class="form-select w-auto">
            <option value="">Semua</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
          <button class="btn btn-sm btn-outline-success ms-2" @click="addCategory">
            + Kategori
          </button>
        </div>
      </div>
    </div>

    <!-- Table Produk -->
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
          <tr v-if="paginatedProducts.length === 0">
            <td colspan="7" class="text-center text-muted py-4">
              Tidak ada produk.
            </td>
          </tr>

          <tr v-for="product in paginatedProducts" :key="product.id">
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
              <Link :href="`/products/${product.id}/edit`" class="btn btn-sm btn-warning me-2">
                Edit
              </Link>
              <button class="btn btn-sm btn-danger" @click="confirmDelete(product.id)">
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <li :class="['page-item', { disabled: currentPage === 1 }]">
          <button class="page-link" @click="prevPage">&laquo;</button>
        </li>

        <li v-if="visiblePages[0] > 1" class="page-item">
          <button class="page-link" @click="goToPage(1)">1</button>
        </li>

        <li v-if="visiblePages[0] > 2" class="page-item disabled">
          <span class="page-link">...</span>
        </li>

        <li v-for="page in visiblePages" :key="page" :class="['page-item', { active: currentPage === page }]">
          <button class="page-link" @click="goToPage(page)">{{ page }}</button>
        </li>

        <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
          <span class="page-link">...</span>
        </li>

        <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
          <button class="page-link" @click="goToPage(totalPages)">{{ totalPages }}</button>
        </li>

        <li :class="['page-item', { disabled: currentPage === totalPages }]">
          <button class="page-link" @click="nextPage">&raquo;</button>
        </li>
      </ul>
    </nav>

    <!-- Modal Manage Kategori -->
    <div v-if="showCategoryModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Manage Kategori</h5>
            <button type="button" class="btn-close" @click="showCategoryModal = false"></button>
          </div>
          <div class="modal-body">
            <ul class="list-group mb-3">
              <li v-for="cat in categories" :key="cat.id" class="list-group-item d-flex justify-content-between align-items-center">
                {{ cat.name }}
                <button class="btn btn-sm btn-danger" @click="deleteCategory(cat.id)">Hapus</button>
              </li>
            </ul>
            <div class="input-group">
              <input type="text" v-model="newCategoryName" class="form-control" placeholder="Tambah kategori baru" />
              <button class="btn btn-success" @click="saveNewCategory">Simpan</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import axios from 'axios'

const props = defineProps({
  products: Array,
  categories: Array,
  filters: { type: Object, default: () => ({}) }
})

// Reactive state
const perPage = ref(10)
const currentPage = ref(1)
const searchQuery = ref('')
const selectedCategory = ref(props.filters.category || '')
const categories = ref([...props.categories])
const showCategoryModal = ref(false)
const newCategoryName = ref('')

// --- Computed ---
const filteredProducts = computed(() => {
  let list = props.products
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    list = list.filter(p => p.name.toLowerCase().includes(query) || p.sku.toLowerCase().includes(query))
  }
  if (selectedCategory.value) {
    list = list.filter(p => p.category_id == selectedCategory.value)
  }
  return list
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / perPage.value) || 1)

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredProducts.value.slice(start, end)
})

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

// --- Methods ---
const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) currentPage.value = page }
const prevPage = () => goToPage(currentPage.value - 1)
const nextPage = () => goToPage(currentPage.value + 1)
watch([searchQuery, perPage], () => { currentPage.value = 1 })

const addCategory = async () => {
  const { value: name } = await Swal.fire({
    title: 'Tambah Kategori',
    input: 'text',
    inputLabel: 'Nama kategori',
    showCancelButton: true,
    confirmButtonText: 'Simpan',
    cancelButtonText: 'Batal',
    inputValidator: (value) => (!value ? 'Nama kategori tidak boleh kosong' : null)
  })
  if (name) {
    try {
      const res = await axios.post('/categories/quick-add', { name })
      categories.value.push(res.data.category)
      Swal.fire('Berhasil!', 'Kategori berhasil ditambahkan', 'success')
    } catch (err) {
      Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan', 'error')
    }
  }
}

const openManageCategory = () => {
  showCategoryModal.value = true
}

const saveNewCategory = async () => {
  if (!newCategoryName.value) return
  try {
    const res = await axios.post('/categories/quick-add', { name: newCategoryName.value })
    categories.value.push(res.data.category)
    newCategoryName.value = ''
    Swal.fire('Berhasil!', 'Kategori berhasil ditambahkan', 'success')
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan', 'error')
  }
}

const deleteCategory = async (id) => {
  try {
    await axios.post(`/categories/${id}`, { _method: 'delete' }) // <- pakai POST + _method
    categories.value = categories.value.filter(c => c.id !== id)
    Swal.fire('Berhasil!', 'Kategori berhasil dihapus', 'success')
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan', 'error')
  }
}

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
