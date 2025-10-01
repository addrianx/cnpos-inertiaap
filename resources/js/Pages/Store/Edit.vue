<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Edit Toko</h2>
      <Link href="/stores" class="btn btn-secondary">← Kembali</Link>
    </div>

    <div class="card">
      <div class="card-header bg-dark text-white">Form Edit Toko</div>
      <div class="card-body">
        <form @submit.prevent="updateStore">
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

          <!-- Tombol Update -->
          <button type="submit" class="btn btn-success">Update Toko</button>
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

const props = defineProps({
  store: Object, // data toko yang akan diedit
  users: Array,  // untuk dropdown user
})

const formStore = reactive({
  name: props.store.name,
  address: props.store.address,
  user_ids: props.store.users?.map(u => u.id) || [],
})

const updateStore = () => {
  router.put(`/stores/${props.store.id}`, formStore, {
    onSuccess: () => {
      Swal.fire('Berhasil!', 'Toko berhasil diperbarui.', 'success')
    },
    onError: () => {
      Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui toko.', 'error')
    },
  })
}
</script>

