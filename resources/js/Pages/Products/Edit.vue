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
        <input v-model="form.cost" type="number" class="form-control" />
      </div>

      <!-- Harga Jual -->
      <div class="mb-3">
        <label class="form-label">Harga Jual</label>
        <input v-model="form.price" type="number" class="form-control" />
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
      <button type="submit" class="btn btn-success">Update</button>
      <Link href="/products" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { onMounted } from 'vue'
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

// Submit update
const submit = () => {
  form.put(`/products/${props.product.id}`)
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
