<template>
  <AppLayout>
    <h2>Tambah Produk</h2>

    <!-- Alert Warning dari Backend -->
    <div v-if="page.props.flash.warning" class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ page.props.flash.warning }}
      <button type="button" class="btn-close" @click="clearFlashWarning"></button>
    </div>

    <!-- Alert Success dari Backend -->
    <div v-if="page.props.flash.success && showSuccessAlert" class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>
      {{ page.props.flash.success }}
      <button type="button" class="btn-close" @click="clearFlashAndResetForm"></button>
    </div>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Kode Produk -->
      <div class="mb-3">
        <label class="form-label">Kode Produk (SKU)</label>
        <div class="input-group">
          <input 
            v-model="form.sku" 
            type="text" 
            class="form-control" 
            :class="{ 'is-valid': skuIsValid }"
            readonly
          />
          <button 
            type="button" 
            class="btn btn-outline-secondary" 
            disabled
            title="Generate SKU Baru (Tidak Tersedia)"
          >
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="form-text text-muted">
          SKU otomatis berdasarkan nama toko: {{ form.sku }}
        </div>
        <div v-if="form.errors.sku" class="text-danger small">{{ form.errors.sku }}</div>
      </div>

      <!-- Nama Produk -->
      <div class="mb-3">
        <label class="form-label">Nama Produk</label>
        <input 
          v-model="form.name" 
          @input="handleProductNameInput"
          @blur="checkSimilarProducts"
          type="text" 
          class="form-control text-uppercase" 
          :class="{ 
            'is-invalid': form.errors.name,
            'is-valid': form.name && !form.errors.name
          }"
          placeholder="MASUKKAN NAMA PRODUK"
          style="text-transform: uppercase;"
        />
        
        <!-- Loading Indicator -->
        <div v-if="checkingSimilar" class="text-info small mt-1">
          <i class="bi bi-hourglass-split me-1"></i>Mengecek produk serupa...
        </div>

        <!-- Error dari Backend (Exact Match) -->
        <div v-if="form.errors.name" class="alert alert-danger mt-2">
          <strong><i class="bi bi-x-circle me-1"></i> Error:</strong> {{ form.errors.name }}
        </div>

        <!-- Warning Produk Serupa (Kandungan Nama) -->
        <div v-else-if="hasSimilarProducts" class="alert alert-warning mt-2">
          <strong><i class="bi bi-exclamation-triangle me-1"></i> Peringatan:</strong> Produk dengan nama serupa sudah ada:
          <ul class="mb-0 mt-1">
            <li v-for="product in similarProducts" :key="product.id">
              <strong>{{ product.name }}</strong> (SKU: {{ product.sku }})
            </li>
          </ul>
          <div class="mt-2 small text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Jika yakin produk ini berbeda, Anda masih bisa menyimpannya.
          </div>
        </div>

        <!-- Info Tidak Ada Produk Serupa -->
        <div v-else-if="form.name && !checkingSimilar && similarProducts.length === 0" class="alert alert-success mt-2">
          <i class="bi bi-check-circle me-1"></i> Tidak ada produk serupa yang ditemukan.
        </div>
      </div>

      <!-- Stok Awal -->
      <div class="mb-3">
        <label class="form-label">Stok Awal</label>
        <input 
          v-model="form.initial_stock" 
          type="number" 
          min="0" 
          step="1" 
          class="form-control" 
          placeholder="Masukkan stok awal"
          :class="{ 
            'is-invalid': form.errors.initial_stock,
            'is-valid': form.initial_stock !== null && !form.errors.initial_stock
          }"
        />
        <div class="form-text text-muted">
          Stok awal akan otomatis dibuat sebagai adjustment stok dengan catatan "stok awal"
        </div>
        <div v-if="form.errors.initial_stock" class="text-danger small">{{ form.errors.initial_stock }}</div>
      </div>

      <!-- Harga Modal -->
      <div class="mb-3">
        <label class="form-label">Harga Modal (Cost)</label>
        <input
          v-model="costFormatted"
          type="text"
          class="form-control"
          placeholder="Masukkan harga modal"
        />
        <div v-if="form.errors.cost" class="text-danger small">{{ form.errors.cost }}</div>
      </div>

      <!-- Harga Jual -->
      <div class="mb-3">
        <label class="form-label">Harga Jual (Price)</label>
        <input
          v-model="priceFormatted"
          type="text"
          class="form-control"
          placeholder="Masukkan harga jual"
        />
        <div v-if="form.errors.price" class="text-danger small">{{ form.errors.price }}</div>
      </div>

      <!-- Diskon -->
      <div class="mb-3">
        <label class="form-label">Diskon (%)</label>
        <input 
          v-model="form.discount" 
          type="number" 
          step="0.01" 
          min="0" 
          max="100" 
          class="form-control" 
        />
        <div v-if="form.errors.discount" class="text-danger small">{{ form.errors.discount }}</div>
      </div>

      <!-- Kategori -->
      <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select v-model="form.category_id" class="form-select">
          <option value="" disabled>Pilih kategori</option>
          <option v-for="cat in sortedCategories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <div v-if="form.errors.category_id" class="text-danger small">{{ form.errors.category_id }}</div>
      </div>

      <!-- Tombol -->
      <button 
        type="submit" 
        class="btn btn-success" 
        :disabled="form.processing"
        :class="{ 'btn-warning': hasSimilarProducts && !form.errors.name }"
      >
        <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
        <span v-if="hasSimilarProducts && !form.errors.name">
          <i class="bi bi-exclamation-triangle me-1"></i> Simpan Meski Ada Peringatan
        </span>
        <span v-else>
          {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
        </span>
      </button>
      
      <!-- Tombol Reset Form -->
      <button 
        type="button" 
        class="btn btn-outline-secondary ms-2" 
        @click="resetForm"
        :disabled="form.processing"
      >
        <i class="bi bi-arrow-clockwise me-1"></i> Reset Form
      </button>

      <Link href="/products" class="btn btn-secondary ms-2">Kembali</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, ref, onMounted, watch, nextTick } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'

