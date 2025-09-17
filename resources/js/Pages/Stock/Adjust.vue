<template>
  <AppLayout>
    <h2>Penyesuaian Stok</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Produk -->
      <div class="mb-3">
        <label class="form-label">Produk</label>
        <select v-model="form.product_id" class="form-select">
          <option disabled value="">-- Pilih Produk --</option>
          <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
      </div>

      <!-- Jumlah -->
      <div class="mb-3">
        <label class="form-label">Jumlah Penyesuaian</label>
        <input v-model="form.quantity" type="number" class="form-control" />
        <small class="text-muted">
          Gunakan angka positif (+) untuk menambah, negatif (-) untuk mengurangi
        </small>
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
          placeholder="Alasan penyesuaian (opsional)"
        ></textarea>
      </div>

      <button type="submit" class="btn btn-success">Simpan</button>
      <Link href="/stock" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  products: Array,
})

const form = useForm({
  product_id: '',
  type: 'adjustment', // default sesuai model
  quantity: '',
  reference: '',
  note: '',
})

const submit = () => {
  form.post('/stock/adjust', {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Penyesuaian stok berhasil disimpan.',
        icon: 'success',
        confirmButtonText: 'OK'
      }).then(() => {
        // opsional redirect ke halaman daftar stok
        window.location.href = '/stock'
      })
    }
  })
}
</script>

