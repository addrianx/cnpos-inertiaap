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

          <tr v-else v-for="(sale, index) in sales" :key="sale.id">
            <td class="text-nowrap">{{ index + 1 }}</td>
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import axios from 'axios'

const props = defineProps({
  sales: Array
})

const showDetailModal = ref(false)
const selectedSale = ref(null)

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
  min-width: 800px; /* Minimum width untuk table */
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Optimasi untuk mobile */
@media (max-width: 768px) {
  .table-nowrap {
    min-width: 1000px; /* Lebih lebar untuk mobile */
    font-size: 0.875rem;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
  }
  
  .badge {
    font-size: 0.7rem;
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
</style>