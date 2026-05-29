# SPK Ngacoan - Backend API (Laravel 13)

Repositori ini berisi backend API untuk Sistem Pendukung Keputusan (SPK) pemilihan supplier terbaik pada ekosistem bisnis Mie Ngacoan. Sistem ini mengimplementasikan algoritma EDAS (Evaluation based on Distance from Average Solution) dan menggunakan arsitektur decoupled (terpisah) dengan frontend Vue 3.

---

## Fitur Utama & Cakupan Sistem

### 1. Core Engine SPK (EDAS Algorithm)
- Komputasi Otomatis: Perhitungan matriks perantara secara real-time meliputi Average Solution (AV), Positive Distance from Average (PDA), Negative Distance from Average (NDA), Weighted Sum (SP/SN), hingga akumulasi skor akhir Appraisal Score (AS).
- Auditability: Menyediakan rincian koordinat matriks matematika beralur transparan untuk kebutuhan validasi akurasi rumus bagi tim penguji/sidang.
- Sesi Riwayat: Pencatatan log permanen hasil keputusan akhir yang aman dari risiko Soft Deletes data master (Supplier/Kriteria).

### 2. Autentikasi & Otorisasi Tingkat Lanjut
- Stateful API Authentication: Manajemen token aman menggunakan Laravel Sanctum.
- Role-Based Access Control (RBAC): Pemisahan hak akses ketat antara Owner (Akses penuh CRUD data master, manajemen user, dan eksekusi EDAS) dan Pengelola (Hanya input nilai aktual matriks evaluasi harian).
- Forgot Password via OTP: Alur pemulihan kata sandi modern menggunakan 6 digit kode OTP dinamis dengan proteksi kadaluwarsa token (15 menit).

### 3. Database Integrity & Guarding
- Menggunakan basis data PostgreSQL dengan penanganan kendala not-null constraint pada skema tabel users (kolom username) dan tabel decision_histories (kolom calculated_at).

---

## Stack Teknologi

- Framework: Laravel 13.x
- Language: PHP 8.3+
- Database: PostgreSQL 16+
- Authentication: Laravel Sanctum
- Development Server: Laragon / PHP Artisan CLI

---

## Langkah Instalasi Lokal

Ikuti panduan berikut untuk menjalankan proyek backend di komputer lokal Anda:

### 1. Klon Repositori
```bash
git clone https://github.com/USERNAME_KAMU/NAMA_REPO_BACKEND.git
cd NAMA_REPO_BACKEND
```
### 2. Instalasi Dependensi Composer
```bash
composer install
```
### 3. Konfigurasi Environment File
Salin file .env.example menjadi .env
```bash
cp .env.example .env
```
Buka file .env dan sesuaikan konfigurasi database PostgreSQL Anda:
```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ngacoan_db
DB_USERNAME=postgres
DB_PASSWORD=masukkan password
```

### 4. Masukkan data Mail di env

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Database Migration & Seeder
```bash
php artisan migrate --seed
```

### 7. Bersihkan Optimasi Cache
```bash
php artisan config:clear
php artisan route:clear
```

### 8. Jalankan Server Lokal
```bash
php artisan serve
```

API kini dapat diakses melalui tautan default: http://127.0.0.1:8000/api/v1

---

## Dokumentasi Endpoint API (v1)

### Endpoint Publik (Tanpa Login)
- GET /api/v1/ping : Cek status koneksi server.
- POST /api/v1/login : Otentikasi masuk pengguna & penerbitan token Sanctum.
- POST /api/v1/forgot-password/send-otp : Mengirimkan 6 digit kode OTP ke log sistem.
- POST /api/v1/forgot-password/reset : Memvalidasi OTP dan memperbarui kata sandi baru.

### Endpoint Terproteksi (Wajib Bearer Token)
#### Peran: Owner & Pengelola
- GET /api/v1/me : Ambil profil pengguna yang sedang login.
- POST /api/v1/logout : Mencabut token akses aktif.
- GET /api/v1/criteria : Melihat daftar kriteria pembobotan.
- GET /api/v1/suppliers : Melihat daftar supplier aktif.
- GET /api/v1/evaluations : Melihat matriks nilai aktual evaluasi.
- GET /api/v1/decision-histories : Melihat rangkuman riwayat keputusan masa lalu.
- GET /api/v1/decision-histories/{id} : Melihat detail hasil perangkingan EDAS spesifik.

#### Peran: Khusus Owner (Protected via role:owner Middleware)
- POST /api/v1/calculate-edas : Memicu mesin kalkulasi algoritma EDAS untuk menghasilkan rekomendasi terbaik.
- POST /api/v1/evaluations/bulk : Mengisi atau memperbarui nilai matriks aktual secara massal.
- CRUD /api/v1/users : Manajemen data akun staf pengelola (Input manual username & proteksi self-delete).
- CRUD /api/v1/criteria : Modifikasi penuh (Tambah/Edit/Hapus) bobot dan jenis kriteria.
- CRUD /api/v1/suppliers : Modifikasi penuh data master supplier (Dilengkapi pengaman Soft Deletes).

---
