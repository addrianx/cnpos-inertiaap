<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Produk</h2>
      <Link href="/products/create" class="btn btn-primary">+ Tambah Produk</Link>
    </div>

    <!-- 🔥 Table responsive + nowrap di semua kolom -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>Kode (SKU)</th>
            <th>Nama</th>
            <th>Modal</th>
            <th>Harga Jual</th>
            <th>Diskon</th>
            <th>Stok</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!products || products.length === 0">
            <td colspan="7" class="text-center text-muted py-4">
              Tidak ada produk.
            </td>
          </tr>

          <tr v-else v-for="product in products" :key="product.id">
            <td>{{ product.sku }}</td>
            <td>{{ product.name }}</td>
            <td>Rp {{ Number(product.cost).toLocaleString() }}</td>
            <td>Rp {{ Number(product.price).toLocaleString() }}</td>
            <td>{{ product.discount }}%</td>
            <td>
              {{
                product.stocks
                  ? product.stocks.reduce((total, s) => {
                      if (s.type === 'in') return total + s.quantity
                      if (s.type === 'out') return total - s.quantity
                      if (s.type === 'adjustment') return total + s.quantity
                      return total
                    }, 0)
                  : 0
              }}
            </td>
            <td>
              <Link
                :href="`/products/${product.id}/edit`"
                class="btn btn-sm btn-warning me-2"
              >
                Edit
              </Link>
              <button
                class="btn btn-sm btn-danger"
                @click="confirmDelete(product.id)"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>


<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

defineProps({
  products: Array,
})

const confirmDelete = (id) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Produk yang dihapus tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/products/${id}`, {
        onSuccess: () => {
          Swal.fire({
            title: 'Berhasil!',
            text: 'Produk berhasil dihapus.',
            icon: 'success',
            confirmButtonText: 'OK',
          })
        },
        onError: () => {
          Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus produk.',
            icon: 'error',
            confirmButtonText: 'OK',
          })
        },
      })
    }
  })
}
</script>
