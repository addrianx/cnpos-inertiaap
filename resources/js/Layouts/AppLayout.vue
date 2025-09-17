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
import { usePage } from '@inertiajs/vue3'
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
</script>
