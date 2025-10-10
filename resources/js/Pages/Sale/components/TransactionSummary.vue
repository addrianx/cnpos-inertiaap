<template>
  <div class="card mb-4">
    <div class="card-header bg-info text-white">
      <h5 class="card-title mb-0">Ringkasan Transaksi</h5>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Tanggal Penjualan</label>
        <input
          type="date"
          v-model="form.sale_date"
          class="form-control"
        >
      </div>

      <div class="border-top pt-3">
        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal:</span>
          <strong>Rp {{ subtotal.toLocaleString() }}</strong>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Total Diskon:</span>
          <strong class="text-danger">- Rp {{ totalDiscount.toLocaleString() }}</strong>
        </div>
        <div class="d-flex justify-content-between mb-3 fs-5 fw-bold">
          <span>Total:</span>
          <span class="text-primary">Rp {{ totalTransaction.toLocaleString() }}</span>
        </div>
      </div>

      <div class="border-top pt-3">
        <div class="mb-3">
          <label class="form-label fw-bold">Dibayar</label>
          <input
            :value="paidFormatted"
            type="text"
            class="form-control form-control-lg"
            placeholder="0"
            @input="handlePaidInput"
            :class="{ 'is-invalid': paid < totalTransaction }"
          >
          <div v-if="paid < totalTransaction" class="invalid-feedback">
            Jumlah pembayaran kurang
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Kembalian</label>
          <div 
            class="form-control form-control-lg fw-bold text-center"
            :class="change >= 0 ? 'text-success bg-light-success' : 'text-danger bg-light-danger'"
          >
            {{ change >= 0 ? 'Rp ' + change.toLocaleString() : 'Kurang: Rp ' + Math.abs(change).toLocaleString() }}
          </div>
        </div>
      </div>

      <button 
        type="button" 
        class="btn btn-success btn-lg w-100 mt-3"
        @click="$emit('submit-sale')"
        :disabled="!canSubmit"
      >
        <i class="bi bi-check-circle me-2"></i>
        {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  form: Object,
  items: Array,
  subtotal: Number,
  totalDiscount: Number,
  totalTransaction: Number,
  paid: Number,
  change: Number,
  canSubmit: Boolean
});

const emit = defineEmits(['update:paid', 'submit-sale']);

const paidFormatted = ref(props.paid.toLocaleString('id-ID'));

const handlePaidInput = (event) => {
  let value = event.target.value.replace(/\D/g, "");
  
  if (value) {
    const numericValue = parseInt(value, 10);
    paidFormatted.value = numericValue.toLocaleString("id-ID");
    emit('update:paid', numericValue);
  } else {
    paidFormatted.value = "0";
    emit('update:paid', 0);
  }
};

watch(() => props.paid, (newValue) => {
  paidFormatted.value = newValue.toLocaleString('id-ID');
});
</script>