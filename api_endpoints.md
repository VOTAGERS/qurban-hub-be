# 📑 API Endpoint Documentation

Dokumentasi endpoint API untuk Qurban Hub Backend. Semua endpoint menggunakan prefix `/api`.

## 🛒 Integrasi WooCommerce (Woo-Specific)
Endpoint ini digunakan untuk sinkronisasi data dari WooCommerce, baik melalui Webhook otomatis maupun hit manual.

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/webhook/woocommerce` | Endpoint utama untuk menerima Webhook dari WooCommerce. |
| `GET` | `/products-woo` | Mengambil daftar semua produk WooCommerce yang tersimpan. |
| `POST` | `/products-woo` | Menambah/Update produk secara manual (menggunakan `ProductEventHandler`). |
| `GET` | `/users-woo` | Mengambil daftar semua customer WooCommerce. |
| `POST` | `/users-woo` | Menambah/Update customer secara manual (menggunakan `CustomerEventHandler`). |
| `GET` | `/orders-woo` | Mengambil daftar semua order beserta item-nya. |
| `POST` | `/orders-woo` | Menambah/Update order secara manual (menggunakan `OrderEventHandler`). |

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
