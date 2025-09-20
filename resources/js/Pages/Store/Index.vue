<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Toko</h2>
      <Link href="/stores/create" class="btn btn-primary">+ Tambah Toko</Link>
    </div>

    <!-- 🔥 Table daftar toko -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle text-nowrap">
        <thead class="table-dark">
          <tr>
            <th>Nama Toko</th>
            <th>Alamat</th>
            <th>Manager</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!stores || stores.length === 0">
            <td colspan="4" class="text-center text-muted py-4">
              Tidak ada toko terdaftar.
            </td>
          </tr>

          <tr v-else v-for="store in stores" :key="store.id">
            <td>{{ store.name }}</td>
            <td>{{ store.address }}</td>
            <td>{{ store.user?.name || 'Belum ada user' }}</td>
            <td>
              <Link
                :href="`/stores/${store.id}/edit`"
                class="btn btn-sm btn-warning me-2"
              >
                Edit
              </Link>
              <button
                class="btn btn-sm btn-danger"
                @click="confirmDelete(store.id)"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

defineProps({
  stores: Array,
})

const confirmDelete = (id) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Toko yang dihapus tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/stores/${id}`, {
        onSuccess: () => {
          Swal.fire({
            title: 'Berhasil!',
            text: 'Toko berhasil dihapus.',
            icon: 'success',
            confirmButtonText: 'OK',
          })
        },
        onError: () => {
          Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus toko.',
            icon: 'error',
            confirmButtonText: 'OK',
          })
        },
      })
    }
  })
}
</script>
