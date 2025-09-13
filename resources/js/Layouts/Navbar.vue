<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">POS Toko</a>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
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
            <Link class="nav-link" href="/reports">Laporan</Link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
