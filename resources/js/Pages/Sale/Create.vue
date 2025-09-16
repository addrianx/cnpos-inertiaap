<template>
  <AppLayout>
    <h2>Buat Transaksi Penjualan (Mode Kasir)</h2>

    <form @submit.prevent="submit" class="mt-3">
      <!-- Daftar Item -->
      <div v-for="(item, index) in form.items" :key="index" class="card mb-3 p-3">
        <div class="row align-items-end">
          <!-- Produk -->
          <div class="col-md-4 mb-2">
            <label class="form-label">Produk</label>
            <select v-model="item.product_id" class="form-select">
              <option disabled value="">-- Pilih Produk --</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <!-- Jumlah -->
          <div class="col-md-2 mb-2">
            <label class="form-label">Jumlah</label>
            <input v-model.number="item.quantity" type="number" class="form-control" min="1" />
          </div>

          <!-- Diskon -->
          <div class="col-md-2 mb-2">
            <label class="form-label">Diskon (%)</label>
            <input v-model.number="item.discount" type="number" class="form-control" min="0" max="100" />
          </div>

          <!-- Subtotal -->
          <div class="col-md-2 mb-2">
            <label class="form-label">Subtotal</label>
            <div class="form-control bg-light">
              Rp {{ itemsWithDetails[index].subtotal.toLocaleString() }}
            </div>
          </div>

          <!-- Hapus Item -->
          <div class="col-md-2 mb-2">
            <button type="button" class="btn btn-danger w-100" @click="removeItem(index)">Hapus</button>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-primary mb-3" @click="addItem">+ Tambah Item</button>

      <!-- Total -->
      <div class="mb-3">
        <label class="form-label">Total Transaksi</label>
        <div class="form-control bg-light">
          Rp {{ totalTransaction.toLocaleString() }}
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Dibayar</label>
        <input v-model.number="form.paid" type="number" class="form-control" min="0" />
      </div>

      <div class="mb-3">
        <label class="form-label">Kembalian</label>
        <div class="form-control bg-light">
          Rp {{ change.toLocaleString() }}
        </div>
      </div>

      <button type="submit" class="btn btn-success" :disabled="form.processing">
        {{ form.processing ? "Menyimpan..." : "Simpan" }}
      </button>
      <Link href="/sales" class="btn btn-secondary ms-2">Batal</Link>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Swal from "sweetalert2";

const props = defineProps({ products: Array });

// Form data
const form = useForm({
  items: [{ product_id: "", quantity: 1, discount: 0 }],
  paid: 0,
});

// Tambah/hapus item
const addItem = () => form.items.push({ product_id: "", quantity: 1, discount: 0 });
const removeItem = (index) => form.items.splice(index, 1);

// Items dengan detail harga, diskon, subtotal
const itemsWithDetails = computed(() =>
  form.items.map((item) => {
    const product = props.products.find((p) => p.id === item.product_id);
    const price = product ? product.price : 0;
    const discountAmount = price * item.quantity * (item.discount || 0) / 100;
    const subtotal = price * item.quantity - discountAmount;

    return { ...item, price, discountAmount, subtotal };
  })
);

// Hitung subtotal, diskon total, dan total akhir
const subtotal = computed(() => itemsWithDetails.value.reduce((sum, i) => sum + i.price * i.quantity, 0));
const totalDiscount = computed(() => itemsWithDetails.value.reduce((sum, i) => sum + i.discountAmount, 0));
const totalTransaction = computed(() => subtotal.value - totalDiscount.value);

// Kembalian
const change = computed(() => Math.max(form.paid - totalTransaction.value, 0));

// Submit form
const submit = () => {
  form.post("/sales", {
    data: {
      items: itemsWithDetails.value,
      subtotal: subtotal.value,
      discount: totalDiscount.value,
      total: totalTransaction.value,
      paid: form.paid,
      change: change.value,
    },
    onSuccess: () => {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Transaksi berhasil disimpan.",
        confirmButtonColor: "#3085d6",
      });
      form.reset();
    },
    onError: (errors) => {
      console.error(errors);
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Terjadi kesalahan, periksa input Anda.",
        confirmButtonColor: "#d33",
      });
    },
  });
};
</script>
