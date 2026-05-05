# Panduan Testing Email OTP Authentication

Dokumen ini menjelaskan cara menguji fitur login tanpa password menggunakan OTP (One-Time Password) di backend QurbanHub.

## 1. Persiapan
Pastikan environment di file `.env` sudah disetel ke driver `log` agar kode OTP tidak benar-benar dikirim ke email, melainkan dicatat di file log lokal.

```env
MAIL_MAILER=log
```

## 2. Alur Pengujian

### Langkah 1: Request OTP
Kirim permintaan untuk mendapatkan kode OTP ke email yang sudah terdaftar di sistem.

**Endpoint:** `POST /api/auth/send-otp`  
**Payload:**
```json
{
    "email": "admin@test"
}
```

**Contoh Curl:**
```bash
curl -X POST http://localhost:8000/api/auth/send-otp \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"email": "admin@test"}'
```

### Langkah 2: Ambil Kode dari Log
Buka file `storage/logs/laravel.log`. Cari baris paling bawah, Anda akan melihat template email yang berisi kode 6 digit.

Contoh tampilan di log:
```text
Your verification code for logging into QurbanHub is: 605204
```

### Langkah 3: Verifikasi OTP
Gunakan kode yang didapat dari log untuk melakukan proses login.

**Endpoint:** `POST /api/auth/verify-otp`  
**Payload:**
```json
{
    "email": "admin@test",
    "otp_code": "605204"
}
```

**Contoh Curl:**
```bash
curl -X POST http://localhost:8000/api/auth/verify-otp \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"email": "admin@test", "otp_code": "605204"}'
```

## 3. Respon Berhasil
Jika berhasil, Anda akan menerima respon JSON seperti berikut:

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": { ... },
        "token": "1|5iCRI7TjCmQMyIHh5gsTSICAvuaspA6IpkzFmGEu531a791e",
        "roles": [
            {
                "role_code": "eQurban-Admin",
                "role_name": "Admin"
            },
            ...
        ]
    }
}
```

*Gunakan `token` tersebut untuk mengakses route yang memerlukan autentikasi (Bearer Token).*

## 4. Skenario Pengujian Manual (Manual Verification)

Gunakan skenario berikut untuk memastikan logika autentikasi berjalan dengan aman:

### A. Pengujian Masa Berlaku (Expiration)
1. Request OTP ke email Anda.
2. Tunggu selama lebih dari 15 menit.
3. Coba verifikasi menggunakan kode tersebut.
4. **Ekspektasi:** Sistem mengembalikan error `422` dengan pesan "Invalid or expired OTP code".

### B. Pengujian Kode Salah (Invalid Code)
1. Request OTP ke email Anda.
2. Coba verifikasi menggunakan kode sembarang (misal: `123456`).
3. **Ekspektasi:** Sistem mengembalikan error `422`.

### C. Pengujian Penggunaan Ulang (Re-use Prevention)
1. Request OTP ke email Anda.
2. Verifikasi kode tersebut sampai berhasil (mendapatkan token).
3. Coba verifikasi kembali menggunakan kode yang sama.
4. **Ekspektasi:** Sistem mengembalikan error `422` karena status kode sudah berubah menjadi `used`.

### D. Pengujian Email Tidak Terdaftar (Unregistered Email)
1. Coba request OTP menggunakan email yang belum ada di tabel `users`.
2. **Ekspektasi:** Sistem mengembalikan error `422` dengan pesan "Email not registered in our system."

### E. Pengujian Logout
1. Lakukan login sampai mendapatkan `token`.
2. Panggil endpoint `POST /api/auth/logout` dengan header `Authorization: Bearer <token>`.
3. Coba akses route terproteksi (misal: `/api/user`) menggunakan token yang sama.
4. **Ekspektasi:** Sistem mengembalikan error `401 Unauthorized`.

---
## 5. Catatan Keamanan
- OTP berlaku selama **15 menit**.
- Setelah berhasil digunakan, status OTP akan berubah menjadi `used` dan tidak bisa dipakai lagi.
- Jika salah memasukkan kode atau kode sudah kadaluarsa, sistem akan mengembalikan error `422 Unprocessable Entity`.
