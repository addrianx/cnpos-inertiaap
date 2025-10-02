<template>
  <AppLayout>
    <h2>Buat Transaksi Penjualan (Mode Kasir)</h2>

  <form @submit.prevent="submit" class="mt-3">

    <!-- 📅 Tanggal Penjualan -->
    <div class="card mb-3 p-3">
      <div class="row">
        <div class="col-md-4 col-12 mb-2">
          <label class="form-label">Tanggal Penjualan</label>
          <input
            type="date"
            v-model="form.sale_date"
            class="form-control"
          />
        </div>
      </div>
    </div>

    <!-- 🛒 Daftar Item -->
    <div
      v-for="(item, index) in form.items"
      :key="index"
      class="card mb-3 p-3"
    >
      <div class="row align-items-end">
        <!-- Produk -->
        <div class="col-md-4 col-12 mb-2">
          <label class="form-label">Produk</label>
          <select v-model="item.product_id"
                  class="form-select"
                  :class="{ 'is-invalid': form.errors.items && form.errors.items[index] }">
            <option disabled value="">-- Pilih Produk --</option>
            <option v-for="p in props.products" :key="p.id" :value="p.id">
              {{ p.name }} (Stok: {{ p.stock ?? 0 }})
            </option>
          </select>
          <div v-if="form.errors.items && form.errors.items[index]" class="invalid-feedback d-block">
            {{ form.errors.items[index] }}
          </div>

        </div>

        <!-- Jumlah + Stok -->
        <div class="col-md-3 col-12 mb-2">
          <label class="form-label">Jumlah</label>
          <div class="input-group">
            <input
              v-model.number="item.quantity"
              type="number"
              class="form-control"
              min="1"
              :max="props.products.find(p => p.id === item.product_id)?.stock ?? 1"
            />
            <span class="input-group-text bg-light">
              Stok: {{ props.products.find(p => p.id === item.product_id)?.stock ?? 0 }}
            </span>
          </div>
        </div>

        <!-- Diskon -->
        <div class="col-md-2 col-6 mb-2">
          <label class="form-label">Diskon (%)</label>
          <input
            v-model.number="item.discount"
            type="number"
            class="form-control"
            min="0"
            max="100"
          />
        </div>

        <!-- Subtotal -->
        <div class="col-md-2 col-6 mb-2">
          <label class="form-label">Subtotal</label>
          <div class="form-control bg-light">
            Rp {{ itemsWithDetails[index].subtotal.toLocaleString() }}
          </div>
        </div>

        <!-- Hapus -->
        <div class="col-md-1 col-12 mb-2">
          <button
            type="button"
            class="btn btn-danger w-100"
            @click="removeItem(index)"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>

    <!-- Tombol tambah item -->
    <button type="button" class="btn btn-primary mb-3" @click="addItem">
      + Tambah Item
    </button>

    <!-- Ringkasan Transaksi -->
    <div class="card p-3 mb-3">
      <div class="mb-3">
        <label class="form-label">Total Transaksi</label>
        <div class="form-control bg-light">
          Rp {{ totalTransaction.toLocaleString() }}
        </div>
    </div>

    <div class="mb-3">
      <label for="paid">Dibayar</label>
      <input
        v-model="form.paid"
        type="number"
        step="0.01"
        class="form-control"
        :class="{ 'is-invalid': form.errors.paid }"
        id="paid"
        placeholder="Masukkan jumlah pembayaran"
      />

      <div v-if="form.errors.paid" class="invalid-feedback">
        {{ form.errors.paid }}
      </div>
    </div>

      <div class="mb-3">
        <label class="form-label">Kembalian</label>
        <div class="form-control bg-light">
          Rp {{ change.toLocaleString() }}
        </div>
      </div>
    </div>

    <!-- Tombol submit -->
    <button type="submit" class="btn btn-success" :disabled="form.processing">
      {{ form.processing ? "Menyimpan..." : "Simpan" }}
    </button>
    <Link href="/sales" class="btn btn-secondary ms-2">Batal</Link>
  </form>

  </AppLayout>
