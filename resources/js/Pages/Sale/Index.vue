<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Penjualan</h2>
      <Link href="/sales/create" class="btn btn-primary">+ Transaksi Baru</Link>
    </div>

    <div class="table-responsive">
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
          <tr v-if="sales.length === 0">
            <td colspan="9" class="text-center text-muted py-4">
              Belum ada penjualan.
            </td>
          </tr>

          <tr v-else v-for="(sale, index) in paginatedSales" :key="sale.id">
            <td class="text-nowrap">{{ (currentPage - 1) * perPage + index + 1 }}</td>
            <td class="text-nowrap">
              <strong>{{ sale.sale_code }}</strong>
              <div v-if="sale.is_returned" class="badge bg-danger mt-1">DI RETUR</div>
            </td>
            <td class="text-nowrap">{{ formatDate(sale.created_at) }}</td>
            <td class="text-nowrap">Rp {{ formatNumber(sale.total) }}</td>
            <td class="text-nowrap">Rp {{ formatNumber(sale.paid) }}</td>
            <td class="text-nowrap">Rp {{ formatNumber(sale.change) }}</td>
            <td class="text-nowrap">{{ sale.user?.name }}</td>
            <td class="text-nowrap">
              <div v-if="sale.is_returned">
                <span class="badge bg-danger">Sudah di-retur</span>
                <small class="d-block text-muted">
                  {{ formatDate(sale.returned_at) }}
                </small>
              </div>
              <div v-else>
                <span v-if="sale.can_return" class="badge bg-success">
                  Bisa di-retur
                </span>
                <span v-else class="badge bg-secondary">
                  Tidak bisa di-retur
                </span>
                <small class="d-block text-muted">
                  {{ sale.remaining_return_time }}
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

    <!-- ✅ PAGINATION: Truncated seperti di products -->
    <nav class="mt-3">
      <div class="d-flex flex-column align-items-center">
        <!-- Mobile-friendly navigation -->
        <div class="d-flex justify-content-center w-100 mb-2 d-sm-none">
          <button 
            :class="['btn', 'me-2', 'px-3', 'py-2', { 
              'btn-primary': currentPage > 1, 
              'btn-outline-secondary': currentPage === 1 
            }]"
            @click="prevPage" 
            :disabled="currentPage === 1"
          >
            <i class="bi bi-chevron-left me-1"></i> Prev
          </button>
          
          <div class="mx-2 d-flex align-items-center">
            <span class="badge bg-primary fs-6 px-3 py-2">
              {{ currentPage }}/{{ totalPages }}
            </span>
          </div>
          
          <button 
            :class="['btn', 'px-3', 'py-2', { 
              'btn-primary': currentPage < totalPages, 
              'btn-outline-secondary': currentPage === totalPages 
            }]"
            @click="nextPage" 
            :disabled="currentPage === totalPages"
          >
            Next <i class="bi bi-chevron-right ms-1"></i>
          </button>
        </div>

        <!-- Desktop pagination -->
        <ul class="pagination justify-content-center flex-wrap d-none d-sm-flex mb-2">
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
          Menampilkan {{ startItem }}-{{ endItem }} dari {{ sales.length }} penjualan
        </div>
      </div>
    </nav>

    <!-- Modal Detail Penjualan -->
    <div v-if="showDetailModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Penjualan - {{ selectedSale?.sale_code }}</h5>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedSale">
              <div class="row mb-3">
                <div class="col-md-6">
                  <strong>Tanggal:</strong> {{ formatDate(selectedSale.created_at) }}<br>
                  <strong>Kasir:</strong> {{ selectedSale.user?.name }}
                </div>
                <div class="col-md-6">
                  <strong>Subtotal:</strong> Rp {{ formatNumber(selectedSale.subtotal) }}<br>
                  <strong>Diskon:</strong> Rp {{ formatNumber(selectedSale.discount) }}<br>
                  <strong>Total:</strong> Rp {{ formatNumber(selectedSale.total) }}
                </div>
              </div>
              
              <h6>Items:</h6>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th class="text-nowrap">Produk</th>
                      <th class="text-nowrap">Qty</th>
                      <th class="text-nowrap">Harga</th>
                      <th class="text-nowrap">Diskon</th>
                      <th class="text-nowrap">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in selectedSale.items" :key="item.id">
                      <td class="text-nowrap">{{ item.product?.name }}</td>
                      <td class="text-nowrap">{{ item.quantity }}</td>
                      <td class="text-nowrap">Rp {{ formatNumber(item.price) }}</td>
                      <td class="text-nowrap">Rp {{ formatNumber(item.discount) }}</td>
                      <td class="text-nowrap">Rp {{ formatNumber(item.subtotal) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import axios from 'axios'

const props = defineProps({
  sales: Array
})

// ✅ PAGINATION: State management
const perPage = ref(20)
const currentPage = ref(1)
const showDetailModal = ref(false)
const selectedSale = ref(null)

// ✅ PAGINATION: Computed properties
const totalPages = computed(() => Math.ceil(props.sales.length / perPage.value) || 1)

const paginatedSales = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return props.sales.slice(start, end)
})

