<template>
  <AppLayout>
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Kasir Penjualan</h2>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary" @click="handleReset">
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
        <!-- Kolom Kiri: Input Item dan Daftar Item -->
        <div class="col-lg-8 py-sm-2 py-md-0">
          <!-- Form Input Item -->
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
                      v-model="itemInput.type" 
                      value="product"
                    >
                    <label class="btn btn-outline-primary" for="productType">Produk</label>
                    
                    <input 
                      type="radio" 
                      class="btn-check" 
                      name="itemType" 
                      id="serviceType" 
                      v-model="itemInput.type" 
                      value="service"
                    >
                    <label class="btn btn-outline-primary" for="serviceType">Jasa</label>
                  </div>
                </div>
              </div>

              <!-- Input untuk Produk -->
              <div v-if="itemInput.type === 'product'" class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Pilih Produk</label>
                  <select 
                    v-model="itemInput.product_id"
                    class="form-select"
                    @change="handleProductSelect"
                  >
                    <option value="">-- Pilih Produk --</option>
                    <option 
                      v-for="product in filteredProducts" 
                      :key="product.id" 
                      :value="product.id"
                    >
                      {{ product.name }} - Rp {{ formatPrice(product.price) }} 
                      (Stok: {{ product.stock }})
                    </option>
                  </select>
                </div>
                
                <div class="col-md-3">
                  <label class="form-label">Jumlah</label>
                  <input
                    v-model.number="itemInput.quantity"
                    type="number"
                    class="form-control"
                    min="1"
                    :max="getMaxQuantity"
                    @blur="validateQuantity"
                  >
                  <small class="text-muted" v-if="selectedProductData">
                    Maks: {{ getMaxQuantity }}
                  </small>
                </div>
                
                <div class="col-md-3">
                  <label class="form-label">Diskon (%)</label>
                  <input
                    v-model.number="itemInput.discount"
                    type="number"
                    class="form-control"
                    min="0"
                    max="100"
                  >
                </div>
              </div>

              <!-- Input untuk Jasa -->
              <div v-else class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Nama Jasa</label>
                  <input
                    v-model="itemInput.service_name"
                    type="text"
                    class="form-control"
                    placeholder="Nama jasa/layanan"
                  >
                </div>
                
                <div class="col-md-3">
                  <label class="form-label">Harga</label>
                  <input
                    v-model.number="itemInput.service_price"
                    type="number"
                    class="form-control"
                    min="0"
                    placeholder="Harga jasa"
                  >
                </div>
                
                <div class="col-md-3">
                  <label class="form-label">Jumlah</label>
                  <input
                    v-model.number="itemInput.quantity"
                    type="number"
                    class="form-control"
                    min="1"
                  >
                </div>
                
                <div class="col-md-2">
                  <label class="form-label">Diskon (%)</label>
                  <input
                    v-model.number="itemInput.discount"
                    type="number"
                    class="form-control"
                    min="0"
                    max="100"
                  >
                </div>
              </div>

              <!-- Preview Item -->
              <div v-if="showPreview" class="mt-3 p-3 bg-light rounded">
                <h6>Preview Item:</h6>
                <div class="d-flex justify-content-between">
                  <span>{{ getPreviewName }}</span>
                  <strong>Rp {{ formatPrice(getPreviewSubtotal) }}</strong>
                </div>
                <small class="text-muted">
                  {{ itemInput.quantity }} x Rp {{ formatPrice(getPreviewPrice) }} 
                  <span v-if="itemInput.discount > 0">- {{ itemInput.discount }}%</span>
                </small>
              </div>

              <!-- Tombol Tambah -->
              <div class="mt-4">
                <button 
                  type="button" 
                  class="btn btn-success w-100"
                  @click="addItemToCart"
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
              <h5 class="card-title mb-0">Keranjang</h5>
            </div>
            <div class="card-body p-0">
              <div v-if="cartItems.length === 0" class="text-center py-5 text-muted">
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
                    <tr v-for="(item, index) in cartItems" :key="item.id">
                      <td>
                        <div>
                          <strong>{{ item.name }}</strong>
                          <small class="d-block text-muted" v-if="item.type === 'service'">
                            Jasa
                          </small>
                          <small class="d-block text-muted" v-else>
                            Stok tersisa: {{ getRemainingStock(item.product_id) }}
                          </small>
                        </div>
                      </td>
                      <td>Rp {{ formatPrice(item.price) }}</td>
                      <td>{{ item.quantity }}</td>
                      <td>{{ item.discount }}%</td>
                      <td class="fw-bold">Rp {{ formatPrice(item.subtotal) }}</td>
                      <td>
                        <button 
                          type="button" 
                          class="btn btn-sm btn-outline-danger"
                          @click="removeCartItem(index)"
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

        <!-- Kolom Kanan: Ringkasan -->
        <div class="col-lg-4">
          <!-- Ringkasan Transaksi -->
          <div class="card mb-4">
            <div class="card-header bg-info text-white">
              <h5 class="card-title mb-0">Ringkasan Transaksi</h5>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label">Tanggal Penjualan</label>
                <input
                  type="date"
                  v-model="saleDate"
                  class="form-control"
                >
              </div>

              <div class="border-top pt-3">
                <div class="d-flex justify-content-between mb-2">
                  <span>Subtotal:</span>
                  <strong>Rp {{ formatPrice(summary.subtotal) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Total Diskon:</span>
                  <strong class="text-danger">- Rp {{ formatPrice(summary.totalDiscount) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3 fs-5 fw-bold">
                  <span>Total:</span>
                  <span class="text-primary">Rp {{ formatPrice(summary.total) }}</span>
                </div>
              </div>

              <div class="border-top pt-3">
                <div class="mb-3">
                  <label class="form-label fw-bold">Dibayar</label>
                  <input
                    v-model="paidInput"
                    type="text"
                    class="form-control form-control-lg"
                    placeholder="0"
                    @input="handlePaidInput"
                    :class="{ 'is-invalid': paidAmount < summary.total }"
                  >
                  <div v-if="paidAmount < summary.total" class="invalid-feedback">
                    Jumlah pembayaran kurang
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-bold">Kembalian</label>
                  <div 
                    class="form-control form-control-lg fw-bold text-center"
                    :class="changeAmount >= 0 ? 'text-success bg-light-success' : 'text-danger bg-light-danger'"
                  >
                    {{ changeAmount >= 0 ? 'Rp ' + formatPrice(changeAmount) : 'Kurang: Rp ' + formatPrice(Math.abs(changeAmount)) }}
                  </div>
                </div>
              </div>

              <!-- Form untuk submit -->
              <form @submit.prevent="processSale">
                <button 
                  type="submit" 
                  class="btn btn-success btn-lg w-100 mt-3"
                  :disabled="!canProcessSale"
                >
                  <i class="bi bi-check-circle me-2"></i>
                  {{ isProcessing ? 'Menyimpan...' : 'Simpan Transaksi' }}
                </button>
              </form>
            </div>
          </div>

          <!-- Info Cepat -->
          <div class="card">
            <div class="card-body">
              <h6 class="card-title">Info Cepat</h6>
              <ul class="list-unstyled small">
                <li class="mb-1">
                  <i class="bi bi-info-circle text-primary me-1"></i>
                  Total Item: <strong>{{ cartItems.length }}</strong>
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
import { ref, computed, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Swal from "sweetalert2";

const props = defineProps({ 
  products: {
    type: Array,
    default: () => []
  }
});

// State Management
const saleDate = ref(new Date().toISOString().split('T')[0]);
const isProcessing = ref(false);

// Input Item State
const itemInput = ref({
  type: 'product',
  product_id: '',
  service_name: '',
  service_price: 0,
  quantity: 1,
  discount: 0
});

// Cart State
const cartItems = ref([]);

// Payment State
const paidInput = ref('0');
const paidAmount = ref(0);

// Computed Properties
const filteredProducts = computed(() => {
  return props.products.filter(product => product.stock > 0);
});

const selectedProductData = computed(() => {
  if (!itemInput.value.product_id) return null;
  return props.products.find(p => p.id == itemInput.value.product_id);
});

const getMaxQuantity = computed(() => {
  if (itemInput.value.type !== 'product' || !selectedProductData.value) return 1;
  
  const product = selectedProductData.value;
  const inCart = cartItems.value
    .filter(item => item.product_id === itemInput.value.product_id)
    .reduce((sum, item) => sum + item.quantity, 0);
  
  return product.stock - inCart;
});

const showPreview = computed(() => {
  if (itemInput.value.type === 'product') {
    return itemInput.value.product_id && itemInput.value.quantity > 0;
  } else {
    return itemInput.value.service_name && itemInput.value.service_price > 0 && itemInput.value.quantity > 0;
  }
});

const getPreviewPrice = computed(() => {
  if (itemInput.value.type === 'product' && selectedProductData.value) {
    return selectedProductData.value.price;
  } else if (itemInput.value.type === 'service') {
    return itemInput.value.service_price;
  }
  return 0;
});

const getPreviewName = computed(() => {
  if (itemInput.value.type === 'product' && selectedProductData.value) {
    return selectedProductData.value.name;
  } else if (itemInput.value.type === 'service') {
    return itemInput.value.service_name;
  }
  return '';
});

const getPreviewSubtotal = computed(() => {
  const price = getPreviewPrice.value;
  const quantity = itemInput.value.quantity;
  const discount = itemInput.value.discount;
  const discountAmount = price * quantity * (discount / 100);
  return (price * quantity) - discountAmount;
});

const canAddItem = computed(() => {
  if (itemInput.value.type === 'product') {
    return itemInput.value.product_id && 
           itemInput.value.quantity > 0 && 
           itemInput.value.quantity <= getMaxQuantity.value;
  } else {
    return itemInput.value.service_name && 
           itemInput.value.service_price > 0 && 
           itemInput.value.quantity > 0;
  }
});

// Summary Calculations
const summary = computed(() => {
  const subtotal = cartItems.value.reduce((sum, item) => sum + item.subtotal, 0);
  const totalDiscount = cartItems.value.reduce((sum, item) => {
    return sum + (item.price * item.quantity * (item.discount / 100));
  }, 0);
  const total = Math.max(0, subtotal - totalDiscount);

  return {
    subtotal,
    totalDiscount,
    total
  };
});

const changeAmount = computed(() => {
  return Math.max(0, paidAmount.value - summary.value.total);
});

const canProcessSale = computed(() => {
  return cartItems.value.length > 0 && 
         paidAmount.value >= summary.value.total && 
         !isProcessing.value;
});

const productCount = computed(() => {
  return cartItems.value.filter(item => item.type === 'product').length;
});

const serviceCount = computed(() => {
  return cartItems.value.filter(item => item.type === 'service').length;
});

// Methods
const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price);
};

const handleProductSelect = () => {
  if (selectedProductData.value) {
    itemInput.value.quantity = 1;
    itemInput.value.discount = 0;
  }
};

const validateQuantity = () => {
  if (itemInput.value.type === 'product' && selectedProductData.value) {
    if (itemInput.value.quantity > getMaxQuantity.value) {
      itemInput.value.quantity = Math.max(1, getMaxQuantity.value);
      if (getMaxQuantity.value > 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Stok Tidak Cukup',
          text: `Stok tersisa: ${getMaxQuantity.value}`,
          timer: 2000
        });
      }
    }
  }
};

const addItemToCart = () => {
  if (!canAddItem.value) return;

  const newItem = {
    id: Date.now() + Math.random(),
    type: itemInput.value.type,
    product_id: itemInput.value.type === 'product' ? itemInput.value.product_id : null,
    name: itemInput.value.type === 'product' ? selectedProductData.value.name : itemInput.value.service_name,
    price: itemInput.value.type === 'product' ? selectedProductData.value.price : itemInput.value.service_price,
    quantity: itemInput.value.quantity,
    discount: itemInput.value.discount,
    subtotal: getPreviewSubtotal.value
  };

  cartItems.value.push(newItem);
  resetItemInput();
  updatePaidAmount();
};

const removeCartItem = (index) => {
  cartItems.value.splice(index, 1);
  updatePaidAmount();
};

const resetItemInput = () => {
  itemInput.value = {
    type: 'product',
    product_id: '',
    service_name: '',
    service_price: 0,
    quantity: 1,
    discount: 0
  };
};

const getRemainingStock = (productId) => {
  const product = props.products.find(p => p.id == productId);
  if (!product) return 0;
  
  const inCart = cartItems.value
    .filter(item => item.product_id == productId)
    .reduce((sum, item) => sum + item.quantity, 0);
  
  return product.stock - inCart;
};

const handlePaidInput = (event) => {
  let value = event.target.value.replace(/\D/g, "");
  
  if (value) {
    const numericValue = parseInt(value, 10);
    paidInput.value = formatPrice(numericValue);
    paidAmount.value = numericValue;
  } else {
    paidInput.value = "0";
    paidAmount.value = 0;
  }
};

const updatePaidAmount = () => {
  if (paidAmount.value < summary.value.total) {
    paidAmount.value = summary.value.total;
    paidInput.value = formatPrice(summary.value.total);
  }
};

const handleReset = () => {
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
      cartItems.value = [];
      resetItemInput();
      saleDate.value = new Date().toISOString().split('T')[0];
      paidAmount.value = 0;
      paidInput.value = '0';
      
      Swal.fire({
        icon: 'success',
        title: 'Form telah direset',
        timer: 1500
      });
    }
  });
};

