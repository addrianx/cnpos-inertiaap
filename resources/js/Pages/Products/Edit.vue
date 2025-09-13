<template>
  <AppLayout>
    <h2>Edit Produk</h2>

    <form @submit.prevent="submit" class="mt-3">
      <div class="mb-3">
        <label class="form-label">SKU</label>
        <input v-model="form.sku" type="text" class="form-control" />
      </div>

      <div class="mb-3">
        <label class="form-label">Nama Produk</label>
        <input v-model="form.name" type="text" class="form-control" />
      </div>

      <div class="mb-3">
        <label class="form-label">Harga Modal</label>
        <input v-model="form.cost" type="number" class="form-control" />
      </div>

      <div class="mb-3">
        <label class="form-label">Harga Jual</label>
        <input v-model="form.price" type="number" class="form-control" />
      </div>

      <div class="mb-3">
        <label class="form-label">Diskon (%)</label>
        <input v-model="form.discount" type="number" class="form-control" />
      </div>

      <button type="submit" class="btn btn-success">Update</button>
      <Link href="/products" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  product: Object,
})

const form = useForm({
  sku: props.product.sku,
  name: props.product.name,
  cost: props.product.cost,
  price: props.product.price,
  discount: props.product.discount,
})

const submit = () => {
  form.put(`/products/${props.product.id}`)
}
</script>
