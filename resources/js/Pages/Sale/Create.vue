<template>
  <AppLayout>
    <h2>Buat Transaksi Penjualan (Mode Kasir)</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Daftar Item -->
      <div v-for="(item, index) in form.items" :key="index" class="card mb-3 p-3">
        <div class="row align-items-end">
          <!-- Produk -->
          <div class="col-md-4 mb-2">
            <label class="form-label">Produk</label>
            <select v-model="item.product_id" class="form-select">
              <option disabled value="">-- Pilih Produk --</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <!-- Jumlah -->
          <div class="col-md-2 mb-2">
            <label class="form-label">Jumlah</label>
            <input v-model.number="item.quantity" type="number" class="form-control" min="1" />
          </div>

          <!-- Diskon -->
          <div class="col-md-2 mb-2">
            <label class="form-label">Diskon (%)</label>
            <input v-model.number="item.discount" type="number" class="form-control" min="0" max="100" />
          </div>

          <!-- Subtotal -->
          <div class="col-md-2 mb-2">
            <label class="form-label">Subtotal</label>
            <div class="form-control bg-light">
              Rp {{ itemSubtotal(item).toLocaleString() }}
            </div>
          </div>

          <!-- Hapus Item -->
          <div class="col-md-2 mb-2">
            <button type="button" class="btn btn-danger w-100" @click="removeItem(index)">Hapus</button>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-primary mb-3" @click="addItem">+ Tambah Item</button>

      <!-- Total -->
      <div class="mb-3">
        <label class="form-label">Total Transaksi</label>
        <div class="form-control bg-light">
          Rp {{ totalTransaction.toLocaleString() }}
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Dibayar</label>
        <input v-model.number="form.paid" type="number" class="form-control" min="0" />
      </div>

      <div class="mb-3">
        <label class="form-label">Kembalian</label>
        <div class="form-control bg-light">
          Rp {{ change.toLocaleString() }}
        </div>
      </div>

      <button type="submit" class="btn btn-success" :disabled="form.processing">
        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
      </button>
      <Link href="/sales" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ products: Array })

const form = useForm({
  items: [{ product_id: '', quantity: 1, discount: 0 }],
  paid: 0,
})

// Tambah/hapus item
const addItem = () => form.items.push({ product_id: '', quantity: 1, discount: 0 })
const removeItem = (index) => form.items.splice(index, 1)

// Subtotal tiap item
const itemSubtotal = (item) => {
  const product = props.products.find(p => p.id === item.product_id)
  if (!product) return 0
  return product.price * item.quantity * (1 - (item.discount || 0) / 100)
}

// Total transaksi
const totalTransaction = computed(() =>
  form.items.reduce((total, item) => total + itemSubtotal(item), 0)
)

// Kembalian
const change = computed(() => Math.max(form.paid - totalTransaction.value, 0))

// Submit form
const submit = () => {
  // Siapkan items lengkap dengan price dan subtotal masing-masing
  form.items = form.items.map(item => {
    const product = props.products.find(p => p.id === item.product_id)
    if (!product) return item
    const price = product.price
    const subtotal = price * item.quantity * (1 - (item.discount || 0)/100)
    return {
      ...item,
      price,
      subtotal,
    }
  })

  // Hitung total transaksi
  form.subtotal = form.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
  form.discount = form.items.reduce((sum, item) => sum + (item.price * item.quantity * (item.discount || 0)/100), 0)
  form.total = form.subtotal - form.discount
  form.change = Math.max(form.paid - form.total, 0)

  // Kirim form
  form.post('/sales', {
    onSuccess: () => {
      alert('Transaksi berhasil disimpan!')
      form.reset()
    },
    onError: (errors) => {
      console.error(errors)
      alert('Terjadi kesalahan, periksa input Anda.')
    }
  })
}

</script>
