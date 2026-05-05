# Authentication & Role Management Documentation (Backend)

Sistem ini mengelola hak akses pengguna berbasis peran (RBAC) dengan mekanisme sinkronisasi multi-role.

## 1. Database Schema Changes

### Tabel `users`
*   Menambahkan kolom `password` (nullable) untuk mendukung transisi ke sistem password/OTP di masa depan.

### Tabel `role_accesses`
*   **PK**: `id_role_access`
*   **Kolom Baru**: `role_code` (string, unique) - Digunakan sebagai identifier unik yang lebih konsisten daripada ID numerik.
*   **Kolom Status**: `active`, `inactive`, `deleted` (Soft Delete).

### Tabel `user_roles`
*   **FK**: `id_user` -> `users.id_user`
*   **FK**: `role_code` -> `role_accesses.role_code`
*   Mendukung hubungan Many-to-Many antara User dan Role.

---

## 2. API Endpoints

### Role Access Management
*   `GET /api/role-access` : Mengambil daftar role aktif.
*   `POST /api/role-access` : Membuat role baru.
*   `PUT /api/role-access/{id}` : Mengupdate data role.
*   `DELETE /api/role-access/{id}` : Mengubah status role menjadi `deleted` (Soft Delete).

### User Access Management
*   `GET /api/user-access` : Mengambil daftar mapping user-role yang aktif.
*   `POST /api/user-access` : **Sync Mechanism**. Menerima `id_user` dan array `role_codes`.
    *   Menambahkan role baru yang belum dimiliki.
    *   Menghapus (soft delete) role yang tidak disertakan dalam array.
*   `GET /api/user-access/user/{userId}` : Mengambil role yang dimiliki user tertentu.
*   `DELETE /api/user-access/{id}` : Menghapus akses role tertentu dari user.

---

## 3. Mekanisme Soft Delete
Aplikasi ini tidak menggunakan fitur SoftDeletes bawaan Laravel secara default, melainkan menggunakan kolom `status` ('deleted').
*   Query `index` selalu menyertakan `where('status', 'active')`.
*   Query `destroy` melakukan update `status = 'deleted'`.

---

## 4. Multi-role Sync Logic
Pada `UserAccessController@store`, sistem melakukan perbandingan antara role saat ini di database dengan role yang dikirim dari frontend. Hal ini memungkinkan admin untuk mengelola seluruh akses user dalam satu kali klik (Check/Uncheck).
