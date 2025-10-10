<template>
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Kasir Penjualan</h2>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" @click="$emit('reset-form')">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
          </button>
          <Link href="/sales" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineEmits(['reset-form']);
</script>