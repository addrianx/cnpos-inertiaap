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
              v-model="formStore.address"
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
          <button 
            type="submit" 
            class="btn btn-success"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Toko' }}
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import { reactive, ref } from 'vue'

defineProps({
  users: Array, // untuk dropdown User Manager
})

const isSubmitting = ref(false)

// Form Toko
const formStore = reactive({
  name: '',
  address: '',
  user_ids: [],
})

// Function untuk format nama toko dengan capitalize
const formatStoreName = () => {
  if (formStore.name && formStore.name.trim()) {
    formStore.name = formStore.name
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
  if (formStore.address && formStore.address.trim()) {
    // Capitalize setiap kalimat dalam alamat
    formStore.address = formStore.address
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

// Submit Toko
const submitStore = () => {
  // Format nama dan alamat sebelum submit
  formatStoreName()
  formatAddress()
  
  isSubmitting.value = true
  
  router.post('/stores', formStore, {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Toko berhasil ditambahkan.',
        icon: 'success',
        confirmButtonColor: '#28a745',
        timer: 2000
      })
      // Reset form
      formStore.name = ''
      formStore.address = ''
      formStore.user_ids = []
    },
    onError: (errors) => {
      let errorMessage = 'Terjadi kesalahan saat menambahkan toko.'
      if (errors.message) {
        errorMessage = errors.message
      } else if (errors.name) {
        errorMessage = errors.name[0]
      } else if (errors.address) {
        errorMessage = errors.address[0]
      }
      
      Swal.fire({
        title: 'Gagal!',
        text: errorMessage,
        icon: 'error',
        confirmButtonColor: '#dc3545'
      })
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
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
</style>