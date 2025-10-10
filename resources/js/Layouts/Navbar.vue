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
            href="/stock-loan"
            :class="{ active: currentUrl.startsWith('/stock-loan') }"
          >
            <i class="fas fa-handshake me-2"></i>Pinjam Stock
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
            <i class="fas fa-desktop me-2"></i>Rakitan PC
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

        <!-- Tombol Logout -->
        <li class="mt-3 px-2">
          <button class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2" @click="confirmLogout">
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const page = usePage()
const isOpen = ref(false)

// ✅ PWA Installation State
const deferredPrompt = ref(null)
const showInstallButton = ref(false)
const isInstalled = ref(false)

// Gunakan usePage() untuk mengakses data
const user = computed(() => {
  return page.props.auth?.user || {}
})

const currentUrl = computed(() => {
  return page.url || '/'
})

const userName = computed(() => {
  return user.value?.name || 'Guest'
})

// Ambil role_id dari user
const userRoleId = computed(() => {
  return user.value?.role_id || null
})

const userRoleName = computed(() => {
  const roles = {
    1: 'Admin',
    2: 'Manager', 
    3: 'Kasir'
  }
  return roles[userRoleId.value] || 'Guest'
})

const isAdmin = computed(() => {
  return userRoleId.value === 1
})

const isManager = computed(() => {
  return userRoleId.value === 2
})

const isCashier = computed(() => {
  return userRoleId.value === 3
})

// Admin & Manager bisa akses management
const canAccessManagement = computed(() => {
  return isAdmin.value || isManager.value
})

const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}

// ✅ PWA Installation Functions
const handleBeforeInstallPrompt = (e) => {
  console.log('🚀 beforeinstallprompt event fired')
  
  // Prevent the mini-infibar from appearing on mobile
  e.preventDefault()
  
  // Stash the event so it can be triggered later
  deferredPrompt.value = e
  
  // Update UI to notify the user they can install the PWA
  showInstallButton.value = true
  
  console.log('✅ PWA install prompt ready to show')
}

const handleAppInstalled = () => {
  console.log('✅ PWA was installed')
  isInstalled.value = true
  showInstallButton.value = false
  deferredPrompt.value = null
  
  Swal.fire({
    title: 'Berhasil!',
    text: 'Aplikasi berhasil diinstall',
    icon: 'success',
    timer: 3000,
    showConfirmButton: false
  })
}

const installPWA = async () => {
  if (!deferredPrompt.value) {
    console.log('❌ No install prompt available')
    
    Swal.fire({
      title: 'Info',
      text: 'Tombol install sudah tidak tersedia. Coba refresh halaman.',
      icon: 'info',
      confirmButtonText: 'OK'
    })
    return
  }

  try {
    console.log('🚀 Showing install prompt...')
    
    // Show the install prompt
    deferredPrompt.value.prompt()
    
    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.value.userChoice
    
    console.log(`User response to install prompt: ${outcome}`)
    
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
      Swal.fire({
        title: 'Dibatalkan',
        text: 'Installasi aplikasi dibatalkan',
        icon: 'info',
        timer: 2000,
        showConfirmButton: false
      })
    }
    
    // We've used the prompt, and can't use it again, so clear it
    deferredPrompt.value = null
    showInstallButton.value = false
    
  } catch (error) {
    console.error('❌ Error during PWA installation:', error)
    
    Swal.fire({
      title: 'Error!',
      text: 'Terjadi kesalahan saat menginstall aplikasi',
      icon: 'error',
      confirmButtonText: 'OK'
    })
  }
}

// Check if app is already installed
const checkIfPWAInstalled = () => {
  // Check if the app is running in standalone mode
  if (window.matchMedia('(display-mode: standalone)').matches) {
    console.log('✅ App is running in standalone mode')
    isInstalled.value = true
    showInstallButton.value = false
    return true
  }
  
  // Check for other indicators of PWA installation
  if (window.navigator.standalone === true) {
    console.log('✅ App is installed (iOS)')
    isInstalled.value = true
    showInstallButton.value = false
    return true
  }
  
  return false
}

onMounted(() => {
  console.log('=== ALL PAGE PROPS ===', page.props)
  console.log('=== AUTH DATA ===', page.props.auth)
  console.log('=== USER DATA ===', page.props.auth?.user)

  // ✅ Initialize PWA Installation
  checkIfPWAInstalled()
  
  // Listen for beforeinstallprompt event
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  
  // Listen for app installed event
  window.addEventListener('appinstalled', handleAppInstalled)
  
  console.log('📱 PWA installation listeners activated')
})

onUnmounted(() => {
  // Clean up event listeners
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  window.removeEventListener('appinstalled', handleAppInstalled)
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
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        // Tampilkan loading
        Swal.fire({
          title: 'Logging out...',
          text: 'Sedang memproses logout',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading()
          }
        })

        // Gunakan fetch untuk POST logout
        const response = await fetch('/logout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        })

        // Setelah logout berhasil, redirect ke login
        if (response.ok) {
          window.location.href = '/login'
        } else {
          throw new Error('Logout failed')
        }
        
      } catch (error) {
        console.error('Logout error:', error)
        Swal.fire({
          title: 'Error!',
          text: 'Gagal melakukan logout',
          icon: 'error',
          confirmButtonText: 'OK'
        })
      }
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
</style>