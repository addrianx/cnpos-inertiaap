<template>
  <AppLayout>
    <h2>Edit Produk</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- SKU -->
      <div class="mb-3">
        <label class="form-label">SKU</label>
        <input v-model="form.sku" type="text" class="form-control" />
      </div>

      <!-- Nama Produk -->
      <div class="mb-3">
        <label class="form-label">Nama Produk</label>
        <input v-model="form.name" type="text" class="form-control" />
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
      </div>

      <!-- Diskon -->
      <div class="mb-3">
        <label class="form-label">Diskon (%)</label>
        <input v-model="form.discount" type="number" class="form-control" />
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
      </div>

      <!-- Tombol -->
      <button type="submit" class="btn btn-success" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        {{ loading ? 'Sedang menyimpan...' : 'Update' }}
      </button>
      <Link href="/products" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import Swal from 'sweetalert2'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  product: Object,
})

const page = usePage()

// Ambil kategori dari props
const categories = page.props.categories || []

// Form edit produk
const form = useForm({
  sku: props.product.sku,
  name: props.product.name,
  cost: props.product.cost,
  price: props.product.price,
  discount: props.product.discount,
  category_id: props.product.category_id || '',
})

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
    Swal.fire('Berhasil!', 'Produk berhasil diperbarui.', 'success')
  } catch (err) {
    console.error(err.response?.data)
    Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan.', 'error')
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
</script>
