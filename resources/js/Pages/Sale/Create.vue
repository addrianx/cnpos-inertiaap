<template>
  <AppLayout>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Kasir Penjualan</h2>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary" @click="resetForm">
                <i class="bi bi-arrow-clockwise me-1"></i> Reset
              </button>
              <Link href="/sales" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Form Input Item -->
        <div class="col-lg-8">
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
                      v-model="newItem.type" 
                      value="product"
                      checked
                    >
                    <label class="btn btn-outline-primary" for="productType">Produk</label>
                    
                    <input 
                      type="radio" 
                      class="btn-check" 
                      name="itemType" 
                      id="serviceType" 
                      v-model="newItem.type" 
                      value="service"
                    >
                    <label class="btn btn-outline-primary" for="serviceType">Jasa</label>
                  </div>
                </div>
              </div>

              <!-- Form Input Berdasarkan Tipe -->
              <div class="row g-3">
                <!-- Input Produk -->
                <template v-if="newItem.type === 'product'">
                  <div class="col-md-6">
                    <label class="form-label">Pilih Produk</label>
                    <select 
                      v-model="newItem.product_id"
                      class="form-select"
                      @change="onProductSelect"
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
                      v-model.number="newItem.quantity"
                      type="number"
                      class="form-control"
                      min="1"
                      :max="selectedProduct?.stock || 1"
                      @change="validateQuantity"
                    >
                  </div>
                  
                  <div class="col-md-3">
                    <label class="form-label">Diskon (%)</label>
                    <input
                      v-model.number="newItem.discount"
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
                      v-model="newItem.service_name"
                      type="text"
                      class="form-control"
                      placeholder="Nama jasa/layanan"
                    >
                  </div>
                  
                  <div class="col-md-3">
                    <label class="form-label">Harga</label>
                    <input
                      v-model.number="newItem.service_price"
                      type="number"
                      class="form-control"
                      min="0"
                      placeholder="Harga jasa"
                    >
                  </div>
                  
                  <div class="col-md-3">
                    <label class="form-label">Jumlah</label>
                    <input
                      v-model.number="newItem.quantity"
                      type="number"
                      class="form-control"
                      min="1"
                    >
                  </div>
                  
                  <div class="col-md-2">
                    <label class="form-label">Diskon (%)</label>
                    <input
                      v-model.number="newItem.discount"
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
                  @click="addItem"
                  :disabled="!canAddItem"
                >
                  <i class="bi bi-plus-circle me-2"></i>
                  Tambah ke Daftar
                </button>
              </div>
            </div>
          </div>

          <!-- Daftar Item -->
          <div class="card">
            <div class="card-header bg-secondary text-white">
              <h5 class="card-title mb-0">Daftar Item Penjualan</h5>
            </div>
            <div class="card-body p-0">
              <div v-if="items.length === 0" class="text-center py-5 text-muted">
                <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                Belum ada item dalam penjualan
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
                          @click="removeItem(index)"
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
        </div>

        <!-- Ringkasan & Pembayaran -->
        <div class="col-lg-4">
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
                    v-model="paidFormatted"
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
                @click="submitSale"
                :disabled="!canSubmit"
              >
                <i class="bi bi-check-circle me-2"></i>
                {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
              </button>
            </div>
          </div>

          <!-- Info Cepat -->
          <div class="card">
            <div class="card-body">
              <h6 class="card-title">Info Cepat</h6>
              <ul class="list-unstyled small">
                <li class="mb-1">
                  <i class="bi bi-info-circle text-primary me-1"></i>
                  Total Item: <strong>{{ items.length }}</strong>
                </li>
                <li class="mb-1">
                  <i class="bi bi-box text-success me-1"></i>
                  Produk: <strong>{{ productCount }}</strong>
                </li>
                <li class="mb-1">
                  <i class="bi bi-tools text-warning me-1"></i>
                  Jasa: <strong>{{ serviceCount }}</strong>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Swal from "sweetalert2";

const props = defineProps({ 
  products: Array 
});

// Form utama
const form = useForm({
  sale_date: new Date().toISOString().split('T')[0],
  items: [],
  subtotal: 0,
  discount: 0,
  total: 0,
  paid: 0,
  change: 0,
});

// Item baru yang akan ditambahkan
const newItem = ref({
  type: 'product',
  product_id: '',
  service_name: '',
  service_price: 0,
  quantity: 1,
  discount: 0
});

// Daftar item yang sudah ditambahkan
const items = ref([]);

// Format input dibayar
const paidFormatted = ref('0');

// Computed properties
const availableProducts = computed(() => 
  props.products.filter(p => p.stock > 0)
);

const selectedProduct = computed(() => 
  props.products.find(p => p.id === newItem.value.product_id)
);

const canAddItem = computed(() => {
  if (newItem.value.type === 'product') {
    return newItem.value.product_id && 
           newItem.value.quantity > 0 && 
           newItem.value.quantity <= (selectedProduct.value?.stock || 0);
  } else {
    return newItem.value.service_name && 
           newItem.value.service_price > 0 && 
           newItem.value.quantity > 0;
  }
});

const subtotal = computed(() => 
  items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
);

const totalDiscount = computed(() => 
  items.value.reduce((sum, item) => {
    const discountAmount = item.price * item.quantity * (item.discount / 100);
    return sum + discountAmount;
  }, 0)
);

const totalTransaction = computed(() => subtotal.value - totalDiscount.value);

const paid = computed(() => form.paid);

const change = computed(() => paid.value - totalTransaction.value);

const canSubmit = computed(() => 
  items.value.length > 0 && 
  paid.value >= totalTransaction.value && 
  !form.processing
);

const productCount = computed(() => 
  items.value.filter(item => item.type === 'product').length
);

const serviceCount = computed(() => 
  items.value.filter(item => item.type === 'service').length
);

// Methods
const onProductSelect = () => {
  if (selectedProduct.value) {
    newItem.value.quantity = 1;
    newItem.value.discount = 0;
  }
};

const validateQuantity = () => {
  if (newItem.value.type === 'product' && selectedProduct.value) {
    const maxStock = selectedProduct.value.stock;
    const currentInCart = items.value
      .filter(item => item.product_id === newItem.value.product_id)
      .reduce((sum, item) => sum + item.quantity, 0);
    
    const availableStock = maxStock - currentInCart;
    
    if (newItem.value.quantity > availableStock) {
      newItem.value.quantity = availableStock;
      Swal.fire({
        icon: 'warning',
        title: 'Stok Tidak Cukup',
        text: `Stok tersisa: ${availableStock}`,
        timer: 2000
      });
    }
  }
};

const addItem = () => {
  if (!canAddItem.value) return;

  let itemData = { ...newItem.value };
  
  // Set data berdasarkan tipe
  if (itemData.type === 'product') {
    const product = selectedProduct.value;
    itemData.price = product.price;
    itemData.item_name = product.name;
    itemData.service_name = null;
    itemData.service_price = null;
  } else {
    itemData.price = itemData.service_price;
    itemData.item_name = itemData.service_name;
    itemData.product_id = null;
  }

  // Hitung subtotal
  const discountAmount = itemData.price * itemData.quantity * (itemData.discount / 100);
  itemData.subtotal = (itemData.price * itemData.quantity) - discountAmount;

  items.value.push(itemData);
  
  // Reset form input
  resetNewItem();
  
  // Update ringkasan
  updateSummary();
};

const removeItem = (index) => {
  items.value.splice(index, 1);
  updateSummary();
};

const selectedProductStock = (productId) => {
  const product = props.products.find(p => p.id === productId);
  const inCart = items.value
    .filter(item => item.product_id === productId)
    .reduce((sum, item) => sum + item.quantity, 0);
  
  return (product?.stock || 0) - inCart;
};

const resetNewItem = () => {
  newItem.value = {
    type: 'product',
    product_id: '',
    service_name: '',
    service_price: 0,
    quantity: 1,
    discount: 0
  };
};

const updateSummary = () => {
  form.subtotal = subtotal.value;
  form.discount = totalDiscount.value;
  form.total = totalTransaction.value;
  
  // Auto-set paid amount jika belum diisi atau kurang
  if (!form.paid || form.paid < totalTransaction.value) {
    form.paid = totalTransaction.value;
    paidFormatted.value = totalTransaction.value.toLocaleString('id-ID');
  }
};

const handlePaidInput = (event) => {
  let value = event.target.value.replace(/\D/g, "");
  
  if (value) {
    const numericValue = parseInt(value, 10);
    paidFormatted.value = numericValue.toLocaleString("id-ID");
    form.paid = numericValue;
  } else {
    paidFormatted.value = "0";
    form.paid = 0;
  }
};

const resetForm = () => {
  Swal.fire({
    title: 'Reset Form?',
    text: 'Semua data penjualan akan dihapus',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Reset!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      items.value = [];
      resetNewItem();
      form.reset();
      form.sale_date = new Date().toISOString().split('T')[0];
      form.paid = 0;
      paidFormatted.value = '0';
      
      Swal.fire({
        icon: 'success',
        title: 'Form telah direset',
        timer: 1500
      });
    }
  });
};

