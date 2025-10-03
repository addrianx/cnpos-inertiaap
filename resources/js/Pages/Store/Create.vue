<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Tambah Toko Baru</h2>
      <Link href="/stores" class="btn btn-secondary">← Kembali</Link>
    </div>

    <!-- Form Tambah Toko -->
    <div class="card">
      <div class="card-header bg-dark text-white">Form Tambah Toko</div>
      <div class="card-body">
        <form @submit.prevent="submitStore">
          <!-- Nama Toko -->
          <div class="mb-3">
            <label class="form-label">Nama Toko</label>
            <input
              v-model="formStore.name"
              type="text"
              class="form-control"
              placeholder="Masukkan nama toko"
              required
            />
          </div>

          <!-- Alamat -->
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea
              v-model="formStore.address"
              class="form-control"
              rows="3"
              placeholder="Alamat lengkap toko"
              required
            ></textarea>
          </div>

          <!-- Pilih User Manager -->
          <div class="mb-3">
            <label class="form-label">User Manager</label>
            <div v-if="users.length > 0">
              <div v-for="user in users" :key="user.id" class="form-check">
                <input
                  type="checkbox"
                  class="form-check-input"
                  :id="'user-' + user.id"
                  :value="user.id"
                  v-model="formStore.user_ids"
                />
                <label class="form-check-label" :for="'user-' + user.id">
                  {{ user.name }} ({{ user.email }})
                </label>
              </div>
            </div>
            <div v-else class="alert alert-warning">
              Tidak ada user yang tersedia. Silakan buat user terlebih dahulu.
            </div>
          </div>

          <!-- Tombol Simpan -->
          <button type="submit" class="btn btn-success">Simpan Toko</button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import { reactive } from 'vue'

defineProps({
  users: Array, // untuk dropdown User Manager
})

// Form Toko
const formStore = reactive({
  name: '',
  address: '',
  user_ids: [],
})

// Submit Toko
const submitStore = () => {
  router.post('/stores', formStore, {
    onSuccess: () => {
      Swal.fire('Berhasil!', 'Toko berhasil ditambahkan.', 'success')
      formStore.name = ''
      formStore.address = ''
      formStore.user_ids = []
    },
    onError: () => {
      Swal.fire('Gagal!', 'Terjadi kesalahan saat menambahkan toko.', 'error')
    },
  })
}
</script>