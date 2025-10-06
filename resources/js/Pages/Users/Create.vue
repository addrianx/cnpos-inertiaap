<template>
  <AppLayout>
    <h2>Tambah User Baru</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Nama -->
      <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input 
          v-model="form.name" 
          type="text" 
          class="form-control" 
          @blur="formatName"
          @keydown.space="handleSpace"
          placeholder="Masukkan nama lengkap"
          ref="nameInput"
        />
        <div class="form-text text-muted">
          Nama akan otomatis dikapitalisasi saat selesai mengetik
        </div>
        <div v-if="form.errors.name" class="text-danger small">{{ form.errors.name }}</div>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input 
          v-model="form.email" 
          type="email" 
          class="form-control" 
          placeholder="contoh@email.com"
        />
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
      <div class="d-flex gap-2">
        <button 
          type="submit" 
          class="btn btn-success"
          :disabled="form.processing"
        >
          <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
          {{ form.processing ? 'Menyimpan...' : 'Simpan User' }}
        </button>
        <Link href="/users" class="btn btn-secondary">Batal</Link>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  roles: Array,
  stores: Array
})

const form = useForm({
  name: '',
  email: '',
  role_id: '',
})

const nameInput = ref(null)

// Function untuk format nama dengan capitalize
const formatName = () => {
  if (form.name && form.name.trim()) {
    // Capitalize setiap kata dalam nama
    const formattedName = form.name
      .toLowerCase()
      .split(' ')
      .map(word => {
        // Handle multiple spaces
        if (word.trim() === '') return ''
        return word.charAt(0).toUpperCase() + word.slice(1)
      })
      .join(' ')
      .replace(/\s+/g, ' ') // Replace multiple spaces with single space
      .trim()
    
    // Only update if different to prevent cursor jumping
    if (formattedName !== form.name) {
      form.name = formattedName
    }
  }
}

// Handle space key to allow normal space input
const handleSpace = (event) => {
  // Allow normal space behavior
  // No interference with space key
}

const submit = () => {
  // Pastikan nama sudah terformat sebelum submit
  formatName()
  
  form.post('/users', {
    onSuccess: () => {
      // Reset form setelah berhasil
      form.reset()
    }
  })
}
</script>

<style scoped>
.form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

.form-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.form-text {
  font-size: 0.875rem;
}
</style>