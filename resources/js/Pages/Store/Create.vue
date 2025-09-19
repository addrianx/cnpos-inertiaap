<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Tambah Toko Baru</h2>
      <Link href="/stores" class="btn btn-secondary">← Kembali</Link>
    </div>

    <!-- Form Tambah Toko -->
    <div class="card mb-4">
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
            <select v-model="formStore.user_id" class="form-select" required>
              <option value="" disabled>Pilih user yang akan jadi manager</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
            <small class="text-muted">
              Jika user belum ada, tambahkan lewat form di bawah.
            </small>
          </div>

          <!-- Tombol Simpan -->
          <button type="submit" class="btn btn-success">Simpan Toko</button>
        </form>
      </div>
    </div>

    <!-- Form Tambah User Baru -->
    <div class="card">
      <div class="card-header bg-secondary text-white">
        Tambah User Baru (Manager Toko)
      </div>
      <div class="card-body">
        <form @submit.prevent="submitUser">
          <!-- Nama -->
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input
              v-model="formUser.name"
              type="text"
              class="form-control"
              placeholder="Nama lengkap"
              required
            />
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input
              v-model="formUser.email"
              type="email"
              class="form-control"
              placeholder="Email aktif"
              required
            />
          </div>

          <!-- Password -->
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input
              v-model="formUser.password"
              type="password"
              class="form-control"
              placeholder="Minimal 6 karakter"
              required
            />
          </div>

          <!-- Pilih Role -->
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select v-model="formUser.role_id" class="form-select" required>
              <option value="" disabled>Pilih role untuk user ini</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.label }}
              </option>
            </select>
            <small class="text-muted">Role menentukan hak akses user di sistem</small>
          </div>

          <!-- Tombol Simpan User -->
          <button type="submit" class="btn btn-primary">Simpan User</button>
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
  roles: Array, // daftar roles dari controller
})

// Form Toko
const formStore = reactive({
  name: '',
  address: '',
  user_id: '',
})

// Form User Baru
const formUser = reactive({
  name: '',
  email: '',
  password: '',
  role_id: '', // tambahan role
})

// Submit Toko
const submitStore = () => {
  router.post('/stores', formStore, {
    onSuccess: () => {
      Swal.fire('Berhasil!', 'Toko berhasil ditambahkan.', 'success')
      formStore.name = ''
      formStore.address = ''
      formStore.user_id = ''
    },
    onError: () => {
      Swal.fire('Gagal!', 'Terjadi kesalahan saat menambahkan toko.', 'error')
    },
  })
}

// Submit User
const submitUser = () => {
  // Buat payload plain object dari reactive formUser
  const payload = {
    name: formUser.name,
    email: formUser.email,
    password: formUser.password,
    role_id: formUser.role_id,
  }

  router.post('/users', payload, {
    onSuccess: () => {
      Swal.fire('Berhasil!', 'User baru berhasil ditambahkan.', 'success')
      // Reset form
      formUser.name = ''
      formUser.email = ''
      formUser.password = ''
      formUser.role_id = ''
    },
    onError: (errors) => {
      Swal.fire('Gagal!', 'Terjadi kesalahan saat menambahkan user.', 'error')
      console.log(errors)
    },
  })
}


</script>