</template>

<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import { computed, onMounted, reactive  } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Swal from "sweetalert2";

const props = defineProps({ products: Array });
const errorsItem = reactive({}) 

// Form data
const form = useForm({
 sale_date: "",
  items: [{ product_id: "", quantity: 1, discount: 0 }],
  paid: 0,
  discount: 0,
});

// computed untuk format tampilan input
const paidFormatted = computed({
  get() {
    return form.paid ? form.paid.toLocaleString("id-ID") : ""
  },
  set(value) {
    // hapus semua selain angka
    const numericValue = value.replace(/\D/g, "")
    form.paid = numericValue ? parseInt(numericValue, 10) : 0
  },
})

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
  // ✅ Validasi produk belum dipilih
  let hasEmptyProduct = false;

  form.items.forEach((item, index) => {
    if (!item.product_id) {
      // buat object errors jika belum ada
      if (!form.errors.items) form.errors.items = {};
      form.errors.items[index] = "Pilih produk terlebih dahulu.";
      hasEmptyProduct = true;
    } else {
      // hapus error jika sudah diisi
      if (form.errors.items) form.errors.items[index] = null;
    }
  });

  if (hasEmptyProduct) {
    Swal.fire({
      icon: "warning",
      title: "Input Salah",
      text: "Periksa kembali item penjualan, ada produk yang belum dipilih.",
    });
    return; // stop submit tanpa merubah item atau kode lain
  }

  // ⚡ Sisa kode validasi harga, stok, subtotal, paid tetap sama
  for (let item of itemsWithDetails.value) {
    const product = props.products.find((p) => p.id === item.product_id);
    if (!product) continue;

    if (product.price <= 0) {
      Swal.fire({
        icon: "error",
        title: "Harga Jual Tidak Valid",
        text: `Produk ${product.name} tidak bisa dijual dengan harga Rp 0.`,
      });
      return;
    }

    if (product.price < product.cost) {
      return Swal.fire({
        title: "Harga Jual < Modal",
        text: `Harga jual Rp ${product.price.toLocaleString()} lebih rendah dari modal Rp ${product.cost.toLocaleString()}. Masukkan harga bayar manual.`,
        input: "number",
        inputAttributes: { min: product.cost },
        inputPlaceholder: "Minimal Rp " + product.cost.toLocaleString(),
        inputValidator: (value) => {
          if (!value || parseInt(value, 10) < product.cost) {
            return "Harga bayar harus minimal modal!";
          }
        },
      }).then((result) => {
        if (result.isConfirmed) {
          form.paid = parseInt(result.value, 10);
          submit(); // lanjut submit ulang
        }
      });
    }

    if (product.stock < item.quantity) {
      Swal.fire({
        icon: "warning",
        title: "Stok Tidak Cukup",
        text: `Stok untuk ${product.name} hanya ${product.stock}, tidak bisa jual ${item.quantity}.`,
      });
      return;
    }
  }

  // 🔹 Kirim ke server (sudah ada di kode lama)
  form.post("/sales", {
    data: {
      sale_date: form.sale_date,
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
      });
      form.reset();
    },
    onError: (errors) => {
      console.log("=== Debug Errors ===", errors);
      if (errors.paid) {
        form.errors.paid = errors.paid[0];
        Swal.fire({
          icon: "warning",
          title: "Input Salah",
          text: errors.paid[0],
        });
      } else if (errors.msg) {
        form.errors.paid = errors.msg;
        Swal.fire({
          icon: "warning",
          title: "Input Salah",
          text: errors.msg,
        });
      }
    },
  });
};




onMounted(() => {
  console.log("=== Debug Products with Stock ===");
  console.table(props.products); // biar rapi lihat tiap field
  // atau console.log(props.products) kalau array terlalu panjang
});
</script>
