<template>
  <div class="card h-100">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
      <h6 class="mb-0">📦 Pilih Komponen</h6>
      <small class="opacity-75">
        {{ availableCategoriesCount }} kategori tersedia
      </small>
    </div>
    <div class="card-body">
      <div v-if="availableCategoriesCount > 0">
        <div v-for="(products, category) in components" :key="category" class="mb-4">
          <h6 class="category-title border-bottom pb-2 mb-3">
            {{ formatCategoryName(category) }}
            <span class="badge bg-primary ms-2">{{ products.length }}</span>
          </h6>
          <div class="row g-2">
            <div v-for="product in products" :key="product.id" class="col-12 col-sm-6">
              <!-- Product Card -->
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
      </div>

      <div v-else class="text-center text-muted py-4">
        <i class="fas fa-box-open fa-2x mb-3"></i>
        <p class="mb-2">Belum ada komponen PC yang tersedia.</p>
        <small class="text-muted">
          Tambahkan produk dengan kategori: Processor, Motherboard, RAM, dll.
        </small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useProductUtils } from '../composables/useProductUtils.js'

const props = defineProps({
  components: {
    type: Object,
    required: true
  },
  selectedComponents: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['toggleComponent'])

const { 
  formatPrice, 
  getFormattedPrice, 
  getFinalPrice, 
  getDiscount, 
  getTotalStock, 
  getHasStock, 
  getDescription, 
  formatCategoryName 
} = useProductUtils()

const isSelected = (product) => {
  return props.selectedComponents.some(comp => comp.id === product.id)
}

const toggleComponent = (product) => {
  emit('toggleComponent', product)
}

const availableCategoriesCount = computed(() => {
  return Object.keys(props.components).length
})
</script>