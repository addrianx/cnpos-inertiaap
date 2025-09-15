<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Stok</h2>
      <Link href="/stock/adjust" class="btn btn-primary">+ Penyesuaian Stok</Link>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped no-wrap">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Catatan</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <!-- Jika kosong -->
          <tr v-if="!stocks || stocks.length === 0">
            <td colspan="5" class="text-center text-muted py-4">
              Tidak ada stok.
            </td>
          </tr>

          <!-- Jika ada data -->
          <tr v-else v-for="(s, i) in stocks" :key="s.id">
            <td>{{ i + 1 }}</td>
            <td>{{ s.product.name }}</td>
            <td>{{ s.quantity }}</td>
            <td>{{ s.note }}</td>
            <td>{{ s.type }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stocks: Array
})
</script>
