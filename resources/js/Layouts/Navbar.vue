<template>
  <div id="wrapper" :class="{ 'toggled': isOpen }">
    <!-- Overlay -->
    <div class="overlay" v-show="isOpen" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <nav id="sidebar-wrapper" class="bg-dark">
      <div class="sidebar-header text-white fw-bold px-3 py-3">
        POS Toko
      </div>
      <ul class="list-unstyled sidebar-nav">
        <!-- Menu untuk semua role -->
        <li>
          <Link
            class="nav-link text-white"
            href="/dashboard"
            :class="{ active: currentUrl.startsWith('/dashboard') }"
          >
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
          </Link>
        </li>
        <li>
          <Link
            class="nav-link text-white"
            href="/products"
            :class="{ active: currentUrl.startsWith('/products') }"
          >
            <i class="fas fa-box me-2"></i>Produk
          </Link>
        </li>


        <li>
          <Link
            class="nav-link text-white"
            href="/sales"
            :class="{ active: currentUrl.startsWith('/sales') }"
          >
            <i class="fas fa-shopping-cart me-2"></i>Penjualan
          </Link>
        </li>

        <template v-if="canAccessManagement">
        <li>
          <Link
            class="nav-link text-white"
            href="/stock-loan"
            :class="{ active: currentUrl.startsWith('/stock-loan') }"
          >
            <i class="fas fa-handshake me-2"></i>Pinjam Stock
          </Link>
        </li>
        </template>

        <!-- Menu hanya untuk Admin & Manager -->
        <template v-if="canAccessManagement">
          <li>
            <Link
              class="nav-link text-white"
              href="/reports"
              :class="{ active: currentUrl.startsWith('/reports') }"
            >
              <i class="fas fa-chart-bar me-2"></i>Laporan
            </Link>
          </li>
        </template>

        <!-- Menu hanya untuk Admin -->
        <li v-if="isAdmin">
          <Link
            class="nav-link text-white"
            href="/stores"
            :class="{ active: currentUrl.startsWith('/stores') }"
          >
            <i class="fas fa-store me-2"></i>Toko
          </Link>
        </li>
        <li v-if="isAdmin">
          <Link
            class="nav-link text-white"
            href="/users"
            :class="{ active: currentUrl.startsWith('/users') }"
            >
            <i class="fas fa-users me-2"></i>Users
          </Link>
        </li>
        
        <li>
          <Link
            class="nav-link text-white"
            href="/pc-assembly"
            :class="{ active: currentUrl.startsWith('/pc-assembly') }"
          >
            <i class="fas fa-desktop me-2"></i>Rakit PC
          </Link>
        </li>

        <!-- ✅ TAMBAHAN: Tombol Install PWA -->
        <li v-if="showInstallButton" class="px-2 mt-2">
          <button 
            class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2"
            @click="installPWA"
          >
            <i class="fas fa-download"></i>
            Install App
          </button>
        </li>

        <!-- ✅ PERBAIKAN: Tombol Logout dengan form -->
  <li class="mt-3 px-2">
    <button 
      type="button" 
      class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2"
      @click="confirmLogout"
    >
      <i class="fas fa-sign-out-alt"></i>
      Sign Out
    </button>
  </li>
      </ul>
    </nav>

    <!-- Page Header tetap di atas -->
    <div class="page-header d-flex justify-content-between align-items-center p-2 bg-secondary text-white">
      <!-- tombol menu -->
      <button type="button" class="btn btn-dark d-flex align-items-center gap-2" @click="toggleSidebar">
        <i class="fas fa-bars"></i>
        <span class="hamb-top">MENU</span>
      </button>

      <!-- nama user -->
      <div class="fw-bold d-flex align-items-center gap-2">
        <i class="fas fa-user"></i>
        {{ userName }} 
        <span class="badge bg-light text-dark ms-1">{{ userRoleName }}</span>
      </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <div class="container-fluid py-3">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
// Di script setup AppLayout.vue
// Di script setup AppLayout.vue - GANTI bagian PWA dengan ini
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const page = usePage()
const isOpen = ref(false)

// ✅ PWA Installation State
const deferredPrompt = ref(null)
const showInstallButton = ref(false)
const isInstalled = ref(false)
const HIDE_DAYS = 3
const LAST_REJECT_KEY = "pwa_last_reject"
const VISIT_COUNT_KEY = "pwa_visit_count"
const INSTALLED_KEY = "pwa_installed"

