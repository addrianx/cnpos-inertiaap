<template>
  <AppLayout>
    <!-- Debug Info -->
    <div v-if="showDebug" class="fixed bottom-4 right-4 bg-gray-800 text-white p-3 rounded-lg text-xs max-w-xs z-50">
      <div class="font-bold mb-2">🔧 Debug AI System</div>
      <div>Total Categories: {{ Object.keys(components).length }}</div>
      <div v-for="(products, category) in components" :key="category" class="mt-1">
        <span class="font-semibold">{{ category }}:</span> {{ products.length }} products
      </div>
      <div class="mt-2 text-yellow-300" v-if="Object.keys(components).length === 0">
        ⚠️ No components found for AI!
      </div>
    </div>

    <!-- Header -->
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
        </div>
      </div>
    </div>

    <!-- AI Builder Section -->
    <AiBuilder
      :components="components"
      @add-component="handleAddComponent"
      @add-all-components="handleAddAllComponents"
    />

    <!-- Main Content -->
    <div class="row g-3">
      <!-- Component List -->
      <div class="col-12 col-lg-8">
        <ComponentList
          :components="components"
          :selected-components="selectedComponents"
          @toggle-component="handleToggleComponent"
        />
      </div>

      <!-- Assembly Sidebar -->
      <div class="col-12 col-lg-4">
        <AssemblySidebar
          :selected-components="selectedComponents"
          :total-price="totalPrice"
          :can-save="canSave"
          :assembly-name="assemblyName"
          :assembly-description="assemblyDescription"
          @remove-component="removeComponent"
          @save-assembly="saveAssembly"
          @update:assembly-name="assemblyName = $event"
          @update:assembly-description="assemblyDescription = $event"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePcBuilder } from './composables/usePcBuilder.js'
import AiBuilder from './components/AiBuilder.vue'
import ComponentList from './components/ComponentList.vue'
import AssemblySidebar from './components/AssemblySidebar.vue'
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  components: Object
})

// Debug mode
const showDebug = ref(true)

// PC Builder Logic
const {
  selectedComponents,
  assemblyName,
  assemblyDescription,
  totalPrice,
  canSave,
  toggleComponent,
  removeComponent,
  addComponent,
  addMultipleComponents
} = usePcBuilder()

onMounted(() => {
  console.log('📦 Components data from backend:', props.components)
  
  // Check required categories
  const requiredCategories = [
    'processors', 'motherboards', 'memories', 
    'storages', 'graphics_cards', 'power_supplies', 
    'cases', 'cpu_coolers'
  ]
  
  const existingCategories = Object.keys(props.components)
  const missingCategories = requiredCategories.filter(cat => !existingCategories.includes(cat))
  
  if (missingCategories.length > 0) {
    console.warn('❌ Missing categories for AI:', missingCategories)
  } else {
    console.log('✅ All required categories available!')
  }
})

// Event Handlers
const handleToggleComponent = (product) => {
  const result = toggleComponent(product)
  
  if (result === false) {
    Swal.fire('Stok Habis', 'Produk ini sedang tidak tersedia.', 'warning')
  }
}

const handleAddComponent = (product) => {
  const success = addComponent(product)
  
  if (success) {
    Swal.fire({
      title: 'Berhasil!',
      text: `${product.name} ditambahkan ke rakitan`,
      icon: 'success',
      timer: 1500,
      showConfirmButton: false
    })
  } else {
    Swal.fire('Stok Habis', 'Produk ini sedang tidak tersedia.', 'warning')
  }
}

const handleAddAllComponents = () => {
  const availableProducts = aiRecommendations.value
    .filter(rec => rec.recommendedProduct)
    .map(rec => rec.recommendedProduct)

  if (availableProducts.length === 0) {
    Swal.fire('Tidak Ada Produk', 'Tidak ada produk yang bisa ditambahkan.', 'info')
    return
  }

  addMultipleComponents(availableProducts)

  Swal.fire({
    title: 'Rakitan Siap!',
    text: `${availableProducts.length} komponen telah ditambahkan`,
    icon: 'success',
    confirmButtonText: 'OK'
  })
}

const saveAssembly = (assemblyData) => {
  if (!canSave.value) return

  router.post('/pc-assembly', assemblyData, {
    onSuccess: () => {
      selectedComponents.value = []
      assemblyName.value = ''
      assemblyDescription.value = ''
      Swal.fire('Berhasil!', 'Rakitan berhasil disimpan.', 'success')
    },
    onError: (errors) => {
      Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan rakitan.', 'error')
    }
  })
}
</script>