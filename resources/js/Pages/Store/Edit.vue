<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Edit Toko</h2>
      <Link href="/stores" class="btn btn-secondary">← Kembali</Link>
    </div>

    <div class="card">
      <div class="card-header bg-dark text-white">Form Edit Toko</div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <!-- Nama Toko -->
          <div class="mb-3">
            <label class="form-label">Nama Toko</label>
            <input
              v-model="form.name"
              type="text"
              class="form-control"
              @blur="formatStoreName"
              placeholder="Masukkan nama toko"
              required
            />
            <div class="form-text text-muted">
              Nama toko akan otomatis dikapitalisasi
            </div>
          </div>

          <!-- Alamat -->
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea
              v-model="form.address"
              class="form-control"
              rows="3"
              @blur="formatAddress"
              placeholder="Alamat lengkap toko"
              required
            ></textarea>
            <div class="form-text text-muted">
              Alamat akan otomatis dikapitalisasi
            </div>
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
                  v-model="form.user_ids"
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
          <button type="submit" class="btn btn-success" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ loading ? 'Sedang menyimpan...' : 'Update Toko' }}
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  store: Object,
  users: Array,
})

const loading = ref(false)

// Gunakan reactive object biasa untuk form
const form = ref({
  name: props.store.name,
  address: props.store.address,
  user_ids: props.store.users?.map(u => u.id) || [],
})

// Function untuk format nama toko dengan capitalize
const formatStoreName = () => {
  if (form.value.name && form.value.name.trim()) {
    form.value.name = form.value.name
      .toLowerCase()
      .split(' ')
      .map(word => {
        if (word.trim() === '') return ''
        return word.charAt(0).toUpperCase() + word.slice(1)
      })
      .join(' ')
      .replace(/\s+/g, ' ')
      .trim()
  }
}

// Function untuk format alamat dengan capitalize
const formatAddress = () => {
  if (form.value.address && form.value.address.trim()) {
    // Capitalize setiap kalimat dalam alamat
    form.value.address = form.value.address
      .toLowerCase()
      .split('. ')
      .map(sentence => {
        if (sentence.trim() === '') return ''
        return sentence.charAt(0).toUpperCase() + sentence.slice(1)
      })
      .join('. ')
      .split(', ')
      .map(part => {
        if (part.trim() === '') return ''
        return part.charAt(0).toUpperCase() + part.slice(1)
      })
      .join(', ')
      .replace(/\s+/g, ' ')
      .trim()
  }
}

// ✅ PERBAIKAN: Ambil role_id dari relationship
const getUserRoleId = (user) => {
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

// ✅ PERBAIKAN: Gunakan axios.post dengan _method seperti form produk yang berhasil
const submit = async () => {
  // Format nama dan alamat sebelum submit
  formatStoreName()
  formatAddress()
  
  loading.value = true
  try {
    const payload = {
      _method: 'put', // Method spoofing untuk PUT request
      name: form.value.name,
      address: form.value.address,
      user_ids: form.value.user_ids,
    }

    await axios.post(`/stores/${props.store.id}`, payload)
    
    Swal.fire({
      title: 'Berhasil!',
      text: 'Toko berhasil diperbarui.',
      icon: 'success',
      confirmButtonText: 'OK'
    }).then(() => {
      window.location.href = '/stores'
    })
    
  } catch (err) {
    console.error('Error updating store:', err.response?.data)
    
    Swal.fire({
      title: 'Gagal!',
      text: err.response?.data?.message || 'Terjadi kesalahan saat memperbarui toko.',
      icon: 'error',
      confirmButtonText: 'OK'
    })
  } finally {
    loading.value = false
  }
}

// Reset form saat mount untuk mencegah data tersimpan dari bfcache
onMounted(() => {
  form.value = {
    name: props.store.name,
    address: props.store.address,
    user_ids: props.store.users?.map(u => u.id) || [],
  }
})
</script>

<style scoped>
.card {
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
  border-bottom: 1px solid #dee2e6;
  font-weight: 600;
}

.form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

.form-check-input:checked {
  background-color: #198754;
  border-color: #198754;
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
  color: #6c757d;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffecb5;
  color: #664d03;
}

.badge {
  font-size: 0.75rem;
}
</style>