// Computed Properties
const user = computed(() => page.props.auth?.user || {})
const currentUrl = computed(() => page.url || '/')
const userName = computed(() => user.value?.name || 'Guest')
const userRoleId = computed(() => user.value?.role_id || null)

const userRoleName = computed(() => {
  const roles = { 1: 'Admin', 2: 'Manager', 3: 'Kasir' }
  return roles[userRoleId.value] || 'Guest'
})

const isAdmin = computed(() => userRoleId.value === 1)
const isManager = computed(() => userRoleId.value === 2)
const isCashier = computed(() => userRoleId.value === 3)
const canAccessManagement = computed(() => isAdmin.value || isManager.value)

// ✅ Track user engagement
const trackEngagement = () => {
  let visitCount = parseInt(localStorage.getItem(VISIT_COUNT_KEY) || '0')
  visitCount++
  localStorage.setItem(VISIT_COUNT_KEY, visitCount.toString())
  console.log('📊 Visit count:', visitCount)
  return visitCount
}

// ✅ SIMPLE Check - hanya cek rejection history
const canShowInstallBtn = () => {
  console.log('🔍 Checking if can show install button...')
  
  // 1. Cek jika sudah diinstall (dari berbagai method)
  if (isInstalled.value) {
    console.log('❌ Cannot show: App already installed')
    return false
  }
  
  // 2. Cek rejection history
  const lastReject = localStorage.getItem(LAST_REJECT_KEY)
  if (!lastReject) {
    console.log('✅ Can show: No rejection history')
    return true
  }
  
  const lastRejectTime = new Date(lastReject).getTime()
  const now = Date.now()
  const diffDays = (now - lastRejectTime) / (1000 * 60 * 60 * 24)
  const canShow = diffDays >= HIDE_DAYS
  
  console.log(`⏰ Rejection: ${diffDays.toFixed(2)} days ago, can show: ${canShow}`)
  return canShow
}

const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}

// ✅ SIMPLE PWA Installation Detection
const checkIfPWAInstalled = () => {
  console.log('🔍 Checking PWA installation...')
  
  let installed = false
  
  // Method 1: Display mode
  if (window.matchMedia('(display-mode: standalone)').matches) {
    console.log('✅ PWA detected: display-mode standalone')
    installed = true
  }
  
  // Method 2: iOS standalone
  else if (window.navigator.standalone === true) {
    console.log('✅ PWA detected: iOS standalone')
    installed = true
  }
  
  // Method 3: localStorage flag (fallback)
  else if (localStorage.getItem(INSTALLED_KEY) === 'true') {
    console.log('✅ PWA detected: localStorage flag')
    installed = true
  }
  
  else {
    console.log('❌ PWA not installed')
    installed = false
  }
  
  // Update state
  isInstalled.value = installed
  if (installed) {
    showInstallButton.value = false
  }
  
  return installed
}

// ✅ Enhanced PWA Functions
const handleBeforeInstallPrompt = (e) => {
  console.log('🚀 beforeinstallprompt event fired!')
  console.log('📝 Event details:', e)
  
  e.preventDefault()
  deferredPrompt.value = e

  // Cek apakah boleh menampilkan tombol
  const canShow = canShowInstallBtn()
  console.log(`🎯 Can show button: ${canShow}`)
  
  if (canShow) {
    showInstallButton.value = true
    console.log('✅ Showing install button')
  } else {
    showInstallButton.value = false
    console.log('⏸️ Not showing install button')
  }
}

const handleAppInstalled = () => {
  console.log('✅ PWA installed event received')
  
  isInstalled.value = true
  showInstallButton.value = false
  deferredPrompt.value = null
  
  // Set flags
  localStorage.setItem(INSTALLED_KEY, 'true')
  localStorage.setItem(VISIT_COUNT_KEY, '0')
  
  Swal.fire({
    title: 'Berhasil!',
    text: 'Aplikasi berhasil diinstall',
    icon: 'success',
    timer: 3000,
    showConfirmButton: false
  })
}

