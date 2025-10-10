<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Penjualan</h2>
      <Link href="/sales/create" class="btn btn-primary">+ Transaksi Baru</Link>
    </div>

    <!-- Form Pencarian dan Filter -->
    <div class="card mb-4">
      <div class="card-body p-3">
        <div class="row g-2 align-items-center">
          <!-- Form Pencarian Kode Penjualan -->
          <div class="col-sm-6 col-md-4">
            <input
              type="text"
              v-model="searchQuery"
              class="form-control form-control-sm"
              placeholder="Cari kode penjualan..."
              @input="applyFilters"
            >
          </div>

          <!-- Form Filter Tanggal -->
          <div class="col-sm-6 col-md-8">
            <div class="row g-2 align-items-center">
              <div class="col-6 col-md-3">
                <input
                  type="date"
                  v-model="startDate"
                  class="form-control form-control-sm"
                  :max="endDate || today"
                  @change="applyFilters"
                >
              </div>
              <div class="col-6 col-md-3">
                <input
                  type="date"
                  v-model="endDate"
                  class="form-control form-control-sm"
                  :min="startDate"
                  :max="today"
                  @change="applyFilters"
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Memuat data penjualan...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="alert alert-danger">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ error }}
    </div>

    <!-- Data Table -->
    <div v-else class="table-responsive">
      <table class="table table-bordered table-striped main-table">
        <thead class="table-dark">
          <tr>
            <th class="no-wrap">#</th>
            <th class="no-wrap">Kode</th>
            <th class="no-wrap">Tanggal</th>
            <th class="no-wrap">Total</th>
            <th class="no-wrap">Bayar</th>
            <th class="no-wrap">Kembali</th>
            <th class="no-wrap">Kasir</th>
            <th class="no-wrap">Status Retur</th>
            <th class="no-wrap">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredSales.length === 0">
            <td colspan="9" class="text-center text-muted py-4">
              <i class="bi bi-cart-x display-6 d-block mb-2"></i>
              <template v-if="hasActiveFilters">
                Tidak ada penjualan yang sesuai dengan filter.
              </template>
              <template v-else>
                Belum ada penjualan.
              </template>
            </td>
          </tr>

          <tr v-else v-for="(sale, index) in paginatedSales" :key="sale.id">
            <td class="no-wrap">{{ (currentPage - 1) * perPage + index + 1 }}</td>
            <td class="no-wrap">
              <strong>{{ sale.sale_code }}</strong>
              <div v-if="sale.is_returned" class="badge badge-retur mt-1">DI RETUR</div>
            </td>
            <td class="no-wrap">{{ formatDate(sale.created_at) }}</td>
            <td class="no-wrap">Rp {{ formatNumber(sale.total) }}</td>
            <td class="no-wrap">Rp {{ formatNumber(sale.paid) }}</td>
            <td class="no-wrap">Rp {{ formatNumber(sale.change) }}</td>
            <td class="no-wrap">{{ sale.user?.name || '-' }}</td>
            <td class="no-wrap">
              <div v-if="sale.is_returned">
                <span class="badge badge-danger">Sudah di-retur</span>
                <small class="d-block text-muted">
                  {{ formatDate(sale.returned_at) }}
                </small>
              </div>
              <div v-else>
                <span v-if="sale.can_return" class="badge badge-success">
                  Bisa di-retur
                </span>
                <span v-else class="badge badge-secondary">
                  Tidak bisa di-retur
                </span>
                <small class="d-block text-muted">
                  {{ sale.remaining_return_time || 'Batas waktu habis' }}
                </small>
              </div>
            </td>
            <td class="no-wrap">
              <div class="d-flex gap-1">
                <button 
                  v-if="!sale.is_returned && sale.can_return"
                  @click="confirmReturn(sale)"
                  class="btn btn-warning btn-sm"
                  title="Retur Penjualan"
                >
                  <i class="fas fa-undo me-1"></i> Retur
                </button>
                <button 
                  @click="showSaleDetail(sale)"
                  class="btn btn-info btn-sm"
                  title="Detail Penjualan"
                >
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION: Hanya tampil jika ada data -->
    <nav v-if="filteredSales.length > 0" class="mt-3">
      <div class="d-flex flex-column align-items-center">
        <ul class="pagination justify-content-center flex-wrap mb-2">
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
        <div class="text-center text-muted small">
          Menampilkan {{ startItem }}-{{ endItem }} dari {{ filteredSales.length }} penjualan
        </div>
      </div>
    </nav>

    <!-- Modal Detail Penjualan -->
    <div v-if="showDetailModal" class="modal-backdrop" @click="showDetailModal = false">
      <div class="modal-container" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Penjualan - {{ selectedSale?.sale_code }}</h5>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedSale">
              <!-- Informasi Header -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <small class="text-muted">Tanggal</small><br>
                  <strong>{{ formatDate(selectedSale.created_at) }}</strong>
                </div>
                <div class="col-md-6">
                  <small class="text-muted">Kasir</small><br>
                  <strong>{{ selectedSale.user?.name || '-' }}</strong>
                </div>
              </div>

              <!-- Daftar Item -->
              <h6 class="border-bottom pb-2 mb-3">Daftar Item:</h6>
              <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered modal-table">
                  <thead class="table-light">
                    <tr>
                      <th class="modal-th">Tipe</th>
                      <th class="modal-th">Nama Item</th>
                      <th class="modal-th text-center">Qty</th>
                      <th class="modal-th text-end">Harga</th>
                      <th class="modal-th text-center">Diskon</th>
                      <th class="modal-th text-end">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in selectedSale.items || []" :key="item.id">
                      <td class="modal-td">
                        <span v-if="item.item_type === 'product'" class="badge badge-product">Produk</span>
                        <span v-else class="badge badge-service">Jasa</span>
                      </td>
                      <td class="modal-td">
                        <div v-if="item.item_type === 'product'">
                          <strong class="item-name">{{ item.product?.name || 'Produk tidak ditemukan' }}</strong>
                          <small v-if="item.product?.sku" class="d-block text-muted item-sku">
                            SKU: {{ item.product.sku }}
                          </small>
                        </div>
                        <div v-else>
                          <strong class="item-name">{{ item.service_name || 'Jasa tidak ditemukan' }}</strong>
                          <small v-if="item.service_description" class="d-block text-muted item-desc">
                            {{ item.service_description }}
                          </small>
                        </div>
                      </td>
                      <td class="modal-td text-center">{{ item.quantity || 0 }}</td>
                      <td class="modal-td text-end">Rp {{ formatNumber(item.price || 0) }}</td>
                      <td class="modal-td text-center">
                        <span v-if="item.discount > 0" class="discount-badge">{{ item.discount }}%</span>
                        <span v-else class="text-muted">-</span>
                      </td>
                      <td class="modal-td text-end fw-bold">Rp {{ formatNumber(item.subtotal || 0) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Ringkasan Pembayaran -->
              <div class="card border-primary summary-card">
                <div class="card-header bg-primary text-white">
                  <h6 class="card-title mb-0">Ringkasan Pembayaran</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-8">
                      <div class="payment-summary">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <span>Subtotal:</span>
                          <strong>Rp {{ formatNumber(selectedSale.subtotal || 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <span>Diskon:</span>
                          <strong class="text-danger">- Rp {{ formatNumber(selectedSale.discount || 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 fs-5 fw-bold border-top pt-2">
                          <span>Total:</span>
                          <span class="text-primary">Rp {{ formatNumber(selectedSale.total || 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <span>Dibayar:</span>
                          <strong class="text-success">Rp {{ formatNumber(selectedSale.paid || 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 fs-5 fw-bold border-top pt-2">
                          <span>Kembalian:</span>
                          <span class="text-success">Rp {{ formatNumber(selectedSale.change || 0) }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 text-center">
                      <div class="status-card border rounded p-3 bg-light">
                        <div class="mb-2">
                          <small class="text-muted d-block">Status</small>
                          <span 
                            v-if="(selectedSale.paid || 0) >= (selectedSale.total || 0)" 
                            class="badge badge-lunas"
                          >
                            LUNAS
                          </span>
                          <span v-else class="badge badge-kurang">
                            KURANG
                          </span>
                        </div>
                        <div class="border-top pt-2">
                          <small class="text-muted d-block mb-1">Item Terjual</small>
                          <div class="item-counts">
                            <span class="badge badge-product-count me-1">
                              {{ (selectedSale.items || []).filter(item => item.item_type === 'product').length }} Produk
                            </span>
                            <span class="badge badge-service-count">
                              {{ (selectedSale.items || []).filter(item => item.item_type === 'service').length }} Jasa
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showDetailModal = false">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import axios from 'axios'

const props = defineProps({
  sales: {
    type: Array,
    default: () => []
  }
})

// State management
const perPage = ref(20)
const currentPage = ref(1)
const showDetailModal = ref(false)
const selectedSale = ref(null)
const loading = ref(false)
const error = ref(null)

// Filter states
const searchQuery = ref('')
const startDate = ref('')
const endDate = ref('')
const today = ref(new Date().toISOString().split('T')[0])

// Data dengan validasi
const salesData = computed(() => {
  return Array.isArray(props.sales) ? props.sales : []
})

// Filtered sales berdasarkan pencarian dan tanggal
const filteredSales = computed(() => {
  let filtered = salesData.value

  // Filter berdasarkan kode penjualan
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(sale => 
      sale.sale_code?.toLowerCase().includes(query)
    )
  }

  // Filter berdasarkan rentang tanggal
  if (startDate.value && endDate.value) {
    filtered = filtered.filter(sale => {
      const saleDate = new Date(sale.created_at).toISOString().split('T')[0]
      return saleDate >= startDate.value && saleDate <= endDate.value
    })
  }

  return filtered
})

// Check jika ada filter aktif
const hasActiveFilters = computed(() => {
  return searchQuery.value !== '' || (startDate.value !== '' && endDate.value !== '')
})

// PAGINATION: Computed properties dengan validasi
const totalPages = computed(() => {
  const total = Math.ceil(filteredSales.value.length / perPage.value)
  return total > 0 ? total : 1
})

const paginatedSales = computed(() => {
  if (filteredSales.value.length === 0) return []
  
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredSales.value.slice(start, end)
})

// PAGINATION: Visible pages dengan truncation (max 5 pages)
const visiblePages = computed(() => {
  if (totalPages.value <= 1) return [1]
  
  const pages = []
  const maxVisible = 5
  const delta = 2

  let start = Math.max(1, currentPage.value - delta)
  let end = Math.min(totalPages.value, currentPage.value + delta)

  if (currentPage.value - delta <= 1) {
    end = Math.min(totalPages.value, maxVisible)
  }

  if (currentPage.value + delta >= totalPages.value) {
    start = Math.max(1, totalPages.value - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

// PAGINATION: Pagination controls visibility
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

// PAGINATION: Pagination info
const startItem = computed(() => {
  return (currentPage.value - 1) * perPage.value + 1
})

const endItem = computed(() => {
  return Math.min(currentPage.value * perPage.value, filteredSales.value.length)
})

// Methods
const applyFilters = () => {
  currentPage.value = 1 // Reset ke halaman pertama saat filter diterapkan
  loading.value = true
  
  // Simulate loading
  setTimeout(() => {
    loading.value = false
  }, 300)
}

// PAGINATION: Methods
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const prevPage = () => goToPage(currentPage.value - 1)
const nextPage = () => goToPage(currentPage.value + 1)

// Reset to first page when component mounts or sales data changes
onMounted(() => {
  currentPage.value = 1
})

// Watch for props changes
watch(() => props.sales, () => {
  currentPage.value = 1
}, { immediate: true })

// Watch filter changes
watch([searchQuery, startDate, endDate], () => {
  currentPage.value = 1
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('id-ID', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    console.error('Error formatting date:', error)
    return '-'
  }
}

const formatNumber = (num) => {
  const number = Number(num) || 0
  return new Intl.NumberFormat('id-ID').format(number)
}

const showSaleDetail = (sale) => {
  selectedSale.value = sale
  showDetailModal.value = true
}

const confirmReturn = (sale) => {
  Swal.fire({
    title: 'Konfirmasi Retur',
    html: `
      <p>Anda akan melakukan retur untuk penjualan: <strong>${sale.sale_code}</strong></p>
      <p>Total: <strong>Rp ${formatNumber(sale.total)}</strong></p>
      <p>Stok akan dikembalikan ke sistem.</p>
      <textarea 
        id="return-reason" 
        class="form-control mt-3" 
        placeholder="Alasan retur (opsional)"
        rows="3"
      ></textarea>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Retur!',
    cancelButtonText: 'Batal',
    preConfirm: () => {
      const reason = document.getElementById('return-reason')?.value || ''
      return { reason }
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        loading.value = true
        const response = await axios.post(`/sales/${sale.id}/return`, {
          reason: result.value.reason
        })

        if (response.data.success) {
          Swal.fire('Berhasil!', response.data.message, 'success')
          router.reload()
        } else {
          Swal.fire('Gagal!', response.data.message, 'error')
        }
      } catch (error) {
        console.error('Return error:', error)
        Swal.fire('Error!', error.response?.data?.message || 'Terjadi kesalahan', 'error')
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

<style scoped>
/* Form yang minimalis dan compact - TIDAK DIUBAH */
.card {
  border: 1px solid #e3e6f0;
}

.card-body {
  padding: 0.75rem;
}

.form-control-sm {
  font-size: 0.875rem;
  padding: 0.25rem 0.5rem;
}

/* PERBAIKAN TABLE UTAMA - Hapus properti yang menyebabkan konten tersembunyi */
.main-table {
  width: 100%;
  /* HAPUS: table-layout: fixed */
}

.main-table .no-wrap {
  white-space: nowrap;
  /* HAPUS: overflow: hidden */
  /* HAPUS: text-overflow: ellipsis */
}

/* HAPUS SEMUA width kolom fixed yang menyebabkan konten terpotong */
.main-table th:nth-child(1),
.main-table td:nth-child(1),
.main-table th:nth-child(2),
.main-table td:nth-child(2),
.main-table th:nth-child(3),
.main-table td:nth-child(3),
.main-table th:nth-child(4),
.main-table td:nth-child(4),
.main-table th:nth-child(5),
.main-table td:nth-child(5),
.main-table th:nth-child(6),
.main-table td:nth-child(6),
.main-table th:nth-child(7),
.main-table td:nth-child(7),
.main-table th:nth-child(8),
.main-table td:nth-child(8),
.main-table th:nth-child(9),
.main-table td:nth-child(9) {
  /* HAPUS SEMUA width dan text-align yang fixed */
}

/* Alignment untuk kolom angka - tetap pertahankan */
.main-table td:nth-child(4),
.main-table td:nth-child(5),
.main-table td:nth-child(6) {
  text-align: right;
}

.main-table td:nth-child(9) {
  text-align: center;
}

/* PERBAIKAN MODAL TABLE - Hapus properti yang menyebabkan konten tersembunyi */
.modal-table {
  width: 100%;
  /* HAPUS: table-layout: fixed */
  margin-bottom: 0;
  font-size: 0.875rem;
}

.modal-th {
  white-space: nowrap;
  /* HAPUS: overflow: hidden */
  /* HAPUS: text-overflow: ellipsis */
  font-weight: 600;
  background-color: #f8f9fa !important;
  padding: 0.75rem 0.5rem;
  border: 1px solid #dee2e6;
}

.modal-td {
  white-space: nowrap;
  /* HAPUS: overflow: hidden */
  /* HAPUS: text-overflow: ellipsis */
  padding: 0.75rem 0.5rem;
  border: 1px solid #dee2e6;
  vertical-align: top;
}

/* HAPUS SEMUA width kolom fixed untuk modal */
.modal-table th:nth-child(1),
.modal-table td:nth-child(1),
.modal-table th:nth-child(2),
.modal-table td:nth-child(2),
.modal-table th:nth-child(3),
.modal-table td:nth-child(3),
.modal-table th:nth-child(4),
.modal-table td:nth-child(4),
.modal-table th:nth-child(5),
.modal-table td:nth-child(5),
.modal-table th:nth-child(6),
.modal-table td:nth-child(6) {
  /* HAPUS SEMUA width dan min-width */
}

/* Styling untuk konten dalam table modal - TETAP PERTAHANKAN */
.item-name {
  font-size: 0.875rem;
  line-height: 1.3;
}

.item-sku,
.item-desc {
  font-size: 0.75rem;
  line-height: 1.2;
}

.discount-badge {
  background-color: #ffc107;
  color: #000;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

/* Modal styling - TIDAK DIUBAH */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-container {
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow: hidden;
}

.modal-content {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
  border-radius: 0.5rem 0.5rem 0 0;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
  color: #2c3e50;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  background: #f8f9fa;
  border-radius: 0 0 0.5rem 0.5rem;
}

/* Ringkasan pembayaran - TIDAK DIUBAH */
.summary-card {
  border: 1px solid #4e73df;
}

.payment-summary {
  font-size: 0.95rem;
}

.status-card {
  font-size: 0.875rem;
}

.item-counts {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

/* Badge styling - TIDAK DIUBAH */
.badge-product {
  background-color: #198754 !important;
  color: white;
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
}

.badge-service {
  background-color: #0dcaf0 !important;
  color: white;
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
}

.badge-lunas {
  background-color: #198754 !important;
  color: white;
  font-size: 0.8rem;
  padding: 0.4rem 0.8rem;
}

.badge-kurang {
  background-color: #ffc107 !important;
  color: #000;
  font-size: 0.8rem;
  padding: 0.4rem 0.8rem;
}

.badge-product-count,
.badge-service-count {
  font-size: 0.7rem;
  padding: 0.3rem 0.5rem;
}

.badge-retur {
  background-color: #dc3545 !important;
  color: white;
  font-size: 0.65rem;
  padding: 0.2rem 0.4rem;
}

.badge-success {
  background-color: #198754 !important;
}

.badge-danger {
  background-color: #dc3545 !important;
}

.badge-secondary {
  background-color: #6c757d !important;
}

/* Tombol aksi dalam sel - TIDAK DIUBAH */
.d-flex.gap-1 {
  gap: 4px;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  white-space: nowrap;
}

/* Table responsive - TIDAK DIUBAH */
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* PAGINATION STYLING - TIDAK DIUBAH */
.pagination {
  flex-wrap: wrap;
  gap: 2px;
  margin-bottom: 0.5rem;
}

.page-item .page-link {
  padding: 0.5rem 0.75rem;
  font-size: 0.9rem;
  min-width: 44px;
  text-align: center;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  color: #0d6efd;
  background-color: white;
  transition: all 0.2s ease;
}

.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
  font-weight: 600;
}

.page-item:not(.disabled):not(.active) .page-link:hover {
  background-color: #e9ecef;
  border-color: #dee2e6;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-item.disabled .page-link {
  color: #6c757d;
  background-color: #f8f9fa;
  border-color: #dee2e6;
  cursor: not-allowed;
  opacity: 0.6;
}

/* Pastikan form selalu menyamping di semua ukuran - TIDAK DIUBAH */
@media (max-width: 576px) {
  .row.g-2.align-items-center {
    flex-wrap: nowrap;
    overflow-x: auto;
  }
  
  .col-sm-6,
  .col-6 {
    flex-shrink: 0;
  }
  
  .col-sm-6 {
    width: 50%;
  }
  
  .col-6 {
    width: 50%;
  }
}

/* Responsive design untuk modal - TIDAK DIUBAH */
@media (max-width: 768px) {
  .modal-container {
    max-width: 95%;
    margin: 1rem;
  }
  
  .modal-content {
    max-height: 85vh;
  }
  
  .modal-header {
    padding: 0.75rem 1rem;
  }
  
  .modal-body {
    padding: 1rem;
  }
  
  .modal-footer {
    padding: 0.75rem 1rem;
  }
  
  .modal-table {
    font-size: 0.8rem;
  }
  
  .modal-th,
  .modal-td {
    padding: 0.5rem 0.3rem;
  }
}

@media (max-width: 576px) {
  .modal-backdrop {
    padding: 0.5rem;
  }
  
  .modal-container {
    max-width: 100%;
    margin: 0.5rem;
  }
  
  .modal-table {
    font-size: 0.75rem;
  }
  
  .modal-th,
  .modal-td {
    padding: 0.4rem 0.25rem;
  }
  
  .item-name {
    font-size: 0.8rem;
  }
  
  .item-sku,
  .item-desc {
    font-size: 0.7rem;
  }
  
  .summary-card .row {
    flex-direction: column;
  }
  
  .summary-card .col-md-8,
  .summary-card .col-md-4 {
    width: 100%;
  }
  
  .status-card {
    margin-top: 1rem;
  }
}

/* Untuk layar sangat kecil - TIDAK DIUBAH */
@media (max-width: 400px) {
  .form-control-sm {
    font-size: 0.8rem;
    padding: 0.2rem 0.4rem;
  }
  
  .btn-sm {
    padding: 0.2rem 0.4rem;
    font-size: 0.7rem;
  }
  
  .main-table th,
  .main-table td {
    padding: 0.4rem 0.3rem;
    font-size: 0.8rem;
  }
  
  .modal-table {
    font-size: 0.7rem;
  }
  
  .modal-th,
  .modal-td {
    padding: 0.3rem 0.2rem;
  }
  
  .badge-product,
  .badge-service {
    font-size: 0.65rem;
    padding: 0.2rem 0.3rem;
  }
}

/* Desktop layout - TIDAK DIUBAH */
@media (min-width: 577px) {
  .col-sm-6 {
    flex: 0 0 auto;
    width: 50%;
  }
}

/* Scrollbar styling untuk modal - TIDAK DIUBAH */
.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>