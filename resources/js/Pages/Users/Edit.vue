<template>
    <AppLayout>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Loading state -->
                <div v-if="!user" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Memuat data user...</div>
                </div>

                <!-- Content ketika user sudah loaded -->
                <div v-else class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Edit User</h4>
                            <Link href="/users" class="btn btn-secondary btn-sm">
                                &larr; Kembali
                            </Link>
                        </div>
                    </div>

                    <div class="card-body">
                        <form @submit.prevent="updateUser">
                            <!-- Nama -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="name"
                                    v-model="form.name"
                                    :class="{ 'is-invalid': errors.name }"
                                    required
                                >
                                <div v-if="errors.name" class="invalid-feedback">
                                    {{ errors.name }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    v-model="form.email"
                                    :class="{ 'is-invalid': errors.email }"
                                    required
                                >
                                <div v-if="errors.email" class="invalid-feedback">
                                    {{ errors.email }}
                                </div>
                            </div>

                            <!-- Role -->
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select
                                    class="form-select"
                                    id="role_id"
                                    v-model="form.role_id"
                                    :class="{ 'is-invalid': errors.role_id }"
                                    required
                                >
                                    <option value="">Pilih Role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <div v-if="errors.role_id" class="invalid-feedback">
                                    {{ errors.role_id }}
                                </div>
                            </div>

                            <!-- Status (readonly) -->
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div>
                                    <span class="badge" :class="user.is_active ? 'bg-success' : 'bg-danger'">
                                        {{ user.is_active ? 'Aktif' : 'Ditangguhkan' }}
                                    </span>
                                    <small v-if="user.suspended_at" class="text-muted ms-2">
                                        sejak {{ formatDate(user.suspended_at) }}
                                    </small>
                                </div>
                            </div>

                            <!-- Tanggal Dibuat (readonly) -->
                            <div class="mb-3">
                                <label class="form-label">Tanggal Dibuat</label>
                                <div class="text-muted">
                                    {{ formatDate(user.created_at) }}
                                </div>
                            </div>

                            <!-- Tombol Submit -->
                            <div class="d-flex gap-2">
                                <button 
                                    type="submit" 
                                    class="btn btn-primary"
                                    :disabled="loading"
                                >
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                                
                                <Link href="/users" class="btn btn-secondary" :disabled="loading">
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
    user: Object,
    roles: Array
})

// State
const loading = ref(false)
const errors = ref({})

// Computed untuk handle undefined user
const safeUser = computed(() => {
    return props.user || {}
})

// Form data dengan safe default values
const form = reactive({
    name: '',
    email: '',
    role_id: ''
})

// Function untuk reset form dengan data terbaru
const resetForm = () => {
    if (safeUser.value) {
        form.name = safeUser.value.name || ''
        form.email = safeUser.value.email || ''
        form.role_id = safeUser.value.roles?.[0]?.id || ''
    }
}

// Reset form ketika component dibuat atau props berubah
onMounted(() => {
    console.log('Component mounted, user:', safeUser.value)
    resetForm()
})

// Watch untuk perubahan props.user
watch(() => safeUser.value, (newUser) => {
    console.log('User data changed, resetting form...', newUser)
    resetForm()
}, { deep: true })

// Format date dengan safe handling
const formatDate = (dateString) => {
    if (!dateString) return '-'
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        })
    } catch (error) {
        console.error('Error formatting date:', error)
        return '-'
    }
}

// ✅ PERBAIKAN: Function untuk get CSRF token yang lebih robust
const getCsrfToken = () => {
    // Coba dari berbagai sumber
    const tokenFromMeta = document.querySelector('meta[name="csrf-token"]')?.content
    const tokenFromPageProps = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    console.log('CSRF Token sources:', {
        fromMeta: tokenFromMeta,
        fromPageProps: tokenFromPageProps
    })
    
    return tokenFromMeta || tokenFromPageProps
}

// Update user dengan Axios
const updateUser = async () => {
    // Validasi sebelum submit
    if (!safeUser.value.id) {
        Swal.fire({
            title: 'Error!',
            text: 'Data user tidak valid.',
            icon: 'error',
            confirmButtonText: 'OK'
        })
        return
    }

    loading.value = true
    errors.value = {}
    
    try {
        // Get CSRF token
        const csrfToken = getCsrfToken()
        
        if (!csrfToken) {
            Swal.fire({
                title: 'CSRF Token Error!',
                text: 'Silakan refresh halaman dan coba lagi.',
                icon: 'error',
                confirmButtonText: 'Refresh'
            }).then(() => {
                window.location.reload()
            })
            return
        }

        const payload = {
            _method: 'PUT',
            name: form.name,
            email: form.email,
            role_id: form.role_id
        }

        console.log('Mengupdate user:', safeUser.value.id, 'dengan payload:', payload)
        console.log('CSRF Token:', csrfToken)

        const response = await axios.post(`/users/${safeUser.value.id}`, payload, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })

        // ✅ PERBAIKAN: Success handling dengan force refresh
        Swal.fire({
            title: 'Berhasil!',
            text: 'Data user berhasil diperbarui.',
            icon: 'success',
            confirmButtonText: 'OK',
            willClose: () => {
                // ✅ Force redirect ke index dengan cache busting
                router.visit('/users', {
                    preserveState: false, // Force fresh data
                    preserveScroll: true,
                    replace: true,
                    // ✅ Tambahkan cache busting parameter
                    data: {
                        _t: Date.now() // Timestamp untuk cache busting
                    }
                })
            }
        })
        
    } catch (err) {
        console.error('Error updating user:', err)
        
        // Handle CSRF token mismatch (419)
        if (err.response?.status === 419) {
            Swal.fire({
                title: 'Session Expired!',
                text: 'Silakan refresh halaman dan coba lagi.',
                icon: 'warning',
                confirmButtonText: 'Refresh'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload()
                }
            })
            return
        }
        
        // Handle validation errors
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {}
            
            Swal.fire({
                title: 'Validasi Gagal!',
                text: 'Terdapat kesalahan dalam pengisian form.',
                icon: 'error',
                confirmButtonText: 'OK'
            })
        } 
        // Handle 403 Forbidden
        else if (err.response?.status === 403) {
            Swal.fire({
                title: 'Akses Ditolak!',
                text: err.response?.data?.message || 'Anda tidak memiliki izin untuk mengubah data user.',
                icon: 'warning',
                confirmButtonText: 'OK'
            })
        }
        // Handle 404 Not Found
        else if (err.response?.status === 404) {
            Swal.fire({
                title: 'User Tidak Ditemukan!',
                text: 'User yang ingin diedit tidak ditemukan.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                router.visit('/users')
            })
        }
        // Handle other errors
        else {
            Swal.fire({
                title: 'Gagal!',
                text: err.response?.data?.message || 'Terjadi kesalahan saat memperbarui user.',
                icon: 'error',
                confirmButtonText: 'OK'
            })
        }
    } finally {
        loading.value = false
    }
}
</script>