<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">POS Toko</a>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <Link class="nav-link" href="/dashboard">Dashboard</Link>
          </li>
          <li class="nav-item">
            <Link class="nav-link" href="/products">Produk</Link>
          </li>
          <li class="nav-item">
            <Link class="nav-link" href="/stock">Stok</Link>
          </li>
          <li class="nav-item">
            <Link class="nav-link" href="/sales">Penjualan</Link>
          </li>
          <li class="nav-item">
            <Link class="nav-link" href="/stores">Toko</Link>
          </li>
          <li class="nav-item">
            <Link class="nav-link" href="/reports">Laporan</Link>
          </li>
        </ul>

        <!-- Sign Out -->
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <button class="btn btn-outline-light" @click="confirmLogout">
              Sign Out
            </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

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
