<template>
  <AppLayout>
    <!-- Header Section dengan Block Element Terpisah -->
    <div class="page-header-block mb-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="page-title-section">
          <h1 class="h3 mb-1">🔍 Detail Rakitan PC</h1>
          <p class="text-muted mb-0">Detail lengkap simulasi rakitan PC</p>
        </div>
        
        <div class="action-buttons d-flex flex-wrap gap-2">
          <Link href="/pc-assembly/history" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-history me-1"></i>Kembali ke History
          </Link>
          <Link href="/pc-assembly" class="btn btn-primary btn-sm">
            <i class="fas fa-tools me-1"></i>Buat Rakitan Baru
          </Link>
        </div>
      </div>
    </div>

    <div v-if="assembly" class="card">
      <div class="card-header bg-dark text-white py-2">
        <h6 class="mb-0">🖥️ {{ getAssemblyName() }}</h6>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-lg-8">
            <!-- Informasi Rakitan -->
            <div class="mb-4">
              <div v-if="getAssemblyDescription()" class="mb-3">
                <strong class="text-dark">Deskripsi:</strong>
                <p class="mb-0 text-muted">{{ getAssemblyDescription() }}</p>
              </div>

              <h5 class="border-bottom pb-2 mb-3">📦 Daftar Komponen</h5>
              <div class="table-responsive">
                <table class="table table-striped table-sm">
                  <thead class="table-light">
                    <tr>
                      <th>Komponen</th>
                      <th class="text-end">Harga</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="component in getComponents()" :key="component.id">
                      <td>
                        <strong class="d-block">{{ getComponentName(component) }}</strong>
                        <small class="text-muted">{{ getComponentDescription(component) }}</small>
                        <div v-if="getComponentDiscount(component) > 0" class="mt-1">
                          <span class="badge bg-warning text-dark small">
                            Diskon {{ getComponentDiscount(component) }}%
                          </span>
                        </div>
                      </td>
                      <td class="text-end">
                        <div class="text-success fw-bold">
                          Rp {{ formatPrice(getComponentFinalPrice(component)) }}
                        </div>
                        <div v-if="getComponentDiscount(component) > 0" class="text-muted small text-decoration-line-through">
                          Rp {{ formatPrice(getComponentPrice(component)) }}
                        </div>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-success">
                    <tr>
                      <td><strong>Total Biaya</strong></td>
                      <td class="text-end">
                        <strong class="h5 text-success mb-0">
                          Rp {{ formatPrice(getTotalPrice()) }}
                        </strong>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <!-- Informasi Tambahan -->
            <div class="card">
              <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0">📊 Informasi Rakitan</h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <strong class="text-dark d-block">Dibuat Oleh:</strong>
                  <span class="text-muted">{{ getUserName() }}</span>
                </div>
                
                <div class="mb-3" v-if="getStoreName()">
                  <strong class="text-dark d-block">Toko:</strong>
                  <span class="text-muted">{{ getStoreName() }}</span>
                </div>
                
                <div class="mb-3">
                  <strong class="text-dark d-block">Tanggal Dibuat:</strong>
                  <span class="text-muted">{{ getCreatedDate() }}</span>
                </div>
                
                <div class="mb-3">
                  <strong class="text-dark d-block">Jumlah Komponen:</strong>
                  <span class="text-muted">{{ getComponentsCount() }} items</span>
                </div>

                <div class="mb-3">
                  <strong class="text-dark d-block">Status:</strong>
                  <span class="badge bg-info">Simulasi Rakitan</span>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 pt-3 border-top">
                  <Link 
                    :href="`/pc-assembly?duplicate=${assembly.id}`" 
                    class="btn btn-outline-success w-100 btn-sm mb-2"
                  >
                    <i class="fas fa-copy me-1"></i>Duplikat Rakitan
                  </Link>
                  
                  <button 
                    @click="printAssembly()" 
                    class="btn btn-outline-secondary w-100 btn-sm"
                  >
                    <i class="fas fa-print me-1"></i>Cetak Detail
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading/Error State -->
    <div v-else class="card">
      <div class="card-body text-center py-5">
        <div v-if="loading" class="text-muted">
          <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
          <p>Memuat detail rakitan...</p>
        </div>
        <div v-else class="text-danger">
          <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
          <h5>Rakitan tidak ditemukan</h5>
          <p class="text-muted">Data rakitan yang diminta tidak dapat ditemukan.</p>
          <Link href="/pc-assembly/history" class="btn btn-primary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke History
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  assembly: Object
})

const loading = ref(false)

// Methods dengan error handling
const formatPrice = (price) => {
  const numPrice = parseFloat(price)
  if (isNaN(numPrice)) {
    return '0'
  }
  return new Intl.NumberFormat('id-ID').format(numPrice)
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID')
}

// Safe getters dengan fallback
const getAssemblyName = () => {
  return props.assembly?.name || 'Rakitan Tanpa Nama'
}

const getAssemblyDescription = () => {
  return props.assembly?.description || null
}

const getComponents = () => {
  return props.assembly?.components || []
}

const getComponentsCount = () => {
  return getComponents().length
}

const getComponentName = (component) => {
  return component?.name || 'Komponen Tidak Dikenal'
}

const getComponentDescription = (component) => {
  return component?.description || component?.sku || 'Tidak ada deskripsi'
}

const getComponentPrice = (component) => {
  return component?.price || 0
}

const getComponentDiscount = (component) => {
  return component?.discount || 0
}

const getComponentFinalPrice = (component) => {
  const price = getComponentPrice(component)
  const discount = getComponentDiscount(component)
  
  if (discount > 0) {
    const discountAmount = price * (discount / 100)
    return price - discountAmount
  }
  
  return price
}

const getTotalPrice = () => {
  return props.assembly?.total_price || 0
}

const getUserName = () => {
  return props.assembly?.user?.name || 'User Tidak Dikenal'
}

const getStoreName = () => {
  return props.assembly?.store?.name || null
}

const getCreatedDate = () => {
  return formatDateTime(props.assembly?.created_at)
}

const printAssembly = () => {
  Swal.fire({
    title: 'Cetak Detail Rakitan',
    text: 'Fitur cetak akan segera tersedia.',
    icon: 'info',
    confirmButtonText: 'OK'
  })
}

// Debug data saat component mounted
onMounted(() => {
  console.log('Assembly data:', props.assembly)
  console.log('Components:', getComponents())
  console.log('User:', props.assembly?.user)
  console.log('Store:', props.assembly?.store)
})
</script>

<style scoped>
.page-header-block {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 0.5rem;
  border-left: 4px solid #0d6efd;
}

.page-title-section h1 {
  color: #2c3e50;
  font-weight: 600;
}

.action-buttons .btn {
  border-radius: 0.375rem;
  font-weight: 500;
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
}

.table td {
  vertical-align: middle;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .page-header-block {
    padding: 1rem;
  }
  
  .page-title-section h1 {
    font-size: 1.25rem;
  }
  
  .action-buttons {
    width: 100%;
    justify-content: flex-start;
  }
}

@media (max-width: 576px) {
  .card-header h6 {
    font-size: 0.9rem;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
  }
  
  .table {
    font-size: 0.875rem;
  }
}

/* Custom styling */
.bg-success {
  background-color: #198754 !important;
}

.bg-primary {
  background-color: #0d6efd !important;
}

.bg-info {
  background-color: #0dcaf0 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
}
</style>