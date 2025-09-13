<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Laporan Pembelian Stok Produk</h2>
      <button class="btn btn-primary" @click="printReport">Cetak</button>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>Kode (SKU)</th>
            <th>Nama Produk</th>
            <th>Harga Modal (Rp)</th>
            <th>Total Stok</th>
            <th>Total Modal (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>{{ product.sku }}</td>
            <td>{{ product.name }}</td>
            <td>{{ Number(product.cost).toLocaleString() }}</td>
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
              {{
                (
                  product.cost *
                  (product.stocks
                    ? product.stocks.reduce((total, s) => {
                        if (s.type === 'in') return total + s.quantity
                        if (s.type === 'out') return total - s.quantity
                        if (s.type === 'adjustment') return total + s.quantity
                        return total
                      }, 0)
                    : 0)
                ).toLocaleString()
              }}
            </td>
          </tr>
        </tbody>

        <tfoot>
          <tr class="fw-bold">
            <td colspan="4" class="text-end">Total Modal Semua Produk</td>
            <td>
              {{
                totalModal.toLocaleString()
              }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { defineProps, computed } from 'vue'

const props = defineProps({
  products: Array
})

// total modal semua produk
const totalModal = computed(() => {
  return props.products.reduce((sum, product) => {
    const totalStock = product.stocks
      ? product.stocks.reduce((total, s) => {
          if (s.type === 'in') return total + s.quantity
          if (s.type === 'out') return total - s.quantity
          if (s.type === 'adjustment') return total + s.quantity
          return total
        }, 0)
      : 0
    return sum + totalStock * product.cost
  }, 0)
})

// optional: fungsi cetak
const printReport = () => {
  window.print()
}
</script>