const installPWA = async () => {
  console.log('🖱️ Install button clicked')
  
  if (!deferredPrompt.value) {
    console.log('❌ No install prompt available')
    
    Swal.fire({
      title: 'Cara Install Aplikasi',
      html: `
        <div class="text-start">
          <p><strong>Chrome/Edge Desktop:</strong></p>
          <ol>
            <li>Klik icon <i class="fas fa-ellipsis-vertical"></i> di address bar</li>
            <li>Pilih "Install POS Toko"</li>
          </ol>
          <p><strong>Chrome Mobile:</strong></p>
          <ol>
            <li>Tap icon <i class="fas fa-ellipsis-vertical"></i></li>
            <li>Pilih "Add to Home Screen"</li>
          </ol>
          <p><strong>Safari (iPhone/iPad):</strong></p>
          <ol>
            <li>Tap icon <i class="fas fa-share"></i></li>
            <li>Pilih "Add to Home Screen"</li>
          </ol>
        </div>
      `,
      icon: 'info',
      confirmButtonText: 'Mengerti',
      width: 500
    })
    return
  }

  try {
    console.log('🚀 Showing install prompt...')
    deferredPrompt.value.prompt()
    const { outcome } = await deferredPrompt.value.userChoice
    
    console.log(`📝 User choice: ${outcome}`)
    
    if (outcome === 'accepted') {
      console.log('✅ User accepted the install')
      Swal.fire({
        title: 'Menginstall...',
        text: 'Aplikasi sedang diinstall',
        icon: 'info',
        timer: 2000,
        showConfirmButton: false
      })
    } else {
      console.log('❌ User dismissed the install')
      localStorage.setItem(LAST_REJECT_KEY, new Date().toISOString())
      
      Swal.fire({
        title: 'Dibatalkan',
        text: 'Installasi aplikasi dibatalkan',
        icon: 'info',
        timer: 2000,
        showConfirmButton: false
      })
    }
    
    deferredPrompt.value = null
    showInstallButton.value = false
    
  } catch (error) {
    console.error('❌ Installation error:', error)
    
    Swal.fire({
      title: 'Error!',
      text: 'Terjadi kesalahan saat menginstall aplikasi',
      icon: 'error',
      confirmButtonText: 'OK'
    })
  }
}

// ✅ SIMPLE PWA Check
const manualPWACheck = async () => {
  console.log('🛠️ SIMPLE PWA CHECK')
  
  // 1. Cek installation status
  const installed = checkIfPWAInstalled()
  if (installed) {
    console.log('⏸️ App installed - hiding button')
    showInstallButton.value = false
    return false
  }
  
  // 2. Cek basic requirements
  const requirements = {
    serviceWorker: 'serviceWorker' in navigator,
    beforeInstallPrompt: 'BeforeInstallPromptEvent' in window,
    manifest: false,
  }

  // Check manifest
  try {
    const response = await fetch('/manifest.json')
    requirements.manifest = response.ok
    console.log('✅ Manifest check passed')
  } catch (error) {
    console.log('❌ Manifest check failed:', error)
  }

  console.log('PWA Requirements:', requirements)
  
  // 3. Cek apakah boleh menampilkan tombol
  const canShow = canShowInstallBtn()
  console.log(`🎯 Final decision - can show: ${canShow}`)
  
  if (canShow) {
    console.log('✅ Showing install button')
    showInstallButton.value = true
    return true
  } else {
    console.log('❌ Not showing install button')
    showInstallButton.value = false
    return false
  }
}

onMounted(() => {
  console.log('=== SIDEBAR MOUNTED ===')
  console.log('🔧 PWA Support Check:')
  console.log('  - Service Worker:', 'serviceWorker' in navigator)
  console.log('  - BeforeInstallPrompt:', 'BeforeInstallPromptEvent' in window)
  console.log('  - Display Mode:', window.matchMedia('(display-mode: standalone)').matches)
  console.log('  - iOS Standalone:', window.navigator.standalone)

  // Track user visit
  const visitCount = trackEngagement()

  // Initial installation check
  checkIfPWAInstalled()
  
  // Listen for PWA events
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  window.addEventListener('appinstalled', handleAppInstalled)
  
  console.log('📱 PWA event listeners activated')

  // ✅ Manual check setelah beberapa detik
  setTimeout(async () => {
    console.log('🕒 Running manual PWA check...')
    await manualPWACheck()
    
    // Fallback: Force show button untuk testing
    console.log('🔄 Current state:', {
      showInstallButton: showInstallButton.value,
      isInstalled: isInstalled.value,
      visitCount: visitCount,
      deferredPrompt: !!deferredPrompt.value
    })
    
    // Jika masih false, coba force show untuk testing
    if (!showInstallButton.value && visitCount >= 2) {
      console.log('🔄 Fallback: Forcing show button for testing')
      showInstallButton.value = true
    }
  }, 2000)
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  window.removeEventListener('appinstalled', handleAppInstalled)
})

