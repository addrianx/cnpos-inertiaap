<template>
    <AppLayout>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
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
                                    :class="{ 'is-invalid': form.errors.name }"
                                    required
                                >
                                <div v-if="form.errors.name" class="invalid-feedback">
                                    {{ form.errors.name }}
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
                                    :class="{ 'is-invalid': form.errors.email }"
                                    required
                                >
                                <div v-if="form.errors.email" class="invalid-feedback">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Role -->
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select
                                    class="form-select"
                                    id="role_id"
                                    v-model="form.role_id"
                                    :class="{ 'is-invalid': form.errors.role_id }"
                                    required
                                >
                                    <option value="">Pilih Role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.role_id" class="invalid-feedback">
                                    {{ form.errors.role_id }}
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
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                                
                                <Link href="/users" class="btn btn-secondary" :disabled="form.processing">
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
import { reactive } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    roles: Array
})

// Form setup
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role_id: props.user.roles[0]?.id || ''
})

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

// Update user
const updateUser = () => {
    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia
        },
        onError: (errors) => {
            console.error('Error updating user:', errors)
        }
    })
}
</script>