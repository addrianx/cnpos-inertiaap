<template>
  <div>
    <!-- Navbar -->
    <Navbar />

    <div class="container mt-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
import Navbar from './Navbar.vue'
</script>
