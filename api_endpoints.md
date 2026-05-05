# 📑 API Endpoint Documentation

Dokumentasi endpoint API untuk Qurban Hub Backend. Semua endpoint menggunakan prefix `/api`.

## 🛒 Integrasi WooCommerce (Product Sync)
Endpoint ini digunakan untuk sinkronisasi data produk dari WooCommerce.

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/webhook/woocommerce` | Endpoint utama untuk menerima Webhook Produk dari WooCommerce. |
| `GET` | `/products-woo` | Mengambil daftar semua produk WooCommerce yang tersimpan. |
| `POST` | `/products-woo` | Menambah/Update produk secara manual (menggunakan `ProductEventHandler`). |

## 💳 Pembayaran & Checkout (Stripe)
Endpoint untuk memproses pembayaran menggunakan Stripe Checkout.

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/checkout` | **Seamless Checkout**: Simpan order + Redirect ke Stripe. |
| `POST` | `/stripe/webhook` | Menerima notifikasi status pembayaran dari Stripe. |
| `POST` | `/create-checkout-session` | Membuat sesi Stripe untuk order yang sudah ada. |
| `GET` | `/order-details/{orderCode}` | Mendapatkan detail lengkap order berdasarkan kode order. |

---

## 📦 Internal Resources
Endpoint standar untuk manajemen data internal aplikasi.

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/packages` | Mendapatkan daftar paket qurban. |
| `POST` | `/packages` | Membuat paket qurban baru. |
| `GET` | `/users` | Mendapatkan daftar user internal. |
| `GET` | `/orders` | Mendapatkan daftar order internal. |
| `GET` | `/payments` | Mendapatkan daftar transaksi pembayaran. |
| `GET` | `/certificates` | Mendapatkan daftar sertifikat qurban. |
| `GET` | `/test` | Cek koneksi backend (Health Check). |

---

## 🛠 Contoh Payload (POST)

### 1. Order (Woo Format)
`POST /api/orders-woo`
```json
{
  "id": 6001,
  "status": "processing",
  "total": "2500000.00",
  "billing": { "email": "pembeli@example.com" },
  "line_items": [
    {
      "name": "Kambing Tipe A",
      "quantity": 1,
      "price": "2500000.00"
    }
  ]
}
```

### 2. Product (Woo Format)
`POST /api/products-woo`
```json
{
  "id": 888,
  "name": "Sapi Madura",
  "price": "18000000.00",
  "status": "publish"
}
```

---

> [!TIP]
> Semua endpoint yang menggunakan prefix `-woo` sudah terhubung dengan **Services** (`EventHandler`) sehingga logikanya seragam dengan Webhook asli.

