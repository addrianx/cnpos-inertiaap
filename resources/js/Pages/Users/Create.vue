<template>
  <AppLayout>
    <h2>Tambah User Baru</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Nama -->
      <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input v-model="form.name" type="text" class="form-control" />
        <div v-if="form.errors.name" class="text-danger small">{{ form.errors.name }}</div>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input v-model="form.email" type="email" class="form-control" />
        <div v-if="form.errors.email" class="text-danger small">{{ form.errors.email }}</div>
      </div>

      <!-- Role -->
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select v-model="form.role_id" class="form-select">
          <option value="" disabled>Pilih role</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
          </option>
        </select>
        <div v-if="form.errors.role_id" class="text-danger small">{{ form.errors.role_id }}</div>
      </div>

      <!-- Tombol -->
      <button type="submit" class="btn btn-success">Simpan User</button>
      <Link href="/users" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  roles: Array,
  stores: Array
})

const form = useForm({
  name: '',
  email: '',
  role_id: '',
})

const submit = () => {
  form.post('/users')
}
</script>