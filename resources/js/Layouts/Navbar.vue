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
        <li>
          <Link
            class="nav-link text-white"
            href="/dashboard"
            :class="{ active: $page.url.startsWith('/dashboard') }"
          >
            Dashboard
          </Link>
        </li>
        <li>
          <Link
            class="nav-link text-white"
            href="/products"
            :class="{ active: $page.url.startsWith('/products') }"
          >
            Produk
          </Link>
        </li>
        <li>
          <Link
            class="nav-link text-white"
            href="/stock"
            :class="{ active: /^\/stock(\/|$)/.test($page.url) }"
          >
            Stok
          </Link>
        </li>
        <li>
          <Link
            class="nav-link text-white"
            href="/stock-transfers"
            :class="{ active: /^\/stock-transfers(\/|$)/.test($page.url) }"
          >
            Stok Transfer
          </Link>
        </li>
        <li>
          <Link
            class="nav-link text-white"
            href="/sales"
            :class="{ active: $page.url.startsWith('/sales') }"
          >
            Penjualan
          </Link>
        </li>
<li v-if="$page.props.auth.user.roles.some(role => role.name === 'admin')">
  <Link
    class="nav-link text-white"
    href="/stores"
    :class="{ active: $page.url.startsWith('/stores') }"
  >
    Toko
  </Link>
</li>

        <li>
          <Link
            class="nav-link text-white"
            href="/reports"
            :class="{ active: $page.url.startsWith('/reports') }"
          >
            Laporan
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
        👤 {{ $page.props.auth?.user?.name || 'Guest' }}
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const isOpen = ref(false)

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
/* Wrapper & Overlay */
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
  /* background-color: #575555; */
}
#wrapper.toggled #page-content-wrapper {
  margin-left: 250px;
}
.sidebar-nav li {
  padding: 0;
}
/* Header utama tetap fixed */
.page-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 56px; /* tinggi header */
  z-index: 1000; /* di atas konten */
}

/* Konten turun ke bawah header */
#page-content-wrapper {
  margin-top: 56px;
}

/* Sidebar */
#sidebar-wrapper {
  min-width: 250px;
  max-width: 250px;
  background: #343a40;
  color: #fff;
  transition: all 0.3s;
  position: fixed;
  top: 0;         /* penting: mulai dari atas, supaya header user gak nutup */
  left: 0;
  height: 100vh;
  z-index: 1010;  /* lebih tinggi daripada header (1000) */
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
</style>
