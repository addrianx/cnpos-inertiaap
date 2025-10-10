<template>
    <AppLayout>
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
            <h2>Manajemen User</h2>

            <div class="mt-2 mt-md-0">
                <Link href="/users/create" class="btn btn-primary me-2 mb-2 mb-md-0">
                    + Tambah User
                </Link>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="row mb-3 g-2">
            <!-- Search + Filter status -->
            <div class="col-12 col-md d-flex gap-2">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Cari user..." 
                    class="form-control" 
                />
                <select v-model="selectedStatus" class="form-select w-auto">
                    <option value="">Status</option>
                    <option value="active">Aktif</option>
                    <option value="suspended">Ditangguhkan</option>
                </select>
                <select v-model="selectedRole" class="form-select w-auto">
                    <option value="">Role</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                        {{ role.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Table User -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-nowrap">
                <thead class="table-dark">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Toko</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loader -->
                    <tr v-if="tableLoading">
                        <td colspan="7" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Memuat data user...</div>
                        </td>
                    </tr>

                    <!-- Data kosong -->
                    <tr v-else-if="paginatedUsers.length === 0">
                        <td colspan="7" class="text-center text-muted py-4">
                            Tidak ada user.
                        </td>
                    </tr>

                    <!-- Data user -->
                    <tr v-else v-for="user in paginatedUsers" :key="user.id">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 32px; height: 32px; font-size: 14px;">
                                    {{ getInitials(user.name) }}
                                </div>
                                {{ user.name }}
                            </div>
                        </td>
                        <td>{{ user.email }}</td>
                        <td>
                            <span class="badge" :class="getRoleBadgeClass(user.roles[0]?.name)">
                                {{ user.roles[0]?.name || '-' }}
                            </span>
                        </td>
                        <td>
                            <div v-if="user.stores.length > 0">
                                <span v-for="store in user.stores" :key="store.id" 
                                      class="badge bg-secondary me-1">
                                    {{ store.name }}
                                </span>
                            </div>
                            <span v-else class="text-muted">-</span>
                        </td>
                        <td>
                            <span class="badge" :class="user.is_active ? 'bg-success' : 'bg-danger'">
                                {{ user.is_active ? 'Aktif' : 'Ditangguhkan' }}
                            </span>
                            <small v-if="user.suspended_at" class="text-muted d-block">
                                sejak {{ formatDate(user.suspended_at) }}
                            </small>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ formatDate(user.created_at) }}
                            </small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <Link 
                                    :href="`/users/${user.id}/edit`" 
                                    class="btn btn-sm btn-primary"
                                >
                                    Edit
                                </Link>
                                <button v-if="user.is_active" 
                                        class="btn btn-sm btn-warning" 
                                        @click="confirmSuspend(user)"
                                        :disabled="user.id === $page.props.auth.user.id">
                                    Suspend
                                </button>
                                <button v-else 
                                        class="btn btn-sm btn-success" 
                                        @click="confirmActivate(user)">
                                    Aktifkan
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                        @click="confirmDelete(user)"
                                        :disabled="user.id === $page.props.auth.user.id">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <li :class="['page-item', { disabled: currentPage === 1 }]">
                    <button class="page-link" @click="prevPage">&laquo;</button>
                </li>

                <li v-if="visiblePages[0] > 1" class="page-item">
                    <button class="page-link" @click="goToPage(1)">1</button>
                </li>

                <li v-if="visiblePages[0] > 2" class="page-item disabled">
                    <span class="page-link">...</span>
                </li>

                <li v-for="page in visiblePages" :key="page" :class="['page-item', { active: currentPage === page }]">
                    <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                </li>

                <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
                    <span class="page-link">...</span>
                </li>

                <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
                    <button class="page-link" @click="goToPage(totalPages)">{{ totalPages }}</button>
                </li>

                <li :class="['page-item', { disabled: currentPage === totalPages }]">
                    <button class="page-link" @click="nextPage">&raquo;</button>
                </li>
            </ul>
        </nav>
    </AppLayout>
</template>

<script setup>
    import { ref, computed, watch } from 'vue'
    import { Link, router } from '@inertiajs/vue3' // Import router untuk reload
    import AppLayout from '@/Layouts/AppLayout.vue'
    import Swal from 'sweetalert2'
    import axios from 'axios'

    const props = defineProps({
        users: Array,
        roles: Array,
        stores: Array
    })

    // Reactive state
    const perPage = ref(20)
    const currentPage = ref(1)
    const searchQuery = ref('')
    const selectedStatus = ref('')
    const selectedRole = ref('')
    const tableLoading = ref(false)

    // Gunakan reactive untuk userList agar lebih responsif
    const userList = ref([...props.users])
    
    const filteredUsers = computed(() => {
        let list = [...userList.value]

        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase()
            list = list.filter(user => 
                user.name.toLowerCase().includes(query) ||
                user.email.toLowerCase().includes(query) ||
                user.stores.some(store => store.name.toLowerCase().includes(query))
            )
        }

        if (selectedStatus.value) {
            list = list.filter(user => 
                selectedStatus.value === 'active' ? user.is_active : !user.is_active
            )
        }

        if (selectedRole.value) {
            list = list.filter(user => 
                user.roles.some(role => role.id == selectedRole.value)
            )
        }

        return list
    })

    const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage.value) || 1)

    const paginatedUsers = computed(() => {
        const start = (currentPage.value - 1) * perPage.value
        const end = start + perPage.value
        return filteredUsers.value.slice(start, end)
    })

    const visiblePages = computed(() => {
        const pages = []
        if (totalPages.value <= 5) {
            for (let i = 1; i <= totalPages.value; i++) pages.push(i)
            return pages
        }
        const delta = 2
        let start = Math.max(2, currentPage.value - delta)
        let end = Math.min(totalPages.value - 1, currentPage.value + delta)
        if (currentPage.value - delta <= 1) end = 5
        if (currentPage.value + delta >= totalPages.value) start = totalPages.value - 4
        for (let i = start; i <= end; i++) pages.push(i)
        return pages
    })

    // Methods
    const goToPage = (page) => {
        if (page >= 1 && page <= totalPages.value) currentPage.value = page
    }
    const prevPage = () => goToPage(currentPage.value - 1)
    const nextPage = () => goToPage(currentPage.value + 1)

    const formatDate = (dateString) => {
        if (!dateString) return '-'
        const date = new Date(dateString)
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        })
    }

    const getInitials = (name) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
    }

    const getRoleBadgeClass = (roleName) => {
        switch(roleName) {
            case 'admin': return 'bg-danger'
            case 'manager': return 'bg-warning text-dark'
            case 'staff': return 'bg-info'
            default: return 'bg-secondary'
        }
    }

    const showSuccessAlert = (message) => {
        return Swal.fire({
            title: 'Berhasil!',
            text: message,
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 2000,
            showConfirmButton: false
        })
    }

    const showErrorAlert = (message) => {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        })
    }

    // Fungsi untuk refresh data dari server
    const refreshUserData = async () => {
        try {
            tableLoading.value = true
            // Reload data dari server menggunakan Inertia
            router.reload({ only: ['users'] })
        } catch (error) {
            console.error('Error refreshing data:', error)
        } finally {
            tableLoading.value = false
        }
    }

    // Update user list ketika props berubah
    watch(() => props.users, (newUsers) => {
        userList.value = [...newUsers]
    })

    const confirmSuspend = async (user) => {
        const result = await Swal.fire({
            title: 'Konfirmasi Suspend',
            text: `Apakah Anda yakin ingin menangguhkan user ${user.name}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, tangguhkan!',
            cancelButtonText: 'Batal',
        })

        if (result.isConfirmed) {
            try {
                tableLoading.value = true
                
                const response = await axios.post(`/users/${user.id}/suspend`)
                
                if (response.data.success) {
                    // Update local state secara langsung
                    const index = userList.value.findIndex(u => u.id === user.id)
                    if (index !== -1) {
                        userList.value[index].is_active = false
                        userList.value[index].suspended_at = new Date().toISOString()
                    }
                    
                    // Tampilkan konfirmasi berhasil
                    await showSuccessAlert(`User ${user.name} berhasil ditangguhkan`)
                }
            } catch (error) {
                console.error('Error suspending user:', error)
                let errorMessage = 'Terjadi kesalahan saat menangguhkan user'
                
                if (error.response?.status === 403) {
                    errorMessage = 'Anda tidak memiliki izin untuk menangguhkan user ini'
                } else if (error.response?.status === 405) {
                    errorMessage = 'Method tidak didukung. Silakan hubungi administrator.'
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message
                }
                
                showErrorAlert(errorMessage)
            } finally {
                tableLoading.value = false
            }
        }
    }

    const confirmActivate = async (user) => {
        const result = await Swal.fire({
            title: 'Konfirmasi Aktivasi',
            text: `Apakah Anda yakin ingin mengaktifkan user ${user.name}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, aktifkan!',
            cancelButtonText: 'Batal',
        })

        if (result.isConfirmed) {
            try {
                tableLoading.value = true
                
                const response = await axios.post(`/users/${user.id}/activate`)
                
                if (response.data.success) {
                    // Update local state secara langsung
                    const index = userList.value.findIndex(u => u.id === user.id)
                    if (index !== -1) {
                        userList.value[index].is_active = true
                        userList.value[index].suspended_at = null
                    }
                    
                    await showSuccessAlert(`User ${user.name} berhasil diaktifkan`)
                }
            } catch (error) {
                console.error('Error activating user:', error)
                let errorMessage = 'Terjadi kesalahan saat mengaktifkan user'
                
                if (error.response?.status === 403) {
                    errorMessage = 'Anda tidak memiliki izin untuk mengaktifkan user ini'
                } else if (error.response?.status === 405) {
                    errorMessage = 'Method tidak didukung. Silakan hubungi administrator.'
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message
                }
                
                showErrorAlert(errorMessage)
            } finally {
                tableLoading.value = false
            }
        }
    }

    const confirmDelete = async (user) => {
        const result = await Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Apakah Anda yakin ingin menghapus user ${user.name}? Tindakan ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        })

        if (result.isConfirmed) {
            try {
                tableLoading.value = true
                
                // SOLUSI: Selalu gunakan POST dengan _method=DELETE untuk Laravel
                const response = await axios.post(`/users/${user.id}`, {
                    _method: 'DELETE'
                })
                
                // Jika berhasil, update UI
                if (response.data.success) {
                    // Hapus dari local state
                    userList.value = userList.value.filter(u => u.id !== user.id)
                    
                    // Tampilkan konfirmasi berhasil
                    await showSuccessAlert(response.data.message || `User ${user.name} berhasil dihapus`)
                    
                    // Jika halaman menjadi kosong setelah penghapusan, kembali ke halaman sebelumnya
                    if (paginatedUsers.value.length === 0 && currentPage.value > 1) {
                        currentPage.value = currentPage.value - 1
                    }
                } else {
                    // Jika server mengembalikan success: false
                    showErrorAlert(response.data.message || 'Gagal menghapus user')
                }
            } catch (error) {
                console.error('Error deleting user:', error)
                let errorMessage = 'Terjadi kesalahan saat menghapus user'
                
                if (error.response?.status === 403) {
                    errorMessage = 'Anda tidak memiliki izin untuk menghapus user ini'
                } else if (error.response?.status === 404) {
                    errorMessage = 'User tidak ditemukan'
                } else if (error.response?.status === 400) {
                    errorMessage = error.response.data.message || 'Tidak dapat menghapus user ini'
                } else if (error.response?.status === 405) {
                    // Method tidak didukung - coba reload data dari server
                    errorMessage = 'Method tidak didukung. Memuat ulang data...'
                    await refreshUserData()
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message
                }
                
                showErrorAlert(errorMessage)
            } finally {
                tableLoading.value = false
            }
        }
    }

    // Debounce untuk search dan filter
    let searchTimeout = null
    watch([searchQuery, selectedStatus, selectedRole], () => {
        tableLoading.value = true
        
        // Reset ke halaman 1 ketika filter berubah
        currentPage.value = 1
        
        if (searchTimeout) clearTimeout(searchTimeout)
        searchTimeout = setTimeout(() => {
            tableLoading.value = false
        }, 500)
    })

    watch([perPage, currentPage], () => {
        tableLoading.value = true
        setTimeout(() => {
            tableLoading.value = false
        }, 300)
    })
</script>

<style scoped>
.avatar {
    font-weight: bold;
}
</style>