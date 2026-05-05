# Dokumentasi Integrasi Stripe - QurbanHub

Dokumentasi ini menjelaskan implementasi sistem pembayaran Stripe pada backend Laravel QurbanHub, mencakup alur Seamless Checkout, Webhook, dan konfigurasi sistem.

## 1. Alur Pembayaran (Seamless Checkout)

Sistem menggunakan metode **Stripe Checkout Session (Redirect)** yang mendukung user terautentikasi (Laravel Cashier) maupun tamu/guest (Stripe SDK).

### A. Endpoint Checkout Utama
*   **URL**: `POST /api/checkout`
*   **Akses**: Publik (Guest & Terautentikasi)
*   **Fungsi**: 
    1. Menyimpan data order baru ke database.
    2. Membuat sesi pembayaran Stripe secara otomatis.
    3. Mengembalikan `checkout_url` untuk langsung di-redirect oleh Frontend.

### B. Endpoint Sesi Manual
*   **URL**: `POST /api/create-checkout-session`
*   **Fungsi**: Digunakan untuk membuat ulang sesi pembayaran bagi order yang sudah ada.

---

## 2. Sistem Webhook

Webhook digunakan untuk sinkronisasi otomatis antara Stripe dan Database lokal.

*   **Endpoint**: `POST /api/stripe/webhook`
*   **Event yang Ditangani**:
    *   `checkout.session.completed`: Dipicu saat user berhasil membayar di halaman redirect Stripe.
    *   `payment_intent.succeeded`: Dipicu jika menggunakan Stripe Elements (Embedded).

### Logika Proses Webhook:
1.  **Audit Log**: Payload JSON mentah dari Stripe disimpan ke tabel `apps_log` untuk kebutuhan debugging.
2.  **Update Database**: Status order diubah menjadi `paid` dan status qurban menjadi `scheduled`.
3.  **Payment Record**: Data transaksi dicatat ke tabel `payments` lengkap dengan `id_stripe`.

---

## 3. Konfigurasi Environment (.env)

Pastikan variabel berikut terdefinisi dengan benar:

```env
# Stripe Keys
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Cashier Settings
CASHIER_CURRENCY=sgd

# Frontend Integration
FRONTEND_URL=http://localhost:5173
```

> [!NOTE]
> `FRONTEND_URL` digunakan untuk menentukan alamat redirect setelah sukses/batal bayar. Nilai ini didaftarkan secara dinamis melalui `config/app.frontend_url`.

---

## 4. Perubahan Database

Untuk mendukung fleksibilitas status, kolom-kolom berikut telah diubah dari **ENUM** menjadi **STRING**:
*   Tabel `orders`: `payment_status`, `qurban_status`.
*   Tabel `payments`: `payment_status` dan penambahan kolom `id_stripe` (string) untuk menyimpan ID transaksi Stripe.

---

## 5. Prosedur Pengembangan (Local Testing)

Untuk mencoba webhook di localhost, gunakan **Cloudflare Tunnel**:
```bash
cloudflared tunnel --url http://localhost
```
Daftarkan URL yang muncul ke Dashboard Stripe dengan path:
`https://[cloudflare-url]/qurban-hub-be/api/stripe/webhook`
