<template>
  <AppLayout>
    <h2>Tambah Produk</h2>

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
          v-model="productName" 
          @input="handleProductNameInput"
          @blur="checkSimilarProducts"
          type="text" 
          class="form-control text-uppercase" 
          :class="{ 
            'is-invalid': hasSimilarProducts || form.errors.name,
            'is-valid': form.name && !hasSimilarProducts && !form.errors.name
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
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
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
      <Link href="/products" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'

const page = usePage()

// Ambil daftar kategori dan autoSku dari props yang dikirim controller
const categories = page.props.categories || []
const autoSku = page.props.autoSku || ''

// State untuk produk serupa
const similarProducts = ref([])
const checkingSimilar = ref(false)
let debounceTimer = null

const form = useForm({
  sku: autoSku,
  name: '',
  cost: '',
  price: '',
  discount: 0,
  category_id: '',
})

// Computed untuk validasi SKU
const skuIsValid = computed(() => {
  return form.sku && form.sku.startsWith('SKU') && form.sku.length >= 7
})

// Computed untuk cek apakah ada produk serupa
const hasSimilarProducts = computed(() => {
  return similarProducts.value.length > 0 && !form.errors.name
})

// Computed untuk nama produk dengan uppercase
const productName = computed({
  get() {
    return form.name
  },
  set(value) {
    // Otomatis konversi ke uppercase
    form.name = value.toUpperCase()
  }
})

// Handler untuk input nama produk - menjaga cursor position
const handleProductNameInput = (event) => {
  const start = event.target.selectionStart
  const end = event.target.selectionEnd
  
  // Konversi ke uppercase
  form.name = event.target.value.toUpperCase()
  
  // Restore cursor position setelah update
  setTimeout(() => {
    event.target.setSelectionRange(start, end)
  }, 0)

  // Reset similar products ketika user mulai mengetik lagi
  if (similarProducts.value.length > 0) {
    similarProducts.value = []
  }

  // Debounce manual untuk cek produk serupa
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    checkSimilarProducts()
  }, 800) // Delay 800ms setelah user berhenti mengetik
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
    
    // DEBUG: Tampilkan info lengkap di console
    console.log('=== PRODUCT SIMILARITY CHECK ===')
    console.log('Search term:', response.data.search_term)
    console.log('Search words:', response.data.search_words)
    console.log('Found products:', response.data.similar_products)
    console.log('Debug info:', response.data.debug)
    console.log('=== END CHECK ===')
    
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

const submit = () => {
  // Pastikan nama produk dalam uppercase sebelum submit
  form.name = form.name.toUpperCase()
  
  // Tampilkan konfirmasi jika ada produk serupa
  if (hasSimilarProducts.value) {
    Swal.fire({
      title: 'Konfirmasi Simpan',
      html: `
        <p>Ada produk serupa yang sudah terdaftar:</p>
        <ul>
          ${similarProducts.value.map(p => `<li><strong>${p.name}</strong> (SKU: ${p.sku})</li>`).join('')}
        </ul>
        <p>Apakah Anda yakin ingin menyimpan produk ini?</p>
      `,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ffc107',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Simpan',
      cancelButtonText: 'Batal',
    }).then((result) => {
      if (result.isConfirmed) {
        submitForm()
      }
    })
  } else {
    submitForm()
  }
}

// Fungsi untuk submit form
const submitForm = () => {
  form.post('/products', {
    onSuccess: () => {
      if (page.props.flash.success) {
        Swal.fire({
          title: 'Berhasil!',
          text: page.props.flash.success,
          icon: 'success',
          confirmButtonText: 'OK'
        })
      }
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

<style scoped>
.input-group .btn {
  border-left: 0;
}

.input-group .form-control:read-only {
  background-color: #f8f9fa;
}

.input-group .btn:disabled {
  background-color: #e9ecef;
  border-color: #dee2e6;
  opacity: 0.6;
  cursor: not-allowed;
}

.is-valid {
  border-color: #198754;
}

.is-invalid {
  border-color: #ffc107;
}

.form-text {
  font-size: 0.875rem;
}

/* Styling untuk input uppercase */
.text-uppercase {
  text-transform: uppercase;
  font-family: inherit;
}

.text-uppercase::placeholder {
  text-transform: uppercase;
  opacity: 0.7;
}

/* Styling untuk alert */
.alert-warning {
  font-size: 0.875rem;
  padding: 0.75rem;
}

.alert-warning ul {
  padding-left: 1.5rem;
}

.alert-warning li {
  margin-bottom: 0.25rem;
}

.alert-success {
  font-size: 0.875rem;
  padding: 0.75rem;
}

.alert-danger {
  font-size: 0.875rem;
  padding: 0.75rem;
}
</style>