<template>
  <AppLayout>
    <h2>Pinjam Stok dari Toko Lain</h2>

    <form @submit.prevent="submit" class="mt-3">

      <!-- Produk -->
      <div class="mb-3">
        <label class="form-label">Produk</label>
        <select v-model="form.product_id" class="form-select">
          <option disabled value="">-- Pilih Produk --</option>
          <option
            v-for="p in products"
            :key="p.id"
            :value="p.id"
          >
            {{ p.name }} (Stok tersedia: {{ p.stock }})
          </option>
        </select>
      </div>

      <!-- Toko Pemberi -->
      <div class="mb-3">
        <label class="form-label">Toko Pemberi</label>
        <select v-model="form.from_store_id" class="form-select">
          <option disabled value="">-- Pilih Toko --</option>
          <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>

      <!-- Jumlah -->
      <div class="mb-3">
        <label class="form-label">Jumlah Barang</label>
        <input
          v-model="form.quantity"
          type="number"
          class="form-control"
          min="1"
          :max="selectedProduct?.stock || 1"
          @input="validateQuantity"
        />
        <small v-if="form.product_id" class="text-muted">
          Maksimal: {{ selectedProduct?.stock }} item
        </small>
      </div>

      <!-- Tanggal Jatuh Tempo -->
      <div class="mb-3">
        <label class="form-label">Tanggal Jatuh Tempo</label>
        <input
          v-model="form.due_date"
          type="date"
          class="form-control"
        />
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
          v-model="form.notes"
          class="form-control"
          rows="3"
          placeholder="Alasan pinjam stok (opsional)"
        ></textarea>
      </div>

      <button type="submit" class="btn btn-success">Ajukan Pinjaman</button>
      <Link href="/stock-loan" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  products: Array, // produk yang tersedia
  stores: Array,   // daftar toko
})

const form = useForm({
  loan_date: new Date().toISOString().split('T')[0], // default hari ini
  from_store_id: '',
  to_store_id: '',   // tambahin supaya sesuai validasi
  reference: '',
  notes: '',
  items: [
    { product_id: '', quantity: 1 }
  ]
})

const selectedProduct = computed(() => {
  return props.products.find(p => p.id === form.product_id) || null
})

const validateQuantity = () => {
  if (selectedProduct && form.quantity > selectedProduct.stock) {
    form.quantity = selectedProduct.stock
    Swal.fire({
      icon: 'warning',
      title: 'Jumlah melebihi stok!',
      text: `Stok tersedia hanya ${selectedProduct.stock} item.`,
    })
  }
}

const submit = () => {
  form.post('/stock-loan/store', {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Pinjaman stok berhasil diajukan.',
        icon: 'success',
        confirmButtonText: 'OK'
      }).then(() => {
        window.location.href = '/stock-loans'
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
