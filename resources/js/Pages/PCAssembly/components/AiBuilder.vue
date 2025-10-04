<template>
  <div class="card mb-4">
    <div class="card-header bg-primary text-white py-2">
      <h6 class="mb-0">🚀 Quick Build dengan AI</h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Budget Input -->
        <div class="col-md-4">
          <label class="form-label fw-bold">💰 Budget Total</label>
          <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input 
              v-model="aiBudget" 
              type="number" 
              class="form-control"
              placeholder="8000000"
              min="1000000"
            >
          </div>
          <small class="text-muted">Masukkan budget yang Anda miliki</small>
        </div>

        <!-- Use Case Selection -->
        <div class="col-md-4">
          <label class="form-label fw-bold">🎯 Tujuan Penggunaan</label>
          <select v-model="selectedUseCase" class="form-select">
            <option value="">Pilih tujuan...</option>
            <option value="gaming">Gaming</option>
            <option value="office">Office/Productivity</option>
            <option value="editing">Video/Photo Editing</option>
            <option value="programming">Programming/Development</option>
          </select>
          <small class="text-muted" v-if="selectedUseCase">
            {{ getUseCaseDescription(selectedUseCase) }}
          </small>
        </div>

        <!-- Generate Button -->
        <div class="col-md-4 d-flex align-items-end">
          <button 
            @click="generateAIRecommendations" 
            :disabled="!canGenerateAI"
            class="btn btn-success w-100"
          >
            <i class="fas fa-robot me-1"></i>
            {{ aiLoading ? 'Generating...' : 'Generate Rekomendasi' }}
          </button>
        </div>
      </div>

      <!-- AI Recommendations -->
      <div v-if="aiRecommendations.length > 0" class="mt-4">
        <h6 class="mb-3">🎯 Rekomendasi AI untuk Rp {{ formatPrice(aiBudget) }}:</h6>
        
        <div class="row g-3">
          <AiRecommendationCard
            v-for="(recommendation, index) in aiRecommendations"
            :key="index"
            :recommendation="recommendation"
            @add-component="emit('addComponent', $event)"
          />
        </div>

        <!-- Build Summary -->
        <div v-if="aiBuildSummary" class="mt-4 p-3 bg-light rounded">
          <div class="row text-center">
            <div class="col-4">
              <strong class="d-block text-success">Rp {{ formatPrice(aiBuildSummary.totalCost) }}</strong>
              <small class="text-muted">Total Biaya</small>
            </div>
            <div class="col-4">
              <strong class="d-block" :class="aiBuildSummary.remainingBudget >= 0 ? 'text-success' : 'text-danger'">
                Rp {{ formatPrice(Math.abs(aiBuildSummary.remainingBudget)) }}
              </strong>
              <small class="text-muted">
                {{ aiBuildSummary.remainingBudget >= 0 ? 'Sisa Budget' : 'Over Budget' }}
              </small>
            </div>
            <div class="col-4">
              <strong class="d-block text-info">{{ aiBuildSummary.componentsCount }}</strong>
              <small class="text-muted">Komponen</small>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="aiRecommendations.length > 0" class="mt-3 text-center">
          <button @click="emit('addAllComponents')" class="btn btn-success me-2">
            <i class="fas fa-bolt me-1"></i>Tambah Semua ke Rakitan
          </button>
          <button @click="clearAIRecommendations" class="btn btn-outline-secondary">
            <i class="fas fa-sync me-1"></i>Generate Ulang
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="aiLoading" class="text-center py-4">
        <i class="fas fa-robot fa-spin fa-2x text-primary mb-3"></i>
        <p class="text-muted mb-0">AI sedang menganalisis komponen...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAiRecommender } from '../composables/useAiRecommender.js'
import { useProductUtils } from '../composables/useProductUtils.js'
import AiRecommendationCard from './AiRecommendationCard.vue'

const props = defineProps({
  components: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['addComponent', 'addAllComponents'])

const { formatPrice } = useProductUtils()
const {
  aiBudget,
  selectedUseCase,
  aiRecommendations,
  aiLoading,
  canGenerateAI,
  aiBuildSummary,
  getUseCaseDescription,
  generateAIRecommendations,
  clearAIRecommendations
} = useAiRecommender(props.components)
</script>

<style scoped>
.recommendation-card {
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.recommendation-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.recommendation-card.border-success {
  border-color: #198754 !important;
  background-color: #f8fff9;
}

@media (max-width: 768px) {
  .recommendation-card {
    margin-bottom: 1rem;
  }
}
</style>