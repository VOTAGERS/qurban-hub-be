# Session Summary Update - 2026-05-05

## 1. Role-Based Access Control (RBAC) Refactor
*   **Database**: Menambahkan `role_code` pada tabel `role_accesses` dan mengubah relasi di `user_roles` agar menggunakan `role_code` (string) alih-alih ID numerik.
*   **Model**: Update `User`, `RoleAccess`, dan `UserRole` untuk mendukung struktur baru dan audit fields.
*   **Filtering**: Mengubah fungsi `index` pada `RoleAccessController` dan `UserAccessController` agar hanya menampilkan data dengan status `active`.

## 2. User Access Management
*   **Backend**: Membuat `UserAccessController` dengan fitur **Sync Logic**. Admin bisa mengirimkan array role untuk satu user, dan sistem otomatis melakukan sinkronisasi (Add/Remove soft delete).
*   **Frontend**: Membuat `UserAccessView.vue` dengan tampilan yang dikelompokkan per user (Grouped View) dan menggunakan badge untuk menampilkan banyak role sekaligus.
*   **Multi-Select**: Implementasi modal dengan checkbox list untuk pengelolaan role yang lebih efisien.

## 3. Passwordless OTP Login Foundation
*   **Database**: Membuat tabel `user_otps` dengan kolom `email`, `otp_code`, `expires_at`, dan 5 base entity fields.
*   **Model**: Membuat model `UserOtp` lengkap dengan trait `HasAuditFields`.
*   **Pinia Store**: Membuat `auth.ts` store di frontend untuk mengelola state user, token, dan roles.

## 4. UI/UX Improvements
*   **Sidebar**: Menambahkan grouping menu (Dashboard vs Management) dengan logika visibilitas berbasis role menggunakan `<template v-if>`. (Catatan: saat ini masih dikomentari untuk kemudahan pengembangan).
*   **Role Access View**: Implementasi CRUD master data role dengan validasi unik pada `role_code`.

## 5. Documentation
*   Dibuat file `docs/auth_and_roles.md` di backend dan frontend sebagai referensi teknis permanen.
