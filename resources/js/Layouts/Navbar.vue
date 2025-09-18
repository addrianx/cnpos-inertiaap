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
              :class="{ 'active': $page.url.startsWith('/dashboard') }"
            >
              Dashboard
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/products"
              :class="{ 'active': $page.url.startsWith('/products') }"
            >
              Produk
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/stock"
              :class="{ 'active': /^\/stock(\/|$)/.test($page.url) }"
            >
              Stok
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/stock-transfers"
              :class="{ 'active': /^\/stock-transfers(\/|$)/.test($page.url) }"
            >
              Stok Transfer
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/sales"
              :class="{ 'active': $page.url.startsWith('/sales') }"
            >
              Penjualan
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/stores"
              :class="{ 'active': $page.url.startsWith('/stores') }"
            >
              Toko
            </Link>
          </li>
          <li>
            <Link
              class="nav-link text-white"
              href="/reports"
              :class="{ 'active': $page.url.startsWith('/reports') }"
            >
              Laporan
            </Link>
          </li>
        </ul>

    </nav>

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <button
        type="button"
        class="btn btn-dark m-2"
        @click="toggleSidebar"
      >
            <span class="hamb-top">MENU</span>
      </button>
      <div class="container-fluid">
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
.sidebar-header{
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
  background: rgba(0,0,0,0.7);
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
.sidebar-header {
  font-size: 1.2rem;
}
.sidebar-nav li {
  padding: 0; /* hapus padding dari li biar gak ganggu ukuran penuh */
}

.sidebar-nav .nav-link {
  display: block;       /* biar link isi seluruh li */
  width: 100%;          /* lebar penuh */
  height: 100%;         /* tinggi penuh */
  padding: 0.6rem 1rem; /* pindahkan padding ke link */
  text-decoration: none;
  color: white;
}
#page-content-wrapper
 {
    flex: 1;
    transition: all 0.3s;
    margin-left: 0;
    background-color: #575555;
}
.sidebar-nav li:hover {
  background: rgba(255,255,255,0.1);
}
.nav-link.active {
  background-color: rgba(255, 255, 255, 0.15);
  font-weight: bold;
  cursor: default;         /* cursor jadi default, bukan pointer */
  pointer-events: none;    /* matikan klik */
}

</style>
