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

      <!-- Tombol Install PWA -->
      <div class="d-flex justify-content-center my-4" v-if="showInstall">
        <button 
          @click="installApp" 
          class="btn btn-primary btn-lg shadow-sm rounded-pill px-4"
        >
          <i class="bi bi-download me-2"></i> Install Aplikasi
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import Navbar from './Navbar.vue'
import { isLoading } from '@/Stores/useLoading'
import Swal from 'sweetalert2'

const page = usePage()
const showInstall = ref(false)
let deferredPrompt

const HIDE_DAYS = 3 // berapa hari tombol disembunyikan setelah user menolak
const LAST_REJECT_KEY = "pwa_last_reject"

// ✅ Computed properties untuk title
const fullTitle = computed(() => {
  const title = page.props.title
  const appName = page.props.appName || 'CNPOS'
  return title === appName ? title : `${title} - ${appName}`
})

const appName = computed(() => page.props.appName || 'CNPOS')

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
      .catch(err => console.log('SW failed', err))
  })
}

// fungsi cek apakah boleh menampilkan tombol install
function canShowInstallBtn() {
  const lastReject = localStorage.getItem(LAST_REJECT_KEY)
  if (!lastReject) return true
  const lastRejectTime = new Date(lastReject).getTime()
  const now = Date.now()
  const diffDays = (now - lastRejectTime) / (1000 * 60 * 60 * 24)
  return diffDays >= HIDE_DAYS
}

onMounted(() => {
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt = e

    if (canShowInstallBtn()) {
      showInstall.value = true
    }
  })
})

const installApp = async () => {
  if (!deferredPrompt) return

  deferredPrompt.prompt()
  const { outcome } = await deferredPrompt.userChoice
  console.log('User response:', outcome)

  if (outcome === 'accepted') {
    console.log('User menerima install')
  } else {
    console.log('User menolak install')
    localStorage.setItem(LAST_REJECT_KEY, new Date().toISOString())
  }

  showInstall.value = false
  deferredPrompt = null
}
</script>