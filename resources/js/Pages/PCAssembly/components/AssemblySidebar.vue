<template>
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
            <button @click.stop="$emit('removeComponent', component.id)" 
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
              v-model="localAssemblyName" 
              type="text" 
              class="form-control form-control-sm" 
              placeholder="Contoh: Gaming PC Budget 10jt"
              required
            >
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold">Deskripsi (Opsional)</label>
            <textarea 
              v-model="localAssemblyDescription" 
              class="form-control form-control-sm" 
              rows="2" 
              placeholder="Deskripsi rakitan..."
            ></textarea>
          </div>
          <button 
            @click="handleSaveAssembly" 
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
</template>

<script setup>
import { ref, watch } from 'vue'
import { useProductUtils } from '../composables/useProductUtils.js'

const props = defineProps({
  selectedComponents: {
    type: Array,
    required: true
  },
  totalPrice: {
    type: Number,
    required: true
  },
  canSave: {
    type: Boolean,
    required: true
  },
  assemblyName: {
    type: String,
    required: true
  },
  assemblyDescription: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['removeComponent', 'saveAssembly', 'update:assemblyName', 'update:assemblyDescription'])

const { formatPrice, getFinalPrice, getDiscount, getTotalStock } = useProductUtils()

// Local state untuk two-way binding
const localAssemblyName = ref(props.assemblyName)
const localAssemblyDescription = ref(props.assemblyDescription)

// Watch untuk update parent component ketika local state berubah
watch(localAssemblyName, (newValue) => {
  emit('update:assemblyName', newValue)
})

watch(localAssemblyDescription, (newValue) => {
  emit('update:assemblyDescription', newValue)
})

// Watch untuk update local state ketika props berubah
watch(() => props.assemblyName, (newValue) => {
  localAssemblyName.value = newValue
})

watch(() => props.assemblyDescription, (newValue) => {
  localAssemblyDescription.value = newValue
})

const handleSaveAssembly = () => {
  emit('saveAssembly', {
    name: localAssemblyName.value,
    description: localAssemblyDescription.value,
    components: props.selectedComponents,
    total_price: props.totalPrice
  })
}
</script>

<style scoped>
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

@media (max-width: 768px) {
  .assembly-sidebar {
    position: static;
    max-height: none;
  }
}

@media (max-width: 576px) {
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