// ✅ Export untuk debugging di console
defineExpose({
  checkIfPWAInstalled,
  manualPWACheck,
  canShowInstallBtn,
  showInstallButton,
  isInstalled,
  deferredPrompt
})

const confirmLogout = () => {
  Swal.fire({
    title: 'Konfirmasi Logout',
    text: 'Apakah Anda yakin ingin keluar?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, logout!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Logging out...',
        text: 'Sedang memproses logout',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
      })
      router.post(route('logout'))
    }
  })
}
</script>


<style scoped>
/* CSS tetap sama seperti sebelumnya */
#wrapper {
  display: flex;
  width: 100%;
  align-items: stretch;
  transition: all 0.3s;
}
.sidebar-header {
  font-size: 1.2rem;
  text-transform: uppercase;
  text-align: center;
}

li {
  padding: 5px 10px;
  border-radius: 15px;
}

.nav-link[data-v-a60fa93c],
.nav-link.active[data-v-a60fa93c] {
  /* Tambahkan properti yang sama di sini */
  color: white;
  text-decoration: none;
  display: block;
  transition: all 0.3s ease;
  /* Tambahkan properti lain yang diinginkan */
}

.nav-link.active[data-v-a60fa93c]{
  padding: 5px 10px;
  border-radius: 15px;
}

.overlay {
  position: fixed;
  display: block;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.7);
  z-index: 998;
}
#sidebar-wrapper {
  min-width: 250px;
  max-width: 250px;
  background: #343a40;
  color: #fff;
  transition: all 0.3s;
  position: fixed;
  height: 100vh;
  z-index: 999;
  transform: translateX(-100%);
}
#wrapper.toggled #sidebar-wrapper {
  transform: translateX(0);
}
#page-content-wrapper {
  flex: 1;
  transition: all 0.3s;
  margin-left: 0;
}
#wrapper.toggled #page-content-wrapper {
  margin-left: 250px;
}
.sidebar-nav li {
  padding: 0;
}
.page-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 56px;
  z-index: 1000;
}
#page-content-wrapper {
  margin-top: 56px;
}
#sidebar-wrapper {
  min-width: 250px;
  max-width: 250px;
  background: #343a40;
  color: #fff;
  transition: all 0.3s;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 1010;
  transform: translateX(-100%);
}
#wrapper.toggled #sidebar-wrapper {
  transform: translateX(0);
}
.sidebar-nav .nav-link {
  display: block;
  width: 100%;
  height: 100%;
  padding: 0.6rem 1rem;
  text-decoration: none;
  color: white;
  transition: all 0.3s ease;
}
.sidebar-nav li:hover {
  background: rgba(255, 255, 255, 0.1);
}
.nav-link.active {
  background-color: rgba(255, 255, 255, 0.15);
  font-weight: bold;
  cursor: default;
  pointer-events: none;
}
.swal2-container.swal2-center.swal2-backdrop-show {
  z-index: 2000;
}
.badge {
  font-size: 0.7em;
}

/* ✅ Style untuk tombol install PWA */
.btn-success {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-success:active {
  transform: translateY(0);
}

/* Icons spacing - PERBAIKAN: lebih rapat */
.nav-link i {
  width: 16px;
  text-align: center;
}

/* Pastikan teks sejajar dengan icon */
.nav-link {
  display: flex;
  align-items: center;
}

/* Page header improvements */
.page-header .btn-dark {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.page-header .btn-dark:hover {
  background: rgba(0, 0, 0, 0.5);
}

/* ✅ PERBAIKAN: Tombol menu lebih besar untuk mobile */
.page-header .btn-dark {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.5rem 1rem; /* Tambahkan padding lebih besar */
  min-height: 44px; /* Minimum touch target size untuk mobile */
  font-size: 1rem; /* Ukuran font lebih besar */
}

/* ✅ PERBAIKAN: Menu item lebih besar TANPA ANIMASI */
.sidebar-nav .nav-link {
  display: block;
  width: 100%;
  height: 100%;
  padding: 0.8rem 1rem; /* Padding lebih besar */
  text-decoration: none;
  color: white;
  min-height: 48px; /* Minimum touch target size */
  font-size: 1rem; /* Ukuran font lebih besar */
  /* HAPUS transition dan transform */
}

/* ✅ PERBAIKAN: Menu aktif lebih besar TANPA ANIMASI */
.nav-link.active {
  background-color: rgba(255, 255, 255, 0.2); /* Lebih terang */
  font-weight: bold;
  cursor: default;
  pointer-events: none;
  /* HAPUS transform dan box-shadow */
}

/* ✅ PERBAIKAN: Hover effect SEDERHANA tanpa animasi */
.sidebar-nav li:hover {
  background: rgba(255, 255, 255, 0.15);
  /* HAPUS transform dan transition */
}

/* ✅ PERBAIKAN: Icon lebih besar */
.nav-link i {
  width: 20px; /* Lebar icon lebih besar */
  text-align: center;
  font-size: 1.1rem; /* Ukuran icon lebih besar */
}

/* ✅ PERBAIKAN: Pastikan tidak melebihi sidebar wrapper */
.sidebar-nav {
  width: 100%;
  overflow: hidden; /* Mencegah konten meluber */
}

.sidebar-nav li {
  width: 100%;
  margin: 0;
  /* HAPUS padding dari li, gunakan padding dari nav-link saja */
}

/* ✅ RESPONSIVE: Untuk mobile view */
@media (max-width: 768px) {
  .page-header .btn-dark {
    padding: 0.6rem 1.2rem; /* Lebih besar di mobile */
    font-size: 1.1rem; /* Font lebih besar di mobile */
    min-height: 50px; /* Lebih tinggi di mobile */
  }
  
  .sidebar-nav .nav-link {
    padding: 1rem 1.2rem; /* Lebih besar di mobile */
    min-height: 52px; /* Lebih tinggi di mobile */
    font-size: 1.1rem; /* Font lebih besar di mobile */
  }
  
  .nav-link i {
    width: 22px; /* Icon lebih besar di mobile */
    font-size: 1.2rem;
  }
  
  /* Tombol logout dan install lebih besar di mobile */
  .btn-danger, .btn-success {
    padding: 0.8rem 1rem;
    min-height: 50px;
    font-size: 1.1rem;
  }
}

/* ✅ PERBAIKAN: Badge role lebih jelas */
.badge {
  font-size: 0.75em;
  padding: 0.4em 0.6em;
}

/* CSS lainnya tetap sama */
#wrapper {
  display: flex;
  width: 100%;
  align-items: stretch;
}
.sidebar-header {
  font-size: 1.2rem;
  text-transform: uppercase;
  text-align: center;
}

.overlay {
  position: fixed;
  display: block;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.7);
  z-index: 998;
}
#sidebar-wrapper {
  min-width: 250px;
  max-width: 250px;
  background: #343a40;
  color: #fff;
  position: fixed;
  height: 100vh;
  z-index: 999;
  transform: translateX(-100%);
  overflow-y: auto; /* Scroll jika konten terlalu panjang */
}
#wrapper.toggled #sidebar-wrapper {
  transform: translateX(0);
}
#page-content-wrapper {
  flex: 1;
  margin-left: 0;
}
#wrapper.toggled #page-content-wrapper {
  margin-left: 250px;
}
.sidebar-nav li {
  padding: 0; /* HAPUS padding dari li */
}
.page-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 56px;
  z-index: 1000;
}
#page-content-wrapper {
  margin-top: 56px;
}
#sidebar-wrapper {
  min-width: 250px;
  max-width: 250px;
  background: #343a40;
  color: #fff;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 1010;
  transform: translateX(-100%);
}
#wrapper.toggled #sidebar-wrapper {
  transform: translateX(0);
}

.swal2-container.swal2-center.swal2-backdrop-show {
  z-index: 2000;
}

/* ✅ Style untuk tombol install PWA */
.btn-success {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
  font-weight: 600;
}

/* Icons spacing */
.nav-link {
  display: flex;
  align-items: center;
}

/* Page header improvements */
.page-header .btn-dark:hover {
  background: rgba(0, 0, 0, 0.5);
}
</style>