// ✅ PAGINATION: Visible pages dengan truncation (max 5 pages)
const visiblePages = computed(() => {
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

// ✅ PAGINATION: Pagination controls visibility
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

// ✅ PAGINATION: Pagination info
const startItem = computed(() => {
  return (currentPage.value - 1) * perPage.value + 1
})

const endItem = computed(() => {
  return Math.min(currentPage.value * perPage.value, props.sales.length)
})

// ✅ PAGINATION: Methods
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

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('id-ID').format(num || 0)
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
      const reason = document.getElementById('return-reason').value
      return { reason }
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
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
        Swal.fire('Error!', error.response?.data?.message || 'Terjadi kesalahan', 'error')
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

/* ✅ PAGINATION: CSS untuk pagination responsive */
.pagination {
  flex-wrap: wrap;
  gap: 4px;
}

.page-item .page-link {
  padding: 0.5rem 0.75rem;
  font-size: 0.9rem;
  min-width: 44px;
  text-align: center;
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

/* Mobile styles */
@media (max-width: 767.98px) {
  /* Hide desktop pagination on mobile */
  .pagination {
    display: none !important;
  }
  
  /* Mobile navigation buttons - BESAR untuk mobile */
  .btn {
    min-height: 48px;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.7rem 1rem !important;
  }
  
  .badge {
    min-width: 90px;
    font-size: 1rem !important;
    padding: 0.7rem 1rem !important;
  }
  
  .table-nowrap {
    min-width: 1000px;
    font-size: 0.875rem;
  }
  
  .btn-sm {
    padding: 0.4rem 0.6rem;
    font-size: 0.8rem;
    min-height: 36px;
  }
}

/* Desktop enhancement */
@media (min-width: 768px) {
  /* Hide mobile navigation on desktop */
  .d-sm-none {
    display: none !important;
  }
  
  .page-item .page-link {
    padding: 0.6rem 0.8rem;
    font-size: 0.95rem;
    min-width: 46px;
  }
  
  .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    font-weight: bold;
  }
  
  /* Hover effects for desktop */
  .page-item:not(.disabled):not(.active) .page-link:hover {
    background-color: #e9ecef;
    transform: translateY(-1px);
    transition: all 0.2s ease;
  }
}

/* Very small mobile devices */
@media (max-width: 375px) {
  .btn {
    padding: 0.6rem 0.8rem !important;
    font-size: 0.9rem;
    min-height: 46px;
  }
  
  .badge {
    font-size: 0.9rem !important;
    padding: 0.6rem 0.8rem !important;
    min-width: 80px;
  }
  
  .table-nowrap {
    min-width: 1100px;
    font-size: 0.85rem;
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