const submitSale = () => {
  if (!canSubmit.value) return;

  // Siapkan data untuk dikirim
  const saleData = {
    sale_date: form.sale_date,
    items: items.value.map(item => ({
      product_id: item.type === 'product' ? item.product_id : null,
      service_name: item.type === 'service' ? item.service_name : null,
      service_description: item.type === 'service' ? `Jasa: ${item.service_name}` : null,
      service_price: item.type === 'service' ? item.service_price : null,
      item_type: item.type,
      quantity: item.quantity,
      price: item.price,
      discount: item.discount,
      subtotal: item.subtotal,
      item_name: item.item_name
    })),
    subtotal: form.subtotal,
    discount: form.discount,
    total: form.total,
    paid: form.paid,
    change: change.value,
  };

  // Tampilkan konfirmasi
  Swal.fire({
    title: 'Simpan Transaksi?',
    html: `
      <div class="text-start">
        <p><strong>Total: Rp ${form.total.toLocaleString()}</strong></p>
        <p>Dibayar: Rp ${form.paid.toLocaleString()}</p>
        <p>Kembalian: Rp ${change.value.toLocaleString()}</p>
        <p>Jumlah Item: ${items.value.length}</p>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Simpan!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      form.processing = true;
      
      // Kirim request
      form.transform(() => saleData)
        .post('/sales', {
          onSuccess: () => {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: 'Transaksi berhasil disimpan',
              timer: 2000
            });
            
            // Redirect ke halaman sales
            setTimeout(() => {
              router.visit('/sales');
            }, 2000);
          },
          onError: (errors) => {
            let errorMessage = 'Terjadi kesalahan saat menyimpan transaksi.';
            
            if (errors.items) {
              errorMessage = 'Ada masalah dengan item penjualan: ' + Object.values(errors.items).join(', ');
            } else if (errors.paid) {
              errorMessage = errors.paid;
            }
            
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: errorMessage
            });
          },
          onFinish: () => {
            form.processing = false;
          }
        });
    }
  });
};

// Watch untuk update otomatis
watch(totalTransaction, (newTotal) => {
  if (form.paid < newTotal) {
    form.paid = newTotal;
    paidFormatted.value = newTotal.toLocaleString('id-ID');
  }
});
</script>

<style scoped>
.bg-light-success {
  background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-light-danger {
  background-color: rgba(220, 53, 69, 0.1) !important;
}

.table th {
  border-top: none;
  font-weight: 600;
}

.card-header {
  border-bottom: none;
}

.btn-check:checked + .btn {
  border-color: #0d6efd;
  background-color: #0d6efd;
}

.form-control-lg {
  font-size: 1.1rem;
  font-weight: 600;
}
</style>