<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Penjualan</h2>
      <Link href="/sales/create" class="btn btn-primary">+ Transaksi Baru</Link>
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
      <table class="table table-bordered table-striped table-nowrap">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Kode</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Bayar</th>
            <th>Kembali</th>
            <th>Kasir</th>
            <th>Status Retur</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="salesData.length === 0">
            <td colspan="9" class="text-center text-muted py-4">
              <i class="bi bi-cart-x display-6 d-block mb-2"></i>
              Belum ada penjualan.
            </td>
          </tr>

          <tr v-else v-for="(sale, index) in paginatedSales" :key="sale.id">
            <td class="text-nowrap">{{ (currentPage - 1) * perPage + index + 1 }}</td>
            <td class="text-nowrap">
              <strong>{{ sale.sale_code }}</strong>
              <div v-if="sale.is_returned" class="badge badge-retur mt-1">DI RETUR</div>
            </td>
            <td class="text-nowrap">{{ formatDate(sale.created_at) }}</td>
            <td class="text-nowrap">Rp {{ formatNumber(sale.total) }}</td>
            <td class="text-nowrap">Rp {{ formatNumber(sale.paid) }}</td>
            <td class="text-nowrap">Rp {{ formatNumber(sale.change) }}</td>
            <td class="text-nowrap">{{ sale.user?.name || '-' }}</td>
            <td class="text-nowrap">
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
            <td class="text-nowrap">
              <button 
                v-if="!sale.is_returned && sale.can_return"
                @click="confirmReturn(sale)"
                class="btn btn-warning btn-sm"
                title="Retur Penjualan"
              >
                <i class="fas fa-undo me-1"></i> Retur
              </button>
              <span v-else-if="sale.is_returned" class="text-muted">-</span>
              <span v-else class="text-muted">-</span>
              
              <!-- Tombol detail -->
              <button 
                @click="showSaleDetail(sale)"
                class="btn btn-info btn-sm ms-1"
                title="Detail Penjualan"
              >
                <i class="fas fa-eye"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION: Hanya tampil jika ada data -->
    <nav v-if="salesData.length > 0" class="mt-3">
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
          Menampilkan {{ startItem }}-{{ endItem }} dari {{ salesData.length }} penjualan
        </div>
      </div>
    </nav>

    <!-- Modal Detail Penjualan -->
    <div v-if="showDetailModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
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
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th class="text-nowrap">Tipe</th>
                      <th class="text-nowrap">Nama Item</th>
                      <th class="text-nowrap">Qty</th>
                      <th class="text-nowrap">Harga</th>
                      <th class="text-nowrap">Diskon</th>
                      <th class="text-nowrap">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in selectedSale.items || []" :key="item.id">
                      <td class="text-nowrap">
                        <span v-if="item.item_type === 'product'" class="badge badge-product">Produk</span>
                        <span v-else class="badge badge-service">Jasa</span>
                      </td>
                      <td class="text-nowrap">
                        <div v-if="item.item_type === 'product'">
                          <strong>{{ item.product?.name || 'Produk tidak ditemukan' }}</strong>
                          <small v-if="item.product?.sku" class="d-block text-muted">
                            SKU: {{ item.product.sku }}
                          </small>
                        </div>
                        <div v-else>
                          <strong>{{ item.service_name || 'Jasa tidak ditemukan' }}</strong>
                          <small v-if="item.service_description" class="d-block text-muted">
                            {{ item.service_description }}
                          </small>
                        </div>
                      </td>
                      <td class="text-nowrap text-center">{{ item.quantity || 0 }}</td>
                      <td class="text-nowrap">Rp {{ formatNumber(item.price || 0) }}</td>
                      <td class="text-nowrap">
                        <span v-if="item.discount > 0">{{ item.discount }}%</span>
                        <span v-else class="text-muted">-</span>
                      </td>
                      <td class="text-nowrap fw-bold">Rp {{ formatNumber(item.subtotal || 0) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Ringkasan Pembayaran -->
              <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                  <h6 class="card-title mb-0">Ringkasan Pembayaran</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-8">
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
                    <div class="col-md-4 text-center">
                      <div class="border rounded p-3 bg-light">
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
                          <div>
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
    default: () => [] // Default empty array
  }
})

// State management
const perPage = ref(20)
const currentPage = ref(1)
const showDetailModal = ref(false)
const selectedSale = ref(null)
const loading = ref(false)
const error = ref(null)

// Data dengan validasi
const salesData = computed(() => {
  return Array.isArray(props.sales) ? props.sales : []
})

// PAGINATION: Computed properties dengan validasi
const totalPages = computed(() => {
  const total = Math.ceil(salesData.value.length / perPage.value)
  return total > 0 ? total : 1
})

