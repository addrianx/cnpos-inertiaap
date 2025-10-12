<template>
  <div>
    <!-- ✅ Gunakan Inertia Head untuk update title -->
    <Head>
      <title>{{ fullTitle }}</title>
    </Head>

    <!-- Navbar -->
    <Navbar />

    <div class="container my-4">
      <slot />

      <!-- Overlay Loading -->
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
import { ref, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import Navbar from './Navbar.vue'
import { isLoading } from '@/Stores/useLoading'
import Swal from 'sweetalert2'

const page = usePage()

// ✅ Computed properties untuk title
const fullTitle = computed(() => {
  const title = page.props.title
  const appName = page.props.appName || 'CNPOS'
  return title === appName ? title : `${title} - ${appName}`
})

const appName = computed(() => page.props.appName || 'CNPOS')

// SweetAlert notifications
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

<style scoped>
/* Style tetap sama jika diperlukan */
</style>