const processSale = async () => {
  if (!canProcessSale.value) return;

  try {
    const result = await Swal.fire({
      title: 'Simpan Transaksi?',
      html: `
        <div class="text-start">
          <p><strong>Total: Rp ${formatPrice(summary.value.total)}</strong></p>
          <p>Dibayar: Rp ${formatPrice(paidAmount.value)}</p>
          <p>Kembalian: Rp ${formatPrice(changeAmount.value)}</p>
          <p>Jumlah Item: ${cartItems.value.length}</p>
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#198754',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Simpan!',
      cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
      isProcessing.value = true;

      // Prepare sale data
      const saleData = {
        sale_date: saleDate.value,
        items: cartItems.value.map(item => ({
          product_id: item.type === 'product' ? item.product_id : null,
          service_name: item.type === 'service' ? item.name : null,
          service_description: item.type === 'service' ? `Jasa: ${item.name}` : null,
          item_type: item.type,
          quantity: item.quantity,
          price: item.price,
          discount: item.discount,
          subtotal: item.subtotal,
        })),
        subtotal: summary.value.subtotal,
        discount: summary.value.totalDiscount,
        total: summary.value.total,
        paid: paidAmount.value,
        change: changeAmount.value,
      };

      // Use Inertia form to submit
      const form = useForm(saleData);
      
      form.post('/sales', {
        onSuccess: () => {
          // Success handled by Inertia redirect
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Transaksi berhasil disimpan',
            timer: 2000
          });
        },
        onError: (errors) => {
          let errorMessage = 'Terjadi kesalahan saat menyimpan transaksi.';
          
          if (errors.paid) {
            errorMessage = errors.paid;
          } else if (errors.items) {
            errorMessage = 'Ada masalah dengan item penjualan';
          }
          
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: errorMessage
          });
        },
        onFinish: () => {
          isProcessing.value = false;
        }
      });
    }
  } catch (error) {
    console.error('Error processing sale:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Terjadi kesalahan saat memproses transaksi'
    });
    isProcessing.value = false;
  }
};

// Auto-update paid amount when total changes
watch(() => summary.value.total, (newTotal) => {
  if (paidAmount.value < newTotal) {
    paidAmount.value = newTotal;
    paidInput.value = formatPrice(newTotal);
  }
});
</script>