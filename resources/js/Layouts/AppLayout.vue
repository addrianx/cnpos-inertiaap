<template>
  <div>
    <!-- Navbar -->
    <Navbar />

    <div class="container mt-4">
      <slot />
      <div
      v-if="isLoading"
      class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center"
      style="z-index: 2000;"
    >
      <div class="spinner-border text-light" style="width: 3rem; height: 3rem;"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navbar from './Navbar.vue'
import { usePage } from '@inertiajs/vue3'
import { isLoading } from '@/Stores/useLoading'
import Swal from 'sweetalert2'

const page = usePage()

if (page.props.flash.success) {
  Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: page.props.flash.success
  })
}

if (page.props.flash.error) {
  Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: page.props.flash.error
  })
}

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/serviceworker.js')
      .then(reg => console.log('SW registered', reg))
      .catch(err => console.log('SW failed', err));
  });
}
</script>
