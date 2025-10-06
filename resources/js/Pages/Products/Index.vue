<template>
    <AppLayout>
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
            <h2>Daftar Produk</h2>

            <div class="mt-2 mt-md-0">
                <!-- Tombol Tambah hanya untuk Admin & Manager -->
                <Link 
                    v-if="canManageProducts" 
                    href="/products/create" 
                    class="btn btn-primary me-2 mb-2 mb-md-0"
                >
                   Tambah
                </Link>
                
                <!-- Tombol Atur Kategori hanya untuk Admin & Manager -->
                <button 
                    v-if="canManageProducts" 
                    class="btn btn-success me-2 mb-2 mb-md-0" 
                    @click="openManageCategory"
                >
                    Atur Kategori
                </button>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="row mb-3 g-2">
            <!-- Search + Filter kategori -->
            <div class="col-12 col-md d-flex gap-2">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Cari produk..." 
                    class="form-control" 
                />
                <select v-model="selectedCategory" class="form-select w-auto">
                    <option value="">Kategori</option>
                    <option v-for="cat in sortedCategories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Table Produk -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-nowrap">
                <thead class="table-dark">
                    <tr>
                        <!-- Checkbox hanya untuk Admin & Manager -->
                        <th v-if="canManageProducts">
                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                        </th>
                        <th>Kode (SKU)</th>
                        <th>Nama</th>
                        
                        <!-- Kolom Modal hanya untuk Admin & Manager -->
                        <th v-if="canManageProducts">Modal</th>
                        
                        <th>Harga Jual</th>
                        <th>Diskon</th>
                        <th>Stok</th>
                        
                        <!-- Kolom Ditambahkan hanya untuk Admin & Manager -->
                        <th v-if="canManageProducts">Ditambahkan</th>
                        
                        <!-- Kolom Aksi hanya untuk Admin & Manager -->
                        <th v-if="canManageProducts">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loader khusus di tabel -->
                   <tr v-if="tableLoading">
                        <td :colspan="getColspanCount" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Memuat data produk...</div>
                        </td>
                    </tr>

                    <!-- Data produk -->
                    <tr v-else-if="paginatedProducts.length === 0">
                        <td :colspan="getColspanCount" class="text-center text-muted py-4">
                            Tidak ada produk.
                        </td>
                    </tr>

                    <tr v-else v-for="product in paginatedProducts" :key="product.id">
                      <!-- Checkbox hanya untuk Admin & Manager -->
                      <td v-if="canManageProducts">
                        <input type="checkbox" v-model="selectedProducts" :value="product.id">
                      </td>
                      
                      <td>{{ product.sku }}</td>
                      <td>{{ product.name }}</td>
                      
                      <!-- Harga Modal hanya untuk Admin & Manager -->
                      <td v-if="canManageProducts" class="text-center">
                        <!-- Jika belum ditampilkan -->
                        <span v-if="!product.showCost" @click="toggleCost(product)" style="cursor:pointer">
                            <i class="bi bi-eye text-primary"></i>
                        </span>

                        <!-- Jika sudah ditampilkan -->
                        <span v-else>
                            Rp {{ Number(product.cost).toLocaleString() }}
                            <i class="bi bi-eye-slash text-danger ms-2" style="cursor:pointer" @click="toggleCost(product)"></i>
                        </span>
                      </td>

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
                      
                      <!-- Kolom Ditambahkan hanya untuk Admin & Manager -->
                      <td v-if="canManageProducts">
                          <div v-if="product.created_by">
                              <div class="fw-semibold">{{ product.created_by.name }}</div>
                              <small class="text-muted">
                                  {{ formatDate(product.created_at) }}
                              </small>
                          </div>
                          <div v-else class="text-muted">
                              <small>-</small>
                          </div>
                      </td>
                      
                      <!-- Kolom Aksi hanya untuk Admin & Manager -->
                      <td v-if="canManageProducts">
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
            
            <!-- Tombol Bulk Delete hanya untuk Admin & Manager -->
            <button 
                v-if="canManageProducts"
                class="btn btn-danger me-2" 
                :disabled="selectedProducts.length === 0"
                @click="confirmDeleteMultiple"
            >
                Hapus Terpilih ({{ selectedProducts.length }})
            </button>
        </div>

        <!-- ✅ PERBAIKAN: Pagination yang lebih baik -->
        <nav class="mt-3">
            <ul class="pagination justify-content-center flex-wrap">
                <!-- Previous Button -->
                <li :class="['page-item', { disabled: currentPage === 1 }]">
                    <button class="page-link" @click="prevPage" :disabled="currentPage === 1">
                        &laquo;
                    </button>
                </li>

                <!-- First Page -->
                <li v-if="showFirstPage" class="page-item">
                    <button class="page-link" @click="goToPage(1)">1</button>
                </li>

                <!-- First Ellipsis -->
                <li v-if="showFirstEllipsis" class="page-item disabled">
                    <span class="page-link">...</span>
                </li>

                <!-- Page Numbers -->
                <li 
                    v-for="page in visiblePages" 
                    :key="page" 
                    :class="['page-item', { active: currentPage === page }]"
                >
                    <button class="page-link" @click="goToPage(page)">
                        {{ page }}
                    </button>
                </li>

                <!-- Second Ellipsis -->
                <li v-if="showSecondEllipsis" class="page-item disabled">
                    <span class="page-link">...</span>
                </li>

                <!-- Last Page -->
                <li v-if="showLastPage" class="page-item">
                    <button class="page-link" @click="goToPage(totalPages)">
                        {{ totalPages }}
                    </button>
                </li>

                <!-- Next Button -->
                <li :class="['page-item', { disabled: currentPage === totalPages }]">
                    <button class="page-link" @click="nextPage" :disabled="currentPage === totalPages">
                        &raquo;
                    </button>
                </li>
            </ul>

            <!-- Info Pagination -->
            <div class="text-center text-muted small mt-2">
                Menampilkan {{ startItem }}-{{ endItem }} dari {{ filteredProducts.length }} produk
            </div>
        </nav>

        <!-- Modal Manage Kategori hanya untuk Admin & Manager -->
        <div v-if="showCategoryModal && canManageProducts" class="modal fad modal-categories show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Kategori</h5>
                    <button type="button" class="btn-close" @click="showCategoryModal = false"></button>
                </div>
                <div class="modal-body">
                    <!-- Input Tambah Kategori -->
                    <div class="input-group mb-3">
                        <input type="text" v-model="newCategoryName" class="form-control"
                            placeholder="Tambah kategori baru" />
                        <button class="btn btn-success" @click="saveNewCategory">Simpan</button>
                    </div>

                    <!-- List Kategori -->
                    <ul 
                    class="list-group mb-3 overflow-auto" 
                    style="max-height: 350px;"
                    >
                    <li 
                        v-for="cat in sortedCategories" 
                        :key="cat.id"
                        class="list-group-item d-flex justify-content-between align-items-center"
                    >
                        {{ cat.name }}
                        <button class="btn btn-sm btn-danger" @click="deleteCategory(cat.id)">Hapus</button>
                    </li>
                    </ul>
                </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
    import {
        ref,
        computed,
        watch
    } from 'vue'
    import {
        Link,
        router,
        usePage
    } from '@inertiajs/vue3'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import Swal from 'sweetalert2'
    import axios from 'axios'
    import { startLoading, stopLoading } from '@/Stores/useLoading'

    const page = usePage()

    const props = defineProps({
        products: Array,
        categories: Array,
        filters: {
            type: Object,
            default: () => ({})
        }
    })

    // ✅ COMPUTED: Cek apakah user bisa manage products (Admin atau Manager)
    const canManageProducts = computed(() => {
        const userRole = page.props.auth?.user?.role_id
        return userRole === 1 || userRole === 2
    })

    // ✅ COMPUTED: Hitung jumlah kolom berdasarkan role
    const getColspanCount = computed(() => {
        let count = 5 // Base columns untuk Kasir
        if (canManageProducts.value) {
            count += 4 // Checkbox, Modal, Ditambahkan, Aksi
        }
        return count
    })

    // ✅ TAMBAH: Fungsi format tanggal
    const formatDate = (dateString) => {
        if (!dateString) return '-'
        const date = new Date(dateString)
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        })
    }

    // Reactive state
    const perPage = ref(20)
    const currentPage = ref(1)
    const searchQuery = ref('')
    const selectedCategory = ref(props.filters.category || '')
    const categories = ref([...props.categories])
    const showCategoryModal = ref(false)
    const newCategoryName = ref('')
    const tableLoading = ref(false)

    // COMPUTED PROPERTIES
    // --- FILTER SECTION ---
    const productList = ref([...props.products])
    const filteredProducts = computed(() => {
    let list = [...productList.value]

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        list = list.filter(p => {
        const category = categories.value.find(c => c.id === p.category_id)
        const categoryName = category ? category.name.toLowerCase() : ''
                return (
                    p.name.toLowerCase().includes(query) ||
                    p.sku.toLowerCase().includes(query) ||
                    categoryName.includes(query)
                )
        })
    }

    if (selectedCategory.value) {
        list = list.filter(p => p.category_id == selectedCategory.value)
    }

    return list
    })

    // ✅ PERBAIKAN: Pagination dengan logic yang lebih baik
    const totalPages = computed(() => Math.ceil(filteredProducts.value.length / perPage.value) || 1)

    const paginatedProducts = computed(() => {
        const start = (currentPage.value - 1) * perPage.value
        const end = start + perPage.value
        return filteredProducts.value.slice(start, end)
    })

    // ✅ PERBAIKAN: Visible pages dengan mobile-friendly (max 5 pages)
    const visiblePages = computed(() => {
        const pages = []
        const maxVisible = 5 // Maximum pages to show
        const delta = 2 // Pages to show before and after current page

        let start = Math.max(1, currentPage.value - delta)
        let end = Math.min(totalPages.value, currentPage.value + delta)

        // Adjust if we're near the start
        if (currentPage.value - delta <= 1) {
            end = Math.min(totalPages.value, maxVisible)
        }

        // Adjust if we're near the end
        if (currentPage.value + delta >= totalPages.value) {
            start = Math.max(1, totalPages.value - maxVisible + 1)
        }

        for (let i = start; i <= end; i++) {
            pages.push(i)
        }

        return pages
    })

    // ✅ PERBAIKAN: Pagination controls visibility
    const showFirstPage = computed(() => {
        return visiblePages.value.length > 0 && visiblePages.value[0] > 1
    })

    const showFirstEllipsis = computed(() => {
        return showFirstPage.value && visiblePages.value[0] > 2
    })

    const showLastPage = computed(() => {
        return visiblePages.value.length > 0 && visiblePages.value[visiblePages.value.length - 1] < totalPages.value
    })

    const showSecondEllipsis = computed(() => {
        return showLastPage.value && visiblePages.value[visiblePages.value.length - 1] < totalPages.value - 1
    })

    // ✅ PERBAIKAN: Pagination info
    const startItem = computed(() => {
        return (currentPage.value - 1) * perPage.value + 1
    })

    const endItem = computed(() => {
        return Math.min(currentPage.value * perPage.value, filteredProducts.value.length)
    })

    // --- Methods ---
    const goToPage = (page) => {
        if (page >= 1 && page <= totalPages.value) {
            currentPage.value = page
            // Scroll to top when changing page
            window.scrollTo({ top: 0, behavior: 'smooth' })
        }
    }

    const prevPage = () => goToPage(currentPage.value - 1)
    const nextPage = () => goToPage(currentPage.value + 1)

    watch([searchQuery, selectedCategory], () => {
        // Reset to first page when filtering
        currentPage.value = 1
    })

    watch([searchQuery, perPage, currentPage], async () => {
      tableLoading.value = true
      await new Promise(resolve => setTimeout(resolve, 400)) 
      tableLoading.value = false
    })

    // CATEGORI SECTION CODE
    const sortedCategories = computed(() => {
    return [...categories.value].sort((a, b) =>
        a.name.localeCompare(b.name, 'id', { sensitivity: 'base' })
    )
    })

    const addCategory = async () => {
        const {
            value: name
        } = await Swal.fire({
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
                startLoading()
                const res = await axios.post('/categories/quick-add', {
                    name
                })
                categories.value.push(res.data.category)
                Swal.fire('Berhasil!', 'Kategori berhasil ditambahkan', 'success')
            } catch (err) {
                Swal.fire('Gagal!', err.response ?.data ?.message || 'Terjadi kesalahan', 'error')
            } finally {
                stopLoading()
            }
        }
    }

    const openManageCategory = () => {
        if (!canManageProducts.value) {
            Swal.fire('Akses Ditolak', 'Anda tidak memiliki akses untuk mengelola kategori', 'warning')
            return
        }
        showCategoryModal.value = true
    }

    const saveNewCategory = async () => {
        if (!newCategoryName.value) return
        try {
            startLoading()
            const res = await axios.post('/categories/quick-add', {
                name: newCategoryName.value
            })
            categories.value.push(res.data.category)
            newCategoryName.value = ''
            Swal.fire('Berhasil!', 'Kategori berhasil ditambahkan', 'success')
        } catch (err) {
            Swal.fire('Gagal!', err.response ?.data ?.message || 'Terjadi kesalahan', 'error')
        } finally {
            stopLoading()
        }
    }

    const deleteCategory = async (id) => {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Kategori yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    startLoading()
                    await axios.post(`/categories/${id}`, {
                        _method: 'delete'
                    })
                    categories.value = categories.value.filter(c => c.id !== id)
                    Swal.fire('Berhasil!', 'Kategori berhasil dihapus', 'success')
                } catch (err) {
                    Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan', 'error')
                } finally {
                    stopLoading()
                }
            }
        })
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
    }).then(async (result) => {
        if (result.isConfirmed) {
        try {
            startLoading()
            await axios.post(`/products/${id}`, {
            _method: 'delete'
            })
            productList.value = productList.value.filter(p => p.id !== id)
            Swal.fire('Berhasil!', 'Produk berhasil dihapus.', 'success')
        } catch (err) {
            Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan.', 'error')
        } finally {
            stopLoading()
        }
        }
    })
    }

    // Reactive untuk checkbox
    const selectedProducts = ref([])
    const selectAll = ref(false)

    // Toggle semua checkbox
    const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedProducts.value = paginatedProducts.value.map(p => p.id)
    } else {
        selectedProducts.value = []
    }
    }

    // Watch jika paginatedProducts berubah, reset selectAll
    watch(paginatedProducts, () => {
    selectAll.value = false
    selectedProducts.value = []
    })

    // Hapus banyak produk
    const confirmDeleteMultiple = () => {
    if (selectedProducts.value.length === 0) return

    Swal.fire({
        title: `Hapus ${selectedProducts.value.length} produk?`,
        text: 'Produk yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
    }).then(async (result) => {
        if (result.isConfirmed) {
        try {
            startLoading()
            await axios.post('/products/bulk-delete', {
            ids: selectedProducts.value
            })
            productList.value = productList.value.filter(
            p => !selectedProducts.value.includes(p.id)
            )
            selectedProducts.value = []
            selectAll.value = false
            Swal.fire('Berhasil!', 'Produk berhasil dihapus.', 'success')
        } catch (err) {
            Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan.', 'error')
        } finally {
            stopLoading()
        }
        }
    })
    }

    // toggle harga modal
    const toggleCost = (product) => {
    product.showCost = !product.showCost
    }
</script>

<style>
    .modal.fad.modal-categories {
        background-color: rgba(0, 0, 0, 0.548);
    }

    .btn.btn-sm{
        padding: 7px 14px;
    }

    /* ✅ PERBAIKAN: Pagination responsive */
    .pagination {
        flex-wrap: wrap;
        gap: 2px;
    }

    .page-item .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    @media (max-width: 576px) {
        .page-item .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .pagination {
            gap: 1px;
        }
    }
</style>