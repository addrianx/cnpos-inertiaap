<template>
  <AppLayout>
    <h2>Edit Produk</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- SKU -->
      <div class="mb-3">
        <label class="form-label">SKU</label>
        <input 
          v-model="form.sku" 
          type="text" 
          class="form-control" 
          :class="{ 'is-valid': skuIsValid }"
        />
        <div class="form-text text-muted">
          SKU: {{ form.sku }}
        </div>
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
        <label class="form-label">Harga Modal</label>
        <input
          type="text"
          class="form-control"
          :value="formatRupiah(form.cost)"
          @input="updateCost($event)"
        />
        <div v-if="form.errors.cost" class="text-danger small">{{ form.errors.cost }}</div>
      </div>

      <!-- Harga Jual -->
      <div class="mb-3">
        <label class="form-label">Harga Jual</label>
        <input
          type="text"
          class="form-control"
          :value="formatRupiah(form.price)"
          @input="updatePrice($event)"
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
        :disabled="loading"
        :class="{ 'btn-warning': hasSimilarProducts && !form.errors.name }"
      >
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        <span v-if="hasSimilarProducts && !form.errors.name">
          <i class="bi bi-exclamation-triangle me-1"></i> Update Meski Ada Peringatan
        </span>
        <span v-else>
          {{ loading ? 'Menyimpan...' : 'Update' }}
        </span>
      </button>
      <Link href="/products" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { onMounted, ref, computed } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  product: Object,
})

const page = usePage()

// Ambil kategori dari props
const categories = page.props.categories || []

// State untuk produk serupa
const similarProducts = ref([])
const checkingSimilar = ref(false)
let debounceTimer = null

// Form edit produk
const form = useForm({
  sku: props.product.sku,
  name: props.product.name,
  cost: props.product.cost,
  price: props.product.price,
  discount: props.product.discount,
  category_id: props.product.category_id || '',
})

// Computed untuk mengurutkan kategori berdasarkan abjad
const sortedCategories = computed(() => {
  return [...categories].sort((a, b) => {
    // Handle null atau undefined names
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

  // Jangan cek jika nama produk sama dengan produk saat ini (edit mode)
  if (form.name.toUpperCase() === props.product.name.toUpperCase()) {
    similarProducts.value = []
    return
  }

  checkingSimilar.value = true
  
  try {
    const response = await axios.post('/products/check-similar', {
      name: form.name,
      exclude_id: props.product.id // Exclude produk saat ini dari pengecekan
    })
    
    similarProducts.value = response.data.similar_products || []
    
  } catch (error) {
    console.error('Error checking similar products:', error)
    similarProducts.value = []
  } finally {
    checkingSimilar.value = false
  }
}

// Format ke Rupiah
const formatRupiah = (value) => {
  if (!value) return ''
  return new Intl.NumberFormat('id-ID').format(value)
}

// Update cost
const updateCost = (e) => {
  const raw = e.target.value.replace(/\D/g, '') // ambil angka saja
  form.cost = Number(raw)
  e.target.value = formatRupiah(raw)
}

// Update price
const updatePrice = (e) => {
  const raw = e.target.value.replace(/\D/g, '')
  form.price = Number(raw)
  e.target.value = formatRupiah(raw)
}

const loading = ref(false)
const submit = async () => {
  // Pastikan nama produk dalam uppercase sebelum submit
  form.name = form.name.toUpperCase()
  
  // Tampilkan konfirmasi jika ada produk serupa
  if (hasSimilarProducts.value) {
    Swal.fire({
      title: 'Konfirmasi Update',
      html: `
        <p>Ada produk serupa yang sudah terdaftar:</p>
        <ul>
          ${similarProducts.value.map(p => `<li><strong>${p.name}</strong> (SKU: ${p.sku})</li>`).join('')}
        </ul>
        <p>Apakah Anda yakin ingin mengupdate produk ini?</p>
      `,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ffc107',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Update',
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
const submitForm = async () => {
  loading.value = true
  try {
    const payload = {
      _method: 'put',
      sku: form.sku,
      name: form.name,
      cost: form.cost,
      price: form.price,
      discount: form.discount,
      category_id: form.category_id,
    }

    await axios.post(`/products/${props.product.id}`, payload)
    
    Swal.fire({
      title: 'Berhasil!',
      text: 'Produk berhasil diperbarui.',
      icon: 'success',
      confirmButtonText: 'OK'
    }).then(() => {
      window.location.href = '/products'
    })
    
  } catch (err) {
    console.error('Error updating product:', err.response?.data)
    
    Swal.fire({
      title: 'Gagal!',
      text: err.response?.data?.message || 'Terjadi kesalahan saat memperbarui produk.',
      icon: 'error',
      confirmButtonText: 'OK'
    })
  } finally {
    loading.value = false
  }
}

// ⚡ Reset form saat mount untuk mencegah data tersimpan dari bfcache
onMounted(() => {
  form.reset({
    sku: props.product.sku,
    name: props.product.name,
    cost: props.product.cost,
    price: props.product.price,
    discount: props.product.discount,
    category_id: props.product.category_id || '',
  })
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

.btn-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #e0a800;
  border-color: #d39e00;
  color: #212529;
}
</style>