const page = usePage()

// Ambil daftar kategori dan autoSku dari props yang dikirim controller
const categories = page.props.categories || []
const autoSku = page.props.autoSku || ''

// State untuk produk serupa
const similarProducts = ref([])
const checkingSimilar = ref(false)
const showSuccessAlert = ref(true) // ✅ Kontrol tampilan alert success
let debounceTimer = null

// Form dengan initial values yang jelas
const form = useForm({
  sku: autoSku,
  name: '',
  initial_stock: 0, // ✅ TAMBAHKAN FIELD STOK AWAL
  cost: '',
  price: '',
  discount: 0,
  category_id: '',
})

// Computed untuk mengurutkan kategori berdasarkan abjad
const sortedCategories = computed(() => {
  return [...categories].sort((a, b) => {
    const nameA = (a.name || '').toLowerCase()
    const nameB = (b.name || '').toLowerCase()
    
    if (nameA < nameB) return -1
    if (nameA > nameB) return 1
    return 0
  })
})

// Computed untuk validasi SKU
const skuIsValid = computed(() => {
  return form.sku && form.sku.startsWith('SKU') && form.sku.length >= 7
})

// Computed untuk cek apakah ada produk serupa
const hasSimilarProducts = computed(() => {
  return similarProducts.value.length > 0 && !form.errors.name
})

// ✅ Watch untuk newSku dari backend - PERBAIKAN
watch(() => page.props.newSku, (newSku) => {
  if (newSku && page.props.flash.success) {
    // Reset form dengan SKU baru
    resetFormWithNewSku(newSku)
  }
})

// ✅ Watch untuk flash success - PERBAIKAN
watch(() => page.props.flash.success, (success) => {
  if (success) {
    showSuccessAlert.value = true
    // Reset form jika ada newSku
    if (page.props.newSku) {
      resetFormWithNewSku(page.props.newSku)
    }
  }
})

