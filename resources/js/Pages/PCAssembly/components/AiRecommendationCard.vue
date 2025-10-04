<template>
  <div class="col-12 col-lg-6">
    <div class="card recommendation-card h-100"
         :class="{ 'border-success': recommendation.isOptimal }">
      <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0">{{ formatCategoryName(recommendation.category) }}</h6>
          <span class="badge" :class="recommendation.badgeClass">
            {{ recommendation.budgetPercentage }}%
          </span>
        </div>
      </div>
      <div class="card-body">
        <!-- Recommended Product -->
        <div v-if="recommendation.recommendedProduct" class="product-recommendation">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <strong class="d-block">{{ recommendation.recommendedProduct.name }}</strong>
              <small class="text-muted">{{ getDescription(recommendation.recommendedProduct) }}</small>
            </div>
            <span class="badge bg-success">
              Rp {{ formatPrice(getFinalPrice(recommendation.recommendedProduct)) }}
            </span>
          </div>
          
          <!-- Product Features -->
          <div class="product-features small text-muted">
            <div v-if="getHasStock(recommendation.recommendedProduct)" class="text-success">
              <i class="fas fa-check me-1"></i>Stok: {{ getTotalStock(recommendation.recommendedProduct) }}
            </div>
            <div v-else class="text-danger">
              <i class="fas fa-times me-1"></i>Stok habis
            </div>
          </div>
        </div>

        <!-- Alternative Products -->
        <div v-if="recommendation.alternatives.length > 0" class="alternatives-section mt-3">
          <small class="text-muted d-block mb-1">Alternatif lain:</small>
          <div v-for="alt in recommendation.alternatives.slice(0, 2)" 
               :key="alt.id"
               class="alternative-item d-flex justify-content-between align-items-center py-1 px-2 border rounded mb-1">
            <span class="small">{{ alt.name }}</span>
            <span class="badge bg-light text-dark small">
              Rp {{ formatPrice(getFinalPrice(alt)) }}
            </span>
          </div>
        </div>

        <!-- No Products Available -->
        <div v-else-if="!recommendation.recommendedProduct" class="text-center text-muted py-3">
          <i class="fas fa-exclamation-triangle"></i>
          <small class="d-block">Tidak ada produk yang sesuai</small>
        </div>
      </div>
      <div class="card-footer bg-transparent py-2">
        <button 
          v-if="recommendation.recommendedProduct"
          @click="$emit('addComponent', recommendation.recommendedProduct)"
          :disabled="!getHasStock(recommendation.recommendedProduct)"
          class="btn btn-outline-primary btn-sm w-100"
        >
          <i class="fas fa-plus me-1"></i>Tambah ke Rakitan
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useProductUtils } from '../composables/useProductUtils.js'

const props = defineProps({
  recommendation: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['addComponent'])

const { 
  formatPrice, 
  getFinalPrice, 
  getHasStock, 
  getTotalStock, 
  getDescription, 
  formatCategoryName 
} = useProductUtils()
</script>

<style scoped>
.alternative-item {
  background: #f8f9fa;
  transition: background-color 0.2s ease;
}

.alternative-item:hover {
  background: #e9ecef;
}
</style>