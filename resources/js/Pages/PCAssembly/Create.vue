<template>
  <AppLayout>
    <!-- Header Section dengan Block Element Terpisah -->
    <div class="page-header-block mb-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="page-title-section">
          <h1 class="h3 mb-1">🛠️ Simulasi Rakitan PC</h1>
          <p class="text-muted mb-0">Pilih komponen untuk membuat rakitan PC custom</p>
        </div>
        
        <div class="action-buttons d-flex flex-wrap gap-2">
          <Link href="/pc-assembly/history" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-history me-1"></i>History
          </Link>
          <Link href="/products" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
          </Link>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <!-- Daftar Komponen -->
      <div class="col-12 col-lg-8">
        <div class="card h-100">
          <div class="card-header bg-dark text-white py-2">
            <h6 class="mb-0">📦 Pilih Komponen</h6>
          </div>
          <div class="card-body">
            <div v-for="(products, category) in components" :key="category" class="mb-4">
              <h6 class="category-title border-bottom pb-2 mb-3">{{ category }}</h6>
              <div class="row g-2">
                <div v-for="product in products" :key="product.id" class="col-12 col-sm-6">
                  <div 
                    class="card product-card h-100"
                    :class="{ 
                      'border-primary': isSelected(product),
                      'border-danger': !getHasStock(product)
                    }"
                    @click="toggleComponent(product)"
                    style="cursor: pointer;"
                  >
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                          <h6 class="card-title mb-1 text-truncate">{{ product.name }}</h6>
                          <p class="card-text text-muted small mb-2">
                            {{ getDescription(product) }}
                            <span v-if="getDiscount(product) > 0" class="badge bg-warning text-dark ms-1">
                              -{{ getDiscount(product) }}%
                            </span>
                          </p>
                          <div class="d-flex flex-wrap gap-1 align-items-center">
                            <span class="badge bg-success">Rp {{ getFormattedPrice(product) }}</span>
                            <span v-if="getDiscount(product) > 0" class="badge bg-danger">
                              Rp {{ formatPrice(getFinalPrice(product)) }}
                            </span>
                            <span v-if="getHasStock(product)" class="badge bg-info">
                              Stok: {{ getTotalStock(product) }}
                            </span>
                            <span v-else class="badge bg-danger">Habis</span>
                          </div>
                        </div>
                        <div v-if="isSelected(product)" class="text-primary ms-2">
                          <i class="fas fa-check-circle"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="Object.keys(components).length === 0" class="text-center text-muted py-4">
              <i class="fas fa-box-open fa-2x mb-3"></i>
              <p class="mb-0">Tidak ada komponen PC tersedia.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Rakitan Saat Ini -->
      <div class="col-12 col-lg-4">
        <div class="card assembly-sidebar">
          <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0">🖥️ Rakitan Anda</h6>
          </div>
          <div class="card-body">
            <div v-if="selectedComponents.length > 0">
              <!-- Daftar Komponen Terpilih -->
              <div class="selected-components mb-3">
                <div v-for="component in selectedComponents" :key="component.id" 
                     class="selected-component-item d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                  <div class="flex-grow-1 me-2">
                    <strong class="d-block text-truncate small">{{ component.name }}</strong>
                    <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                      <small class="text-muted">Rp {{ formatPrice(getFinalPrice(component)) }}</small>
                      <span v-if="getDiscount(component) > 0" class="badge bg-success bg-opacity-25 text-success small">
                        -{{ getDiscount(component) }}%
                      </span>
                    </div>
                    <small class="text-info d-block mt-1">Stok: {{ getTotalStock(component) }}</small>
                  </div>
                  <button @click.stop="removeComponent(component.id)" 
                          class="btn btn-outline-danger btn-sm flex-shrink-0"
                          title="Hapus komponen">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              
              <!-- Total Harga -->
              <div class="total-price-section border-top pt-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                  <strong class="text-dark">Total Biaya:</strong>
                  <span class="h5 text-success mb-0">Rp {{ formatPrice(totalPrice) }}</span>
                </div>
              </div>

              <!-- Form Simpan Rakitan -->
              <div class="save-assembly-form">
                <div class="mb-3">
                  <label class="form-label small fw-bold">Nama Rakitan</label>
                  <input 
                    v-model="assemblyName" 
                    type="text" 
                    class="form-control form-control-sm" 
                    placeholder="Contoh: Gaming PC Budget 10jt"
                    required
                  >
                </div>
                <div class="mb-3">
                  <label class="form-label small fw-bold">Deskripsi (Opsional)</label>
                  <textarea 
                    v-model="assemblyDescription" 
                    class="form-control form-control-sm" 
                    rows="2" 
                    placeholder="Deskripsi rakitan..."
                  ></textarea>
                </div>
                <button 
                  @click="saveAssembly" 
                  :disabled="!canSave"
                  class="btn btn-success w-100 btn-sm"
                >
                  <i class="fas fa-save me-1"></i>Simpan Rakitan
                </button>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center text-muted py-4">
              <i class="fas fa-desktop fa-2x mb-3 text-muted"></i>
              <p class="small mb-0">Pilih komponen dari sisi kiri untuk mulai merakit</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  components: Object
})

// State
const selectedComponents = ref([])
const assemblyName = ref('')
const assemblyDescription = ref('')

// Computed
const totalPrice = computed(() => {
  return selectedComponents.value.reduce((total, component) => {
    return total + getFinalPrice(component)
  }, 0)
})

const canSave = computed(() => {
  return selectedComponents.value.length > 0 && assemblyName.value.trim() !== ''
})

// Methods
const formatPrice = (price) => {
  const numPrice = parseFloat(price)
  if (isNaN(numPrice)) {
    return '0'
  }
  return new Intl.NumberFormat('id-ID').format(numPrice)
}

const getFormattedPrice = (product) => {
  if (product.formatted_price !== undefined) return product.formatted_price
  return formatPrice(product.price || 0)
}

const getFinalPrice = (product) => {
  if (product.final_price !== undefined) return product.final_price
  
  let price = parseFloat(product.price || 0)
  const discount = getDiscount(product)
  
  if (discount > 0) {
    const discountAmount = price * (discount / 100)
    price = price - discountAmount
  }
  
  return price
}

const getDiscount = (product) => {
  return parseFloat(product.discount || 0)
}

const getTotalStock = (product) => {
  if (product.total_stock !== undefined) return product.total_stock
  if (product.stock !== undefined) return product.stock
  if (product.stocks && product.stocks.length > 0) {
    return product.stocks.reduce((sum, stock) => sum + parseInt(stock.quantity || 0), 0)
  }
  return 0
}

const getHasStock = (product) => {
  if (product.has_stock !== undefined) return product.has_stock
  return getTotalStock(product) > 0
}

const getDescription = (product) => {
  if (product.description !== undefined) return product.description
  return `SKU: ${product.sku || 'N/A'}`
}

const isSelected = (product) => {
  return selectedComponents.value.some(comp => comp.id === product.id)
}

const toggleComponent = (product) => {
  if (!getHasStock(product)) {
    Swal.fire('Stok Habis', 'Produk ini sedang tidak tersedia.', 'warning')
    return
  }

  const existingIndex = selectedComponents.value.findIndex(comp => comp.id === product.id)
  
  if (existingIndex > -1) {
    selectedComponents.value.splice(existingIndex, 1)
  } else {
    const sameCategoryIndex = selectedComponents.value.findIndex(
      comp => comp.category?.name === product.category?.name
    )
    
    if (sameCategoryIndex > -1) {
      selectedComponents.value.splice(sameCategoryIndex, 1)
    }
    
    selectedComponents.value.push(product)
  }
}

const removeComponent = (productId) => {
  selectedComponents.value = selectedComponents.value.filter(comp => comp.id !== productId)
}

const saveAssembly = () => {
  if (!canSave.value) return

  router.post('/pc-assembly', {
    name: assemblyName.value,
    description: assemblyDescription.value,
    components: selectedComponents.value,
    total_price: totalPrice.value
  }, {
    onSuccess: () => {
      selectedComponents.value = []
      assemblyName.value = ''
      assemblyDescription.value = ''
    }
  })
}
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

.category-title {
  color: #495057;
  font-weight: 600;
  font-size: 0.95rem;
}

.product-card {
  transition: all 0.2s ease;
  border: 1px solid #dee2e6;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border-color: #0d6efd;
}

.assembly-sidebar {
  position: sticky;
  top: 100px;
  max-height: calc(100vh - 140px);
  overflow-y: auto;
}

.selected-component-item {
  background: #f8f9fa;
  transition: background-color 0.2s ease;
}

.selected-component-item:hover {
  background: #e9ecef;
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
  
  .assembly-sidebar {
    position: static;
    max-height: none;
  }
  
  .product-card .card-body {
    padding: 0.75rem;
  }
}

@media (max-width: 576px) {
  .card-header h6 {
    font-size: 0.9rem;
  }
  
  .selected-component-item {
    padding: 0.5rem;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
  }
}

/* Custom scrollbar for sidebar */
.assembly-sidebar::-webkit-scrollbar {
  width: 6px;
}

.assembly-sidebar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.assembly-sidebar::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.assembly-sidebar::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>