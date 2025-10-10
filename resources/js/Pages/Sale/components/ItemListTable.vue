<template>
  <div class="card">
    <div class="card-header bg-secondary text-white">
      <h5 class="card-title mb-0">Keranjang</h5>
    </div>
    <div class="card-body p-0">
      <div v-if="items.length === 0" class="text-center py-5 text-muted">
        <i class="bi bi-cart-x display-4 d-block mb-2"></i>
        Belum ada item dalam keranjang
      </div>
      
      <div v-else class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th width="40%">Item</th>
              <th width="15%">Harga</th>
              <th width="10%">Jumlah</th>
              <th width="10%">Diskon</th>
              <th width="15%">Subtotal</th>
              <th width="10%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="index">
              <td>
                <div>
                  <strong>{{ item.item_name }}</strong>
                  <small class="d-block text-muted" v-if="item.type === 'service'">
                    {{ item.service_description || 'Jasa' }}
                  </small>
                  <small class="d-block text-muted" v-else>
                    Stok tersisa: {{ selectedProductStock(item.product_id) }}
                  </small>
                </div>
              </td>
              <td>Rp {{ item.price.toLocaleString() }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ item.discount }}%</td>
              <td class="fw-bold">Rp {{ item.subtotal.toLocaleString() }}</td>
              <td>
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-danger"
                  @click="$emit('remove-item', index)"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: Array,
  products: Array
});

defineEmits(['remove-item']);

const selectedProductStock = (productId) => {
  const product = props.products.find(p => p.id === productId);
  const inCart = props.items
    .filter(item => item.product_id === productId)
    .reduce((sum, item) => sum + item.quantity, 0);
  
  return (product?.stock || 0) - inCart;
};
</script>