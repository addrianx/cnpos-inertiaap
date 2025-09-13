<template>
  <AppLayout>
    <h1>Dashboard</h1>
    <div class="row mt-4">
      <div class="col-md-4">
        <div class="card text-bg-primary">
          <div class="card-body">
            <h5 class="card-title">Total Produk</h5>
            <p class="card-text fs-3">{{ stats.products }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card text-bg-success">
          <div class="card-body">
            <h5 class="card-title">Total Stok</h5>
            <p class="card-text fs-3">{{ stats.stock }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card text-bg-warning">
          <div class="card-body">
            <h5 class="card-title">Penjualan Hari Ini</h5>
            <p class="card-text fs-3">Rp {{ stats.sales_today }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  stats: Object
})
</script>
