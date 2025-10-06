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
          @input="handleNoteInput"
          class="form-control text-uppercase"
          rows="3"
          placeholder="ALASAN PENYESUAIAN (OPSIONAL)"
          style="text-transform: uppercase;"
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
import { nextTick } from 'vue' // ✅ Import nextTick

const props = defineProps({
  products: Array,
})

const form = useForm({
  product_id: '',
  type: 'adjustment',
  quantity: '',
  reference: '',
  note: '',
})

// Handler untuk input catatan - konversi ke uppercase
const handleNoteInput = (event) => {
  const start = event.target.selectionStart
  const end = event.target.selectionEnd
  
  // Konversi ke uppercase
  form.note = event.target.value.toUpperCase()
  
  // Restore cursor position setelah update
  nextTick(() => {
    event.target.setSelectionRange(start, end)
  })
}

const submit = () => {
  form.post('/stock/adjust', {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Penyesuaian stok berhasil disimpan.',
        icon: 'success',
        confirmButtonText: 'OK'
      }).then(() => {
        window.location.href = '/stock'
      })
    }
  })
}
</script>

<style scoped>
.text-uppercase {
  text-transform: uppercase;
}

.text-uppercase::placeholder {
  text-transform: uppercase;
  opacity: 0.7;
}
</style>