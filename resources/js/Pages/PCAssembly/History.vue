<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>📋 History Rakitan PC</h2>
      <div>
        <Link href="/pc-assembly" class="btn btn-primary me-2">
          🛠️ Buat Rakitan Baru
        </Link>
        <Link href="/products" class="btn btn-secondary">
          ← Kembali
        </Link>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="assemblies.length > 0">
          <div v-for="assembly in assemblies" :key="assembly.id" class="border-bottom pb-3 mb-3">
            <div class="row">
              <div class="col-md-8">
                <h5>{{ assembly.name }}</h5>
                <p class="text-muted mb-2" v-if="assembly.description">{{ assembly.description }}</p>
                <div class="d-flex gap-2 mb-2">
                  <span class="badge bg-success">Total: Rp {{ formatPrice(assembly.total_price) }}</span>
                  <span class="badge bg-secondary">{{ assembly.components.length }} Komponen</span>
                  <span class="badge bg-info">{{ formatDate(assembly.created_at) }}</span>
                </div>
                <div class="small">
                  <strong>Komponen:</strong>
                  <span class="text-muted">
                    {{ assembly.components.map(c => c.name).join(', ') }}
                  </span>
                </div>
              </div>
              <div class="col-md-4 text-end">
                <Link 
                  :href="`/pc-assembly/${assembly.id}`" 
                  class="btn btn-outline-primary btn-sm"
                >
                  <i class="fas fa-eye me-1"></i> Detail
                </Link>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center text-muted py-5">
          <i class="fas fa-history fa-3x mb-3"></i>
          <p>Belum ada history rakitan.</p>
          <Link href="/pc-assembly" class="btn btn-primary">
            Buat Rakitan Pertama
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  assemblies: Array
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID')
}
</script>