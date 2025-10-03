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

          <!-- Pilih User -->
          <div class="mb-3">
            <label class="form-label">User Toko</label>
            <div v-if="users.length > 0">
              <div v-for="user in users" :key="user.id" class="form-check mb-2">
                <input
                  type="checkbox"
                  class="form-check-input"
                  :id="'user-' + user.id"
                  :value="user.id"
                  v-model="formStore.user_ids"
                />
                <label class="form-check-label d-flex align-items-center gap-2" :for="'user-' + user.id">
                  <div>
                    <strong>{{ user.name }}</strong> ({{ user.email }})
                  </div>
                  <div class="d-flex gap-1">
                    <span class="badge" :class="getRoleBadgeClass(getUserRoleId(user))">
                      {{ getUserRoleName(getUserRoleId(user)) }}
                    </span>
                    <span v-if="isCurrentStoreUser(user.id)" class="badge bg-info">
                      User Toko Ini
                    </span>
                  </div>
                </label>
              </div>
            </div>
            <div v-else class="alert alert-warning">
              Tidak ada user yang tersedia.
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
  store: Object,
  users: Array,
})

const formStore = reactive({
  name: props.store.name,
  address: props.store.address,
  user_ids: props.store.users?.map(u => u.id) || [],
})

// ✅ PERBAIKAN: Ambil role_id dari relationship
const getUserRoleId = (user) => {
  // Akses role melalui relationship
  return user.roles?.[0]?.id || null
}

const getUserRoleName = (roleId) => {
  const roles = {
    1: 'Admin',
    2: 'Manager', 
    3: 'Kasir'
  }
  return roles[roleId] || 'Unknown'
}

const getRoleBadgeClass = (roleId) => {
  const classes = {
    1: 'bg-danger',
    2: 'bg-primary',  
    3: 'bg-secondary'
  }
  return classes[roleId] || 'bg-dark'
}

const isCurrentStoreUser = (userId) => {
  return props.store.users?.some(user => user.id === userId) || false
}

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