<template>
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0">Input Item</h5>
    </div>
    <div class="card-body">
      <!-- Tipe Item -->
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label fw-bold">Tipe Item</label>
          <div class="btn-group w-100" role="group">
            <input 
              type="radio" 
              class="btn-check" 
              name="itemType" 
              id="productType" 
              v-model="localItem.type" 
              value="product"
              checked
            >
            <label class="btn btn-outline-primary" for="productType">Produk</label>
            
            <input 
              type="radio" 
              class="btn-check" 
              name="itemType" 
              id="serviceType" 
              v-model="localItem.type" 
              value="service"
            >
            <label class="btn btn-outline-primary" for="serviceType">Jasa</label>
          </div>
        </div>
      </div>

      <!-- Form Input Berdasarkan Tipe -->
      <div class="row g-3">
        <!-- Input Produk -->
        <template v-if="localItem.type === 'product'">
          <div class="col-md-6">
            <label class="form-label">Pilih Produk</label>
            <select 
              v-model="localItem.product_id"
              class="form-select"
              @change="$emit('product-select')"
            >
              <option value="">-- Pilih Produk --</option>
              <option 
                v-for="product in availableProducts" 
                :key="product.id" 
                :value="product.id"
                :disabled="product.stock <= 0"
              >
                {{ product.name }} - Rp {{ product.price.toLocaleString() }} 
                <span v-if="product.stock <= 0" class="text-danger">(Stok Habis)</span>
                <span v-else>(Stok: {{ product.stock }})</span>
              </option>
            </select>
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Jumlah</label>
            <input
              v-model.number="localItem.quantity"
              type="number"
              class="form-control"
              min="1"
              :max="selectedProduct?.stock || 1"
              @change="$emit('validate-quantity')"
            >
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Diskon (%)</label>
            <input
              v-model.number="localItem.discount"
              type="number"
              class="form-control"
              min="0"
              max="100"
            >
          </div>
        </template>

        <!-- Input Jasa -->
        <template v-else>
          <div class="col-md-4">
            <label class="form-label">Nama Jasa</label>
            <input
              v-model="localItem.service_name"
              type="text"
              class="form-control"
              placeholder="Nama jasa/layanan"
            >
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Harga</label>
            <input
              v-model.number="localItem.service_price"
              type="number"
              class="form-control"
              min="0"
              placeholder="Harga jasa"
            >
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Jumlah</label>
            <input
              v-model.number="localItem.quantity"
              type="number"
              class="form-control"
              min="1"
            >
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Diskon (%)</label>
            <input
              v-model.number="localItem.discount"
              type="number"
              class="form-control"
              min="0"
              max="100"
            >
          </div>
        </template>
      </div>

      <!-- Tombol Tambah Item -->
      <div class="mt-4">
        <button 
          type="button" 
          class="btn btn-success w-100"
          @click="$emit('add-item')"
          :disabled="!canAddItem"
        >
          <i class="bi bi-plus-circle me-2"></i>
          Tambah ke Daftar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  products: Array,
  newItem: Object,
  availableProducts: Array,
  selectedProduct: Object,
  canAddItem: Boolean
});

const emit = defineEmits([
  'update:new-item',
  'add-item',
  'product-select',
  'validate-quantity'
]);

const localItem = ref({ ...props.newItem });

watch(localItem, (newValue) => {
  emit('update:new-item', newValue);
}, { deep: true });

watch(() => props.newItem, (newValue) => {
  localItem.value = { ...newValue };
}, { deep: true });
</script>