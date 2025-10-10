<template>
  <div class="card">
    <div class="card-body">
      <h6 class="card-title">Info Cepat</h6>
      <ul class="list-unstyled small">
        <li class="mb-1">
          <i class="bi bi-info-circle text-primary me-1"></i>
          Total Item: <strong>{{ items.length }}</strong>
        </li>
        <li class="mb-1">
          <i class="bi bi-box text-success me-1"></i>
          Produk: <strong>{{ productCount }}</strong>
        </li>
        <li class="mb-1">
          <i class="bi bi-tools text-warning me-1"></i>
          Jasa: <strong>{{ serviceCount }}</strong>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: Array,
  productCount: Number,
  serviceCount: Number
});
</script>