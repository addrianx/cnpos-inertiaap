<template>
  <AppLayout>
    <h2>Transfer Stok ke Toko Lain</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Produk -->
      <div class="mb-3">
        <label class="form-label">Produk</label>
        <select v-model="form.product_id" class="form-select">
          <option disabled value="">-- Pilih Produk --</option>
          <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
      </div>

      <!-- Toko Tujuan -->
      <div class="mb-3">
        <label class="form-label">Kirim ke Toko</label>
        <select v-model="form.to_store_id" class="form-select">
          <option disabled value="">-- Pilih Toko --</option>
          <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>

      <!-- Jumlah -->
      <div class="mb-3">
        <label class="form-label">Jumlah Barang</label>
        <input v-model="form.quantity" type="number" class="form-control" min="1" />
      </div>

      <!-- Referensi -->
      <div class="mb-3">
        <label class="form-label">Referensi</label>
        <input
          v-model="form.reference"
          type="text"
          class="form-control"
          placeholder="No. dokumen / faktur (opsional)"
        />
      </div>

      <!-- Catatan -->
      <div class="mb-3">
        <label class="form-label">Catatan</label>
        <textarea
          v-model="form.note"
          class="form-control"
          rows="3"
          placeholder="Alasan transfer (opsional)"
        ></textarea>
      </div>

      <button type="submit" class="btn btn-success">Kirim</button>
      <Link href="/stock-transfers" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  products: Array, // produk milik toko user login
  stores: Array,   // daftar toko tujuan
})

const form = useForm({
  product_id: '',
  to_store_id: '',
  quantity: 1,
  reference: '',
  note: '',
})

const submit = () => {
  form.post('/stock-transfers/transfer', {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Transfer stok berhasil disimpan.',
        icon: 'success',
        confirmButtonText: 'OK'
      }).then(() => {
        window.location.href = '/stock-transfers'
      })
    },
    onError: (errors) => {
      Swal.fire({
        title: 'Gagal!',
        html: Object.values(errors).join('<br>'),
        icon: 'error',
        confirmButtonText: 'OK'
      })
    }
  })
}
</script>
