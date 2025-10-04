<template>
  <AppLayout>
    <!-- Header Section dengan Block Element Terpisah -->
    <div class="page-header-block mb-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="page-title-section">
          <h1 class="h3 mb-1">📋 History Rakitan PC</h1>
          <p class="text-muted mb-0">Daftar simulasi rakitan PC yang pernah dibuat</p>
        </div>
        
        <div class="action-buttons d-flex flex-wrap gap-2">
          <Link href="/pc-assembly" class="btn btn-primary btn-sm">
            <i class="fas fa-tools me-1"></i>Buat Rakitan Baru
          </Link>
          <Link href="/pc-assembly" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
          </Link>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-dark text-white py-2">
        <h6 class="mb-0">🖥️ Daftar Simulasi Rakitan</h6>
      </div>
      <div class="card-body">
        <div v-if="assemblies.length > 0">
          <div v-for="assembly in assemblies" :key="assembly.id" 
               class="assembly-item border-bottom pb-3 mb-3">
            <div class="row align-items-center">
              <div class="col-12 col-md-8">
                <div class="d-flex align-items-start mb-2">
                  <div class="flex-grow-1">
                    <h5 class="mb-1 text-dark">{{ assembly.name }}</h5>
                    <p class="text-muted small mb-2" v-if="assembly.description">
                      {{ assembly.description }}
                    </p>
                    
                    <!-- Badges -->
                    <div class="d-flex flex-wrap gap-2 mb-2">
                      <span class="badge bg-success">
                        <i class="fas fa-tag me-1"></i>Rp {{ formatPrice(assembly.total_price) }}
                      </span>
                      <span class="badge bg-secondary">
                        <i class="fas fa-microchip me-1"></i>{{ assembly.components.length }} Komponen
                      </span>
                      <span class="badge bg-info">
                        <i class="fas fa-calendar me-1"></i>{{ formatDate(assembly.created_at) }}
                      </span>
                      <span v-if="assembly.store" class="badge bg-primary">
                        <i class="fas fa-store me-1"></i>{{ assembly.store.name }}
                      </span>
                    </div>

                    <!-- Daftar Komponen -->
                    <div class="components-preview">
                      <strong class="small text-dark">Komponen:</strong>
                      <div class="d-flex flex-wrap gap-1 mt-1">
                        <span v-for="(component, index) in assembly.components.slice(0, 4)" 
                              :key="component.id"
                              class="badge bg-light text-dark border small">
                          {{ component.name }}
                        </span>
                        <span v-if="assembly.components.length > 4" 
                              class="badge bg-light text-muted border small">
                          +{{ assembly.components.length - 4 }} lainnya
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-12 col-md-4 text-start text-md-end">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-start justify-content-md-end">
                  <Link 
                    :href="`/pc-assembly/${assembly.id}`" 
                    class="btn btn-outline-primary btn-sm"
                  >
                    <i class="fas fa-eye me-1"></i>Detail
                  </Link>
                  <button 
                    v-if="canDuplicate(assembly)"
                    @click="duplicateAssembly(assembly)"
                    class="btn btn-outline-success btn-sm"
                    title="Duplikat rakitan ini"
                  >
                    <i class="fas fa-copy me-1"></i>Duplikat
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center text-muted py-5">
          <i class="fas fa-history fa-2x mb-3"></i>
          <h5 class="text-muted">Belum ada history rakitan</h5>
          <p class="mb-3">Mulai buat simulasi rakitan PC pertama Anda</p>
          <Link href="/pc-assembly" class="btn btn-primary btn-sm">
            <i class="fas fa-tools me-1"></i>Buat Rakitan Pertama
          </Link>
        </div>

        <!-- Pagination (jika ada) -->
        <div v-if="assemblies.length > 0" class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
          <div class="text-muted small">
            Menampilkan {{ assemblies.length }} rakitan
          </div>
          <div class="text-muted small">
            Terakhir diupdate: {{ formatDate(new Date()) }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  assemblies: Array
})

// Methods
const formatPrice = (price) => {
  const numPrice = parseFloat(price)
  if (isNaN(numPrice)) {
    return '0'
  }
  return new Intl.NumberFormat('id-ID').format(numPrice)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const canDuplicate = (assembly) => {
  // Cek apakah komponen masih tersedia stoknya
  return assembly.components.some(component => {
    const stock = component.stock || component.total_stock || 0
    return stock > 0
  })
}

const duplicateAssembly = (assembly) => {
  Swal.fire({
    title: 'Duplikat Rakitan?',
    html: `Anda akan membuat salinan dari <strong>"${assembly.name}"</strong>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Duplikat',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      // Redirect ke halaman create dengan data rakitan
      router.visit('/pc-assembly', {
        data: {
          duplicate: assembly.id
        }
      })
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

.assembly-item {
  transition: background-color 0.2s ease;
  padding: 1rem;
  border-radius: 0.5rem;
}

.assembly-item:hover {
  background-color: #f8f9fa;
}

.assembly-item:last-child {
  border-bottom: none !important;
  margin-bottom: 0 !important;
  padding-bottom: 0 !important;
}

.components-preview .badge {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
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
  
  .assembly-item {
    padding: 0.75rem;
  }
  
  .assembly-item h5 {
    font-size: 1.1rem;
  }
}

@media (max-width: 576px) {
  .card-header h6 {
    font-size: 0.9rem;
  }
  
  .assembly-item {
    padding: 0.5rem;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
  }
  
  .badge {
    font-size: 0.75rem;
  }
}

/* Custom styling untuk badges */
.badge {
  font-weight: 500;
}

.bg-success {
  background-color: #198754 !important;
}

.bg-secondary {
  background-color: #6c757d !important;
}

.bg-info {
  background-color: #0dcaf0 !important;
}

.bg-primary {
  background-color: #0d6efd !important;
}

.bg-light {
  background-color: #f8f9fa !important;
}
</style>