<template>
  <AppLayout>
    <h2>Tambah Produk</h2>

    <form @submit.prevent="submit" class="mt-3">
      <div class="mb-3">
        <label class="form-label">Kode Produk (SKU)</label>
        <input v-model="form.sku" type="text" class="form-control" />
        <div v-if="form.errors.sku" class="text-danger small">{{ form.errors.sku }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Nama Produk</label>
        <input v-model="form.name" type="text" class="form-control" />
        <div v-if="form.errors.name" class="text-danger small">{{ form.errors.name }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Harga Modal (Cost)</label>
        <input v-model="form.cost" type="number" step="0.01" class="form-control" />
        <div v-if="form.errors.cost" class="text-danger small">{{ form.errors.cost }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Harga Jual (Price)</label>
        <input v-model="form.price" type="number" step="0.01" class="form-control" />
        <div v-if="form.errors.price" class="text-danger small">{{ form.errors.price }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Diskon (%)</label>
        <input v-model="form.discount" type="number" step="0.01" min="0" max="100" class="form-control" />
        <div v-if="form.errors.discount" class="text-danger small">{{ form.errors.discount }}</div>
      </div>

      <button type="submit" class="btn btn-success">Simpan</button>
      <Link href="/products" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const form = useForm({
  sku: '',
  name: '',
  cost: '',
  price: '',
  discount: 0,
})

const submit = () => {
  form.post('/products', {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Produk berhasil disimpan.',
        icon: 'success',
        confirmButtonText: 'OK'
      })
    }
  })
}
</script>
