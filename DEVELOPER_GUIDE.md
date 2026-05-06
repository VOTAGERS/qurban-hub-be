# 📖 Developer Setup & Testing Guide - QurbanHub Backend

Panduan ini ditujukan bagi tim pengembang untuk melakukan setup awal, pengujian autentikasi OTP, dan pengelolaan sertifikat Qurban.

---

## 🚀 1. Setup Awal (Database & Data)

Sebelum memulai, pastikan database sudah terkonfigurasi di file `.env`. Jalankan perintah berikut untuk menyiapkan data role dan user admin default.

### Step-by-step:
1.  **Migrate Database**:
    ```bash
    php artisan migrate
    ```
2.  **Jalankan Seeder**:
    Perintah ini akan membuat role (Admin, Customer, Finance) dan user admin default.
    ```bash
    php artisan db:seed --class=RoleAndAdminSeeder
    ```
    *   **User Admin Default**: `admin@localhost`
    *   **Password**: `admin123`

---

## 🔐 2. Pengujian Autentikasi (Email OTP)

Sistem menggunakan login tanpa password (passwordless) menggunakan kode OTP yang dikirim ke email.

### Cara Pengujian (Local):
1.  Pastikan `MAIL_MAILER=log` di file `.env` agar OTP tidak benar-benar mengirim email asli, melainkan dicatat di log.
2.  Gunakan aplikasi **Postman** atau **Insomnia** untuk memanggil API:
    *   **Step A (Minta OTP)**:
        *   `POST /api/auth/send-otp`
        *   Payload: `{ "email": "admin@localhost" }`
    *   **Step B (Cek Log)**:
        *   Buka file `storage/logs/laravel.log`.
        *   Cari baris "Your OTP code is: XXXXXX".
    *   **Step C (Verifikasi OTP)**:
        *   `POST /api/auth/verify-otp`
        *   Payload: `{ "email": "admin@localhost", "otp_code": "XXXXXX" }`
3.  Jika sukses, Anda akan menerima **Sanctum Token** untuk mengakses API terproteksi.

### Mekanisme Internal Auth:
*   **Generate OTP**: Kode 6 digit angka acak dihasilkan setiap kali request masuk.
*   **Masa Berlaku**: OTP hanya berlaku selama **15 menit** sejak dibuat.
*   **Status Code**:
    *   `pending`: Kode baru dibuat dan siap digunakan.
    *   `used`: Kode sudah berhasil diverifikasi (tidak bisa dipakai ulang).
*   **Keamanan**: Setiap kali OTP baru diminta, kode `pending` yang lama untuk email tersebut akan otomatis dianggap kedaluwarsa.

### 🍏 Integrasi Frontend (Pinia):
Setelah API `verify-otp` mengembalikan data, frontend akan memprosesnya sebagai berikut:
1.  **Data Response**: API mengembalikan objek `user`, `roles` (array of strings), dan `access_token`.
2.  **Pinia Store**: Memanggil fungsi `authStore.setAuthData(user, roles, token)`.
3.  **Persistensi**: Data disimpan otomatis di `localStorage` agar sesi tidak hilang saat refresh halaman.
4.  **Role Guard**: Role digunakan untuk menentukan visibilitas menu (contoh: `authStore.isCustomer` atau `authStore.isAdmin`).
5.  **Authorization Header**: Token dikirimkan pada setiap request API selanjutnya melalui header:
    `Authorization: Bearer <your_token>`


---

## 📜 3. Pengelolaan Sertifikat (Certificate Generation)

Modul ini digunakan untuk membuat sertifikat PDF secara otomatis dengan menempelkan nama peserta di atas template yang sudah ada.

### Prasyarat:
*   Template PDF harus ada di: `public/certificate-template/qurbanhub-certificate.pdf`
*   Order harus berstatus **Paid** (lunas).

### Alur Kerja (Workflows):
1.  **Daftar Order**:
    `GET /api/certificates/orders` -> Menampilkan semua order yang siap dibuatkan sertifikat.
2.  **Daftar Peserta**:
    `GET /api/certificates/order/{id}/participants` -> Menampilkan nama-nama peserta dalam satu order.
3.  **Generate Massal**:
    `POST /api/certificates/order/{id}/generate-bulk` -> Membuat file PDF untuk semua peserta sekaligus.

### Cara Tes Cepat (CLI):
Gunakan script tester yang sudah disediakan untuk mensimulasikan proses generate:
```bash
php scratch/test_certificate.php
```

### Hasil File:
Sertifikat yang dihasilkan akan disimpan di:
`storage/app/public/certificates/`

---

## 🛠 Troubleshooting & Tips

*   **Z-Index Modal**: Jika popup konfirmasi tertutup oleh modal Bootstrap di frontend, pastikan CSS `.swal2-container { z-index: 9999 !important; }` sudah terpasang.
*   **Storage Link**: Jika file sertifikat tidak bisa diakses/didownload dari browser, pastikan Anda sudah menjalankan link storage:
    ```bash
    php artisan storage:link
    ```
*   **Syntax Error**: Jika muncul error "unexpected token public", pastikan semua kurung kurawal `{ }` di Controller sudah tertutup dengan benar.

---
**QurbanHub Tech Team**