// ✅ Fungsi reset form dengan SKU baru
const resetFormWithNewSku = (newSku) => {
  // Reset form secara manual
  form.name = ''
  form.initial_stock = 0 // ✅ RESET STOK AWAL
  form.cost = ''
  form.price = ''
  form.discount = 0
  form.category_id = ''
  form.sku = newSku
  
  // Reset errors
  form.clearErrors()
  
  // Reset similar products
  similarProducts.value = []
  
  // Reset formatted values
  costFormatted.value = ''
  priceFormatted.value = ''
}

// ✅ FUNGSI RESET FORM - TOMBOL RESET (LANGSUNG RESET TANPA SWEETALERT)
const resetForm = () => {
  // Reset form values
  form.name = ''
  form.initial_stock = 0 // ✅ RESET STOK AWAL
  form.cost = ''
  form.price = ''
  form.discount = 0
  form.category_id = ''
  form.sku = autoSku // Kembalikan ke SKU awal
  
  // Clear errors
  form.clearErrors()
  
  // Reset similar products
  similarProducts.value = []
  
  // Reset formatted values
  costFormatted.value = ''
  priceFormatted.value = ''
  
  console.log('Form telah direset')
}

// Handler untuk input nama produk - menjaga cursor position
const handleProductNameInput = (event) => {
  const start = event.target.selectionStart
  const end = event.target.selectionEnd
  
  // Konversi ke uppercase
  form.name = event.target.value.toUpperCase()
  
  // Restore cursor position setelah update
  nextTick(() => {
    event.target.setSelectionRange(start, end)
  })

  // Reset similar products ketika user mulai mengetik lagi
  if (similarProducts.value.length > 0) {
    similarProducts.value = []
  }

  // Debounce manual untuk cek produk serupa
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    checkSimilarProducts()
  }, 800)
}

// Fungsi untuk cek produk serupa
const checkSimilarProducts = async () => {
  if (!form.name || form.name.length < 3) {
    similarProducts.value = []
    return
  }

  checkingSimilar.value = true
  
  try {
    const response = await axios.post('/products/check-similar', {
      name: form.name
    })
    
    similarProducts.value = response.data.similar_products || []
    
  } catch (error) {
    console.error('Error checking similar products:', error)
    similarProducts.value = []
  } finally {
    checkingSimilar.value = false
  }
}

// Computed untuk formatting cost
const costFormatted = computed({
  get() {
    return form.cost ? Number(form.cost).toLocaleString("id-ID") : ""
  },
  set(value) {
    const numericValue = value.replace(/\D/g, "")
    form.cost = numericValue ? parseInt(numericValue, 10) : 0
  }
})

// Computed untuk formatting price
const priceFormatted = computed({
  get() {
    return form.price ? Number(form.price).toLocaleString("id-ID") : ""
  },
  set(value) {
    const numericValue = value.replace(/\D/g, "")
    form.price = numericValue ? parseInt(numericValue, 10) : 0
  }
})

// ✅ PERUBAHAN: Submit langsung tanpa konfirmasi
const submit = () => {
  // Pastikan nama produk dalam uppercase sebelum submit
  form.name = form.name.toUpperCase()
  
  // Submit langsung tanpa konfirmasi
  submitForm()
}

// Fungsi untuk submit form
const submitForm = () => {
  form.post('/products', {
    onSuccess: () => {
      // Success akan ditangani oleh alert dari backend
    },
    onError: () => {
      if (page.props.flash.error) {
        Swal.fire({
          title: 'Gagal!',
          text: page.props.flash.error,
          icon: 'error',
          confirmButtonText: 'OK'
        })
      } else {
        Swal.fire({
          title: 'Gagal!',
          text: 'Silakan periksa kembali input Anda.',
          icon: 'error',
          confirmButtonText: 'OK'
        })
      }
    }
  })
}

// ✅ FUNGSI BARU: Reset form dan clear flash - PERBAIKAN
const clearFlashAndResetForm = () => {
  router.visit('/products/create', {
    only: [],
    replace: true,
    preserveScroll: false
  })
}

// Fungsi untuk clear flash warning saja
const clearFlashWarning = () => {
  router.reload({ only: ['flash'] })
}

// Auto set SKU saat component mounted
onMounted(() => {
  if (autoSku && !form.sku) {
    form.sku = autoSku
  }
})

// Cleanup timer ketika component unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
})
</script>