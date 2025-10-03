<template>
  <AppLayout>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>🔍 Detail Rakitan PC</h2>
      <div>
        <Link href="/pc-assembly/history" class="btn btn-outline-secondary me-2">
          ← Kembali ke History
        </Link>
        <Link href="/pc-assembly" class="btn btn-primary">
          🛠️ Buat Rakitan Baru
        </Link>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-dark text-white">
        <h4 class="mb-0">{{ assembly.name }}</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <div v-if="assembly.description" class="mb-3">
              <strong>Deskripsi:</strong>
              <p class="mb-0">{{ assembly.description }}</p>
            </div>

            <h5>📦 Daftar Komponen</h5>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Komponen</th>
                    <th>Harga</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="component in assembly.components" :key="component.id">
                    <td>
                      <strong>{{ component.name }}</strong>
                      <div class="text-muted small">{{ component.description }}</div>
                    </td>
                    <td>Rp {{ formatPrice(component.selling_price) }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="table-success">
                    <td><strong>Total</strong></td>
                    <td><strong>Rp {{ formatPrice(assembly.total_price) }}</strong></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0">📊 Informasi Rakitan</h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <strong>Dibuat Oleh:</strong><br>
                  {{ assembly.user.name }}
                </div>
                <div class="mb-3">
                  <strong>Toko:</strong><br>
                  {{ assembly.store.name }}
                </div>
                <div class="mb-3">
                  <strong>Tanggal Dibuat:</strong><br>
                  {{ formatDateTime(assembly.created_at) }}
                </div>
                <div class="mb-3">
                  <strong>Jumlah Komponen:</strong><br>
                  {{ assembly.components.length }} items
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  assembly: Object
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('id-ID')
}
</script>