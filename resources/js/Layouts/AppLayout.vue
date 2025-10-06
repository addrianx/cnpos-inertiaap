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

const HIDE_DAYS = 3
const LAST_REJECT_KEY = "pwa_last_reject"

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

// Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/serviceworker.js')
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
  // Event listener untuk PWA installation prompt
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt = e

    if (canShowInstallBtn()) {
      showInstall.value = true
    }
  })

  // Event listener untuk app installed
  window.addEventListener('appinstalled', () => {
    showInstall.value = false
    deferredPrompt = null
  })

  // Check jika app sudah running sebagai PWA
  if (window.matchMedia('(display-mode: standalone)').matches) {
    showInstall.value = false
  }
})

const installApp = async () => {
  if (!deferredPrompt) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Install prompt tidak tersedia. Refresh halaman dan coba lagi.'
    })
    return
  }

  try {
    // Tampilkan prompt install
    deferredPrompt.prompt()
    
    // Tunggu user memilih
    const { outcome } = await deferredPrompt.userChoice

    if (outcome === 'accepted') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Aplikasi sedang diinstall...',
        timer: 2000
      })
    } else {
      // Simpan waktu penolakan
      localStorage.setItem(LAST_REJECT_KEY, new Date().toISOString())
      
      Swal.fire({
        icon: 'info',
        title: 'Dibatalkan',
        text: 'Anda dapat install aplikasi nanti melalui tombol install.',
        timer: 2000
      })
    }

    // Sembunyikan tombol dan reset deferredPrompt
    showInstall.value = false
    deferredPrompt = null
    
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Terjadi kesalahan saat install. Coba lagi.'
    })
  }
}
</script>

<style scoped>
/* Style tetap sama jika diperlukan */
</style>