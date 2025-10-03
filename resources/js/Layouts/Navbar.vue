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
            Dashboard
          </Link>
        </li>
        <li>
          <Link
            class="nav-link text-white"
            href="/products"
            :class="{ active: currentUrl.startsWith('/products') }"
          >
            Produk
          </Link>
        </li>
        <li>
          <Link
            class="nav-link text-white"
            href="/sales"
            :class="{ active: currentUrl.startsWith('/sales') }"
          >
            Penjualan
          </Link>
        </li>

        <!-- Menu hanya untuk Admin & Manager -->
        <template v-if="canAccessManagement">
          <li>
            <Link
              class="nav-link text-white"
              href="/stock"
              :class="{ active: /^\/stock(\/|$)/.test(currentUrl) }"
            >
              Stok
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/stock-loan"
              :class="{ active: /^\/stock-loan(\/|$)/.test(currentUrl) }"
            >
              Pinjam Stock
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/stock-transfers"
              :class="{ active: /^\/stock-transfers(\/|$)/.test(currentUrl) }"
            >
              Stok Transfer
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/reports"
              :class="{ active: currentUrl.startsWith('/reports') }"
            >
              Laporan
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
            Toko
          </Link>
        </li>
        <li v-if="isAdmin">
          <Link
            class="nav-link text-white"
            href="/users"
            :class="{ active: currentUrl.startsWith('/users') }"
          >
            Users
          </Link>
        </li>

        <!-- Tombol Logout -->
        <li class="mt-3 px-2">
          <button class="btn btn-danger w-100" @click="confirmLogout">
            Sign Out
          </button>
        </li>
      </ul>
    </nav>

    <!-- Page Header tetap di atas -->
    <div class="page-header d-flex justify-content-between align-items-center p-2 bg-secondary text-white">
      <!-- tombol menu -->
      <button type="button" class="btn btn-dark" @click="toggleSidebar">
        <span class="hamb-top">☰ MENU</span>
      </button>

      <!-- nama user -->
      <div class="fw-bold">
        👤 {{ userName }} <span class="badge bg-light text-dark ms-1">{{ userRoleName }}</span>
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
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const page = usePage()
const isOpen = ref(false)

// Debug: Tampilkan semua props yang tersedia
onMounted(() => {
  console.log('=== ALL PAGE PROPS ===', page.props)
  console.log('=== AUTH DATA ===', page.props.auth)
  console.log('=== USER DATA ===', page.props.auth?.user)
})

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

// Ambil role_id dari user - gunakan computed untuk reactivity
const userRoleId = computed(() => {
  const roleId = user.value?.role_id
  console.log('User Role ID:', roleId) // Debug
  return roleId || null
})

const userRoleName = computed(() => {
  const roles = {
    1: 'Admin',
    2: 'Manager', 
    3: 'Kasir'
  }
  const roleName = roles[userRoleId.value] || 'Guest'
  console.log('User Role Name:', roleName) // Debug
  return roleName
})

const isAdmin = computed(() => {
  const admin = userRoleId.value === 1
  console.log('Is Admin:', admin) // Debug
  return admin
})

const isManager = computed(() => {
  const manager = userRoleId.value === 2
  console.log('Is Manager:', manager) // Debug
  return manager
})

const isCashier = computed(() => {
  const cashier = userRoleId.value === 3
  console.log('Is Cashier:', cashier) // Debug
  return cashier
})

// Admin & Manager bisa akses management
const canAccessManagement = computed(() => {
  const canAccess = isAdmin.value || isManager.value
  console.log('Can Access Management:', canAccess) // Debug
  return canAccess
})

const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}

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
      router.post('/logout')
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
</style>