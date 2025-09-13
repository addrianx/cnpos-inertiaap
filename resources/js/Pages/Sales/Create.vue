<template>
  <AppLayout>
    <h2>Buat Transaksi Penjualan</h2>

    <form @submit.prevent="submit" class="mt-3">
      <div class="mb-3">
        <label class="form-label">Produk</label>
        <select v-model="form.product_id" class="form-select">
          <option disabled value="">-- Pilih Produk --</option>
          <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Jumlah</label>
        <input v-model="form.quantity" type="number" class="form-control" />
      </div>

      <div class="mb-3">
        <label class="form-label">Diskon (%)</label>
        <input v-model="form.discount" type="number" class="form-control" />
      </div>

      <button type="submit" class="btn btn-success">Simpan</button>
      <Link href="/sales" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  products: Array,
})

const form = useForm({
  product_id: '',
  quantity: '',
  discount: 0,
})

const submit = () => {
  form.post('/sales')
}
</script>
