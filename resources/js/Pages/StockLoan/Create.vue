<template>
  <AppLayout>
    <h2>Pinjam Stok dari Toko Lain</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Toko Pemberi -->
      <div class="mb-3">
        <label class="form-label">Toko Pemberi</label>
        <select v-model="form.from_store_id" @change="loadProducts" class="form-select">
          <option disabled value="">-- Pilih Toko --</option>
          <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>

      <!-- Produk (muncul setelah toko dipilih) -->
      <div class="mb-3" v-if="products.length > 0">
        <label class="form-label">Produk</label>
        <select v-model="form.items[0].product_id" class="form-select">
          <option disabled value="">-- Pilih Produk --</option>
          <option v-for="p in products" :key="p.id" :value="p.id">
            {{ p.name }} (Stok: {{ p.stock }})
          </option>
        </select>
      </div>

      <!-- Jumlah -->
      <div class="mb-3" v-if="form.items[0].product_id">
        <label class="form-label">Jumlah Barang</label>
        <input
          v-model.number="form.items[0].quantity"
          @blur="validateQuantity"
          type="number"
          min="1"
          class="form-control"
        />
        <small class="text-muted">
          Maksimal: {{ selectedProduct?.stock ?? 0 }} item
        </small>
      </div>

      <!-- Tanggal Jatuh Tempo -->
      <div class="mb-3">
        <label class="form-label">Tanggal Jatuh Tempo</label>
        <input v-model="form.due_date" type="date" class="form-control" />
      </div>

      <!-- Referensi -->
      <div class="mb-3">
        <label class="form-label">Referensi</label>
        <input v-model="form.reference" type="text" class="form-control" placeholder="No. dokumen / faktur (opsional)" />
      </div>

      <!-- Catatan -->
      <div class="mb-3">
        <label class="form-label">Catatan</label>
        <textarea v-model="form.notes" class="form-control" rows="3" placeholder="Alasan pinjam stok (opsional)"></textarea>
      </div>

      <button type="submit" class="btn btn-success">Ajukan Pinjaman</button>
      <Link href="/stock-loan" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  stores: Array,
  my_store: Object
})

const form = useForm({
  loan_date: new Date().toISOString().split('T')[0],
  from_store_id: '',
  to_store_id: props.my_store?.id ?? '',
  reference: '',
  notes: '',
  due_date: '',
  items: [{ product_id: '', quantity: 1 }]
})

const products = ref([])

const loadProducts = async () => {
  if (!form.from_store_id) return
  const res = await fetch(`/stock-loan/products/${form.from_store_id}`)
  products.value = await res.json()
  form.items[0].product_id = ''
  form.items[0].quantity = 1
}

const selectedProduct = computed(() => {
  return products.value.find(p => p.id === form.items[0].product_id) || null
})

const validateQuantity = () => {
  if (selectedProduct.value && form.items[0].quantity > selectedProduct.value.stock) {
    form.items[0].quantity = selectedProduct.value.stock
    Swal.fire({
      icon: 'warning',
      title: 'Jumlah melebihi stok!',
      text: `Stok tersedia hanya ${selectedProduct.value.stock} item.`,
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