const paginatedSales = computed(() => {
  if (salesData.value.length === 0) return []
  
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return salesData.value.slice(start, end)
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
  return Math.min(currentPage.value * perPage.value, salesData.value.length)
})

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
  console.log('Sales data loaded:', salesData.value)
})

// Watch for props changes
watch(() => props.sales, (newSales) => {
  console.log('Sales props updated:', newSales)
  currentPage.value = 1 // Reset to first page when data changes
}, { immediate: true })

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
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

/* ✅ Tambahan CSS untuk table no-wrap */
.table-nowrap {
  white-space: nowrap;
  min-width: 800px;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* ========================= */
/* ✅ PAGINATION: SAMA UNTUK SEMUA SCREEN */
/* ========================= */
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

/* ========================= */
/* ✅ BADGE SIZE FIX */
/* ========================= */

/* Badge untuk tipe item di tabel */
.badge-product,
.badge-service {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
  font-weight: 500;
}

.badge-product {
  background-color: #198754 !important;
  color: white;
}

.badge-service {
  background-color: #0dcaf0 !important;
  color: white;
}

/* Badge untuk status retur di tabel utama */
.badge-retur {
  font-size: 0.65rem;
  padding: 0.2rem 0.4rem;
  background-color: #dc3545 !important;
  color: white;
}

.badge-success {
  font-size: 0.75rem;
  padding: 0.3rem 0.5rem;
}

.badge-danger {
  font-size: 0.75rem;
  padding: 0.3rem 0.5rem;
}

.badge-secondary {
  font-size: 0.75rem;
  padding: 0.3rem 0.5rem;
}

/* Badge di modal ringkasan */
.badge-lunas,
.badge-kurang {
  font-size: 0.75rem;
  padding: 0.3rem 0.6rem;
  font-weight: 500;
}

.badge-lunas {
  background-color: #198754 !important;
  color: white;
}

.badge-kurang {
  background-color: #ffc107 !important;
  color: #000;
}

.badge-product-count,
.badge-service-count {
  font-size: 0.7rem;
  padding: 0.25rem 0.4rem;
  margin: 0.1rem;
}

.badge-product-count {
  background-color: #198754 !important;
  color: white;
}

.badge-service-count {
  background-color: #0dcaf0 !important;
  color: white;
}

/* ========================= */
/* RESPONSIVE DESIGN */
/* ========================= */

/* Mobile styles - Pagination adjustment */
@media (max-width: 767.98px) {
  .pagination {
    gap: 1px;
  }
  
  .page-item .page-link {
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
    min-width: 40px;
  }
  
  /* Badge di mobile - LEBIH KECIL */
  .badge {
    font-size: 0.65rem !important;
    padding: 0.2rem 0.4rem !important;
  }
  
  .badge-product,
  .badge-service {
    font-size: 0.6rem;
    padding: 0.15rem 0.3rem;
  }
  
  .badge-retur {
    font-size: 0.55rem;
    padding: 0.15rem 0.3rem;
  }
  
  .badge-lunas,
  .badge-kurang {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
  }
  
  .badge-product-count,
  .badge-service-count {
    font-size: 0.6rem;
    padding: 0.15rem 0.3rem;
    margin: 0.05rem;
  }
  
  .table-nowrap {
    min-width: 1000px;
    font-size: 0.875rem;
  }
  
  .btn-sm {
    padding: 0.3rem 0.5rem;
    font-size: 0.75rem;
    min-height: 32px;
  }
}

/* Very small mobile devices */
@media (max-width: 375px) {
  .pagination {
    gap: 0;
  }
  
  .page-item .page-link {
    padding: 0.35rem 0.5rem;
    font-size: 0.8rem;
    min-width: 36px;
  }
  
  /* Badge di very small screen - EXTRA KECIL */
  .badge {
    font-size: 0.6rem !important;
    padding: 0.15rem 0.3rem !important;
  }
  
  .badge-product,
  .badge-service {
    font-size: 0.55rem;
    padding: 0.1rem 0.25rem;
  }
  
  .badge-retur {
    font-size: 0.5rem;
    padding: 0.1rem 0.25rem;
  }
  
  .table-nowrap {
    min-width: 1100px;
    font-size: 0.85rem;
  }
}

/* Desktop enhancement */
@media (min-width: 768px) {
  .page-item .page-link {
    padding: 0.6rem 0.8rem;
    font-size: 0.95rem;
    min-width: 46px;
  }
  
  /* Hover effects for desktop */
  .page-item:not(.disabled):not(.active) .page-link:hover {
    background-color: #e9ecef;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  }
}

/* Pastikan semua cell tidak wrap */
.text-nowrap {
  white-space: nowrap;
}

/* Header table tetap */
.table-dark th {
  position: sticky;
  top: 0;
  background: #343a40;
  z-index: 10;
}

/* Loading state untuk table */
.table-loading {
  opacity: 0.7;
  pointer-events: none;
}
</style>