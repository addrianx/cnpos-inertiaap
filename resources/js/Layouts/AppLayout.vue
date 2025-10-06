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

      <!-- Debug Info (Hanya di Development) -->
      <div v-if="isDevelopment" class="alert alert-info mt-3">
        <h6>🔧 PWA Debug Info:</h6>
        <p><strong>Install Button:</strong> {{ showInstall ? 'SHOWING' : 'HIDDEN' }}</p>
        <p><strong>Service Worker:</strong> {{ swStatus }}</p>
        <p><strong>Manifest:</strong> {{ manifestStatus }}</p>
        <p><strong>HTTPS:</strong> {{ isHTTPS ? 'YES' : 'NO' }}</p>
        <p><strong>Can Show:</strong> {{ canShowInstallBtn() ? 'YES' : 'NO' }}</p>
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
const swStatus = ref('Checking...')
const manifestStatus = ref('Checking...')
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

// Check environment
const isDevelopment = computed(() => {
  return window.location.hostname === 'localhost' || 
         window.location.hostname === '127.0.0.1'
})

const isHTTPS = computed(() => {
  return window.location.protocol === 'https:'
})

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

// Service Worker Registration dengan error handling
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/serviceworker.js')
      .then(reg => {
        console.log('✅ SW registered successfully:', reg)
        swStatus.value = 'Registered'
        
        // Check jika service worker aktif
        if (reg.active) {
          swStatus.value = 'Active'
        } else if (reg.installing) {
          swStatus.value = 'Installing'
        } else if (reg.waiting) {
          swStatus.value = 'Waiting'
        }
      })
      .catch(err => {
        console.log('❌ SW registration failed:', err)
        swStatus.value = `Failed: ${err.message}`
      })
  })
} else {
  swStatus.value = 'Not Supported'
}

// Check manifest accessibility
const checkManifest = async () => {
  try {
    const response = await fetch('/manifest.json')
    if (response.ok) {
      const manifest = await response.json()
      console.log('✅ Manifest loaded:', manifest)
      manifestStatus.value = 'Loaded'
      return true
    } else {
      console.log('❌ Manifest HTTP error:', response.status)
      manifestStatus.value = `HTTP ${response.status}`
      return false
    }
  } catch (error) {
    console.log('❌ Manifest fetch failed:', error)
    manifestStatus.value = `Error: ${error.message}`
    return false
  }
}

// fungsi cek apakah boleh menampilkan tombol install
function canShowInstallBtn() {
  // Di production, selalu tampilkan (kecuali user sudah reject)
  if (!isDevelopment.value) {
    const lastReject = localStorage.getItem(LAST_REJECT_KEY)
    if (!lastReject) return true
    
    const lastRejectTime = new Date(lastReject).getTime()
    const now = Date.now()
    const diffDays = (now - lastRejectTime) / (1000 * 60 * 60 * 24)
    return diffDays >= HIDE_DAYS
  }
  
  // Di development, selalu tampilkan untuk testing
  return true
}

// Check PWA requirements
const checkPWARequirements = async () => {
  console.log('🔍 Checking PWA Requirements for Installation...')
  
  const requirements = {
    https: isHTTPS.value,
    serviceWorker: 'serviceWorker' in navigator,
    manifest: await checkManifest(),
    beforeInstallPrompt: !!deferredPrompt
  }
  
  console.log('📋 PWA Installation Requirements:', requirements)
  
  // Log specific issues
  if (!requirements.https) {
    console.log('⚠️ PWA requires HTTPS in production')
  }
  if (!requirements.serviceWorker) {
    console.log('⚠️ Service Worker not supported')
  }
  if (!requirements.manifest) {
    console.log('⚠️ Manifest not accessible')
  }
  if (!requirements.beforeInstallPrompt) {
    console.log('⚠️ beforeinstallprompt event not fired')
  }
  
  return requirements
}

onMounted(async () => {
  console.log('🚀 AppLayout mounted - PWA Initialization')
  
  // Check requirements
  await checkPWARequirements()
  
  // Event listener untuk PWA installation prompt
  window.addEventListener('beforeinstallprompt', (e) => {
    console.log('🎯 beforeinstallprompt event fired!', e)
    
    e.preventDefault()
    deferredPrompt = e

    if (canShowInstallBtn()) {
      console.log('✅ Showing install button')
      showInstall.value = true
    } else {
      console.log('⏸️ Install button hidden (user recently rejected)')
    }
  })

  // Event listener untuk app installed
  window.addEventListener('appinstalled', (evt) => {
    console.log('🎉 PWA was successfully installed!', evt)
    showInstall.value = false
    deferredPrompt = null
  })

  // Check jika app sudah running sebagai PWA
  if (window.matchMedia('(display-mode: standalone)').matches) {
    console.log('📱 App running in standalone mode (already installed)')
    showInstall.value = false
  }

  // Additional check setelah 3 detik
  setTimeout(() => {
    if (!showInstall.value && !deferredPrompt) {
      console.log('ℹ️ Install button not showing. Possible reasons:')
      console.log('- App already installed')
      '- beforeinstallprompt event not fired by browser'
      '- PWA requirements not met'
      '- User recently rejected installation'
    }
  }, 3000)
})

const installApp = async () => {
  if (!deferredPrompt) {
    console.log('❌ No install prompt available')
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Install prompt tidak tersedia. Refresh halaman dan coba lagi.'
    })
    return
  }

  try {
    console.log('🔄 Showing install prompt...')
    
    // Tampilkan prompt install
    deferredPrompt.prompt()
    
    // Tunggu user memilih
    const { outcome } = await deferredPrompt.userChoice
    console.log('📱 User response:', outcome)

    if (outcome === 'accepted') {
      console.log('✅ User accepted PWA installation')
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Aplikasi sedang diinstall...',
        timer: 2000
      })
    } else {
      console.log('❌ User declined PWA installation')
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
    console.error('❌ Install error:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Terjadi kesalahan saat install. Coba lagi.'
    })
  }
}
</script>

<style scoped>
.alert-info {
  font-size: 0.875rem;
}
</style>