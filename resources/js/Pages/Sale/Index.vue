<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Penjualan</h2>
      <Link href="/sales/create" class="btn btn-primary">+ Tambah Penjualan</Link>
    </div>

    <div v-if="sales.length === 0" class="alert alert-info">
      Belum ada penjualan.
    </div>

    <div v-else class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Items</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sale in sales" :key="sale.id">
            <td>{{ sale.id }}</td>
            <td>{{ new Date(sale.created_at).toLocaleString() }}</td>
            <td>{{ sale.customer_name ?? '-' }}</td>
            <td>
              <ul class="mb-0">
                <li v-for="item in sale.items" :key="item.id">
                  {{ item.product.name }} x {{ item.quantity }} = Rp {{ Number(item.subtotal).toLocaleString() }}
                </li>
              </ul>
            </td>
            <td>Rp {{ Number(sale.total).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  sales: Array
})

// Ambil flash message langsung dari props Inertia
const flash = usePage().props.flash ?? {}
</script>
