<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center auth-container">
    <div class="card shadow-lg border-0 auth-card">
      <div class="card-body p-5">
        <!-- Header -->
        <div class="text-center mb-4">
          <div class="auth-icon mb-3">
            <i class="fas fa-lock"></i>
          </div>
          <h2 class="auth-title">Buat Password Baru</h2>
          <p class="text-muted auth-subtitle">Lengkapi pengaturan akun Anda</p>
        </div>

        <!-- User Info -->
        <div class="user-info-card mb-4">
          <div class="d-flex align-items-center">
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div class="ms-3">
              <p class="user-email mb-0">{{ email }}</p>
              <small class="text-muted">Akun yang akan diaktifkan</small>
            </div>
          </div>
        </div>

        <!-- Password Form -->
        <form @submit.prevent="submitPassword" class="auth-form">
          <!-- New Password -->
          <div class="form-group mb-4">
            <label class="form-label">Password Baru</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-key"></i>
              </span>
              <input 
                v-model="form.password" 
                :type="showPassword ? 'text' : 'password'" 
                class="form-control" 
                placeholder="Masukkan password baru"
                required
              />
              <button 
                type="button" 
                class="btn btn-outline-secondary"
                @click="showPassword = !showPassword"
              >
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <div class="form-text">
              Password minimal 8 karakter
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group mb-4">
            <label class="form-label">Konfirmasi Password</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-key"></i>
              </span>
              <input 
                v-model="form.password_confirmation" 
                :type="showConfirmPassword ? 'text' : 'password'" 
                class="form-control" 
                placeholder="Ulangi password baru"
                required
              />
              <button 
                type="button" 
                class="btn btn-outline-secondary"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="btn btn-primary w-100 auth-submit-btn"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Menyimpan...' : 'Simpan Password' }}
          </button>
        </form>

        <!-- Additional Info -->
        <div class="text-center mt-4">
          <small class="text-muted">
            Setelah menyimpan password, Anda akan diarahkan ke halaman login
          </small>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({
  userId: Number,
  email: String,
})

const form = reactive({
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const submitPassword = async () => {
  // Validation
  if (form.password.length < 8) {
    Swal.fire({
      title: 'Password Terlalu Pendek',
      text: 'Password harus minimal 8 karakter',
      icon: 'warning',
      confirmButtonText: 'OK'
    })
    return
  }

  if (form.password !== form.password_confirmation) {
    Swal.fire({
      title: 'Password Tidak Sesuai',
      text: 'Password dan konfirmasi password harus sama',
      icon: 'error',
      confirmButtonText: 'OK'
    })
    return
  }

  loading.value = true

  try {
    await router.post('/set-password', {
      user_id: props.userId,
      password: form.password,
      password_confirmation: form.password_confirmation,
    }, {
      onSuccess: () => {
        Swal.fire({
          title: 'Berhasil!',
          text: 'Password berhasil dibuat. Silakan login dengan password baru Anda.',
          icon: 'success',
          confirmButtonText: 'Login Sekarang',
          timer: 5000,
          timerProgressBar: true
        }).then(() => {
          router.visit('/login')
        })
      },
      onError: (errors) => {
        const errorMessage = errors.password ? errors.password[0] : 'Terjadi kesalahan saat menyimpan password'
        Swal.fire({
          title: 'Gagal',
          text: errorMessage,
          icon: 'error',
          confirmButtonText: 'OK'
        })
      },
    })
  } catch (error) {
    console.error('Error:', error)
    Swal.fire({
      title: 'Error',
      text: 'Terjadi kesalahan sistem',
      icon: 'error',
      confirmButtonText: 'OK'
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-container {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  min-height: 100vh;
}

.auth-card {
  width: 100%;
  max-width: 450px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
}

.auth-icon {
  font-size: 3rem;
  color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.auth-icon i {
  color: white;
}

.auth-title {
  color: #2c3e50;
  font-weight: 700;
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
}

.auth-subtitle {
  color: #6c757d;
  font-size: 0.95rem;
}

.user-info-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.25rem;
  border-left: 4px solid #667eea;
}

.user-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
}

.user-email {
  font-weight: 600;
  color: #2c3e50;
  font-size: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.input-group {
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #e9ecef;
  transition: all 0.3s ease;
}

.input-group:focus-within {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group-text {
  background: #f8f9fa;
  border: none;
  color: #667eea;
  padding: 0.75rem 1rem;
}

.form-control {
  border: none;
  padding: 0.75rem;
  font-size: 1rem;
}

.form-control:focus {
  box-shadow: none;
  background: transparent;
}

.form-control::placeholder {
  color: #6c757d;
  opacity: 0.7;
}

.form-text {
  font-size: 0.8rem;
  color: #6c757d;
  margin-top: 0.25rem;
}

.auth-submit-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 12px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  height: 50px;
}

.auth-submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.auth-submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.btn-outline-secondary {
  border: none;
  color: #6c757d;
  padding: 0.75rem 1rem;
}

.btn-outline-secondary:hover {
  background: #f8f9fa;
  color: #495057;
}

/* Responsive Design */
@media (max-width: 576px) {
  .auth-container {
    padding: 15px;
  }
  
  .auth-card {
    margin: 0;
    border-radius: 15px;
  }
  
  .card-body {
    padding: 2rem !important;
  }
  
  .auth-title {
    font-size: 1.5rem;
  }
  
  .auth-icon {
    width: 70px;
    height: 70px;
    font-size: 2.5rem;
  }
  
  .user-info-card {
    padding: 1rem;
  }
  
  .user-avatar {
    width: 45px;
    height: 45px;
    font-size: 1.1rem;
  }
}

@media (max-width: 400px) {
  .card-body {
    padding: 1.5rem !important;
  }
  
  .auth-title {
    font-size: 1.3rem;
  }
  
  .form-control {
    font-size: 0.9rem;
  }
  
  .auth-submit-btn {
    height: 45px;
    font-size: 0.9rem;
  }
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-card {
  animation: fadeIn 0.6s ease-out;
}

/* Loading state improvements */
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}
</style>