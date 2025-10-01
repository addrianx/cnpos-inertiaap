<template>
  <div class="container mt-5">
    <h2>Buat Password Baru</h2>
    <p>Akun: {{ email }}</p>
    <form @submit.prevent="submitPassword">
      <div class="mb-3">
        <label>Password Baru</label>
        <input v-model="form.password" type="password" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Konfirmasi Password</label>
        <input v-model="form.password_confirmation" type="password" class="form-control" required />
      </div>
      <button class="btn btn-primary">Simpan Password</button>
    </form>
  </div>
</template>


<script setup>
import { reactive } from 'vue'
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

const submitPassword = () => {
  if (form.password !== form.password_confirmation) {
    Swal.fire('Error', 'Password dan konfirmasi tidak sama', 'error')
    return
  }

  router.post('/set-password', {
    user_id: props.userId,
    password: form.password,
    password_confirmation: form.password_confirmation,
  }, {
    onSuccess: () => {
      Swal.fire('Berhasil', 'Password berhasil dibuat, silakan login', 'success')
      router.visit('/login')
    },
    onError: (errors) => {
      Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan password', 'error')
      console.log(errors)
    },
  })
}

</script>
