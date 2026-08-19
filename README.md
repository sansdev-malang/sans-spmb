# SANS SPMB - Sistem Penerimaan Murid Baru (Sekolah Anak Saleh)

[![Laravel v11](https://img.shields.io/badge/Laravel-v11-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Tailwind CSS v4](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![Winpay SNAP API](https://img.shields.io/badge/Winpay_SNAP_API-Staging/Prod-00529C?style=for-the-badge)](https://winpay.co.id)

SANS SPMB adalah platform digital modern untuk pengelolaan Penerimaan Murid Baru (SPMB) di Sekolah Anak Saleh. Dirancang dengan arsitektur tangguh menggunakan **Laravel 11**, **Tailwind CSS v4 (Plus Jakarta Sans)**, dan integrasi standar industri **Winpay SNAP BI Payment Gateway**.

---

## 🌟 Fitur Utama

### 1. Portal Calon Wali Murid (Candidate Experience)
*   **Menu Tabbed Modern:** Pemisahan navigasi rapi menjadi sub-halaman mandiri:
    *   **Dashboard:** Ringkasan progres pengisian berkas, status pembayaran, profil calon siswa, dan pengumuman panitia.
    *   **Formulir:** Wizard dinamis multi-step untuk melengkapi data diri, orang tua, dan dokumen persyaratan (otomatis terkunci setelah kirim).
    *   **Payment:** Rincian biaya seleksi, inisiasi invoice Winpay, instruksi transfer, unduhan QRIS (PNG), dan utility developer sandbox.
    *   **Verification:** Laporan visual status pemeriksaan berkas kelengkapan dari panitia penerimaan.
    *   **Observation:** Jadwal tes wawancara kesiapan belajar daring beserta tautan *Zoom Meeting* dan dokumen panduan resmi.
    *   **Final Result:** Surat keputusan kelulusan utama secara digital beserta pencetakan kartu tanda kelulusan.
*   **Tema Fleksibel:** Toggle Dark/Light mode persisten secara instan.
*   **Keamanan & Notifikasi:** Dropdown profil interaktif dan laci bel notifikasi masuk.

### 2. Integrasi Winpay SNAP BI Payment Gateway
*   **SNAP BI Standard:** Keamanan tinggi menggunakan tanda tangan digital (RSA SHA256) sesuai regulasi Bank Indonesia.
*   **Multi-Environment:** Konfigurasi dinamis untuk mode **Simulator Lokal**, **Sandbox (Staging)**, dan **Production (Live)**.
*   **Master Payment Channels:** Status ketersediaan bank Virtual Account (Mandiri, BRI, BNI, BCA) dan QRIS dikontrol dari database dan ditampilkan menggunakan toggle switch premium.
*   **Sinkronisasi Otomatis:** Fitur pembaruan otomatis list bank langsung dengan API eksternal Winpay (*Auto-Sync Channels*).

### 3. Panel Administrasi (Admin Command Center)
*   **Manajemen SPMB:** CRUD Tahun Ajaran (Period), Gelombang (Wave), dan Jalur Pendaftaran (Type).
*   **Setting Formulir Dinamis:** Panitia dapat menambah, merubah, menyusun urutan tahapan (*steps*), dan membuat kolom pertanyaan baru (*fields* - teks, angka, tanggal, email, file, dropdown) secara instan tanpa menyentuh kode program.
*   **Manajemen Pendaftar:** List registrasi pendaftar beserta modal verifikasi data lengkap, lampiran berkas, persetujuan/penolakan, serta input catatan panitia.
*   **Manajemen Pengguna:** CRUD akun pengguna yang dikelompokkan secara terstruktur berdasarkan tab Role (Candidate, Admin, dll) beserta fitur Reset Password cepat.

---

## 🛠️ Persyaratan Sistem
*   PHP >= 8.2
*   Composer
*   Node.js & NPM
*   Database (MySQL / PostgreSQL / SQLite)

---

## 🚀 Panduan Instalasi & Menjalankan

1.  **Clone repositori:**
    ```bash
    git clone https://github.com/sansdev-malang/sans-spmb.git
    cd sans-spmb
    ```

2.  **Instalasi dependensi PHP & Node.js:**
    ```bash
    composer install
    npm install
    ```

3.  **Salin file environment & konfigurasi database:**
    ```bash
    cp .env.example .env
    # Sesuaikan konfigurasi DB_DATABASE, DB_USERNAME, DB_PASSWORD di file .env
    ```

4.  **Generate app key & link storage berkas:**
    ```bash
    php artisan key:generate
    php artisan storage:link
    ```

5.  **Jalankan migrasi database beserta data awal (Seeder):**
    ```bash
    php artisan migrate:fresh --seed
    ```

6.  **Jalankan server pembangunan:**
    ```bash
    # Terminal 1: Laravel Server
    php artisan serve

    # Terminal 2: Vite compiler
    npm run dev
    ```

---

## 🔒 Akun Demo Login Bawaan (Default Seed)
Setelah menjalankan perintah `--seed`, Anda dapat menguji portal menggunakan akun dummy berikut:

*   **Akun Wali Murid (Candidate):**
    *   **Email:** `candidate@example.com`
    *   **Password:** `password`
    *   *Keterangan: Akun ini sudah terisi lengkap data draf pendaftaran bawaan (14 field & berkas mock) sehingga Anda bisa langsung menguji tombol "Kirim Pendaftaran" dan inisiasi pembayaran Winpay.*

*   **Akun Panitia (Admin):**
    *   **Email:** `admin@example.com`
    *   **Password:** `password`

---

## 🧪 Pengujian API E2E Terpadu
Untuk memastikan seluruh modul SNAP API, signature generator, simulator Winpay, webhook callback, dan alur database berjalan tanpa celah, jalankan perintah pengujian integrasi berikut:
```bash
php artisan app:test-spmb-api
```
*(Pengujian ini mensimulasikan registrasi, login, pengiriman berkas, generate VA invoice Winpay, simulasi transfer webhook sukses, peninjauan berkas oleh admin, hingga kelulusan secara otomatis).*

---

## 💻 Kontribusi
Proyek SANS SPMB ini bersifat open-source. Hubungi tim **Sekolah Anak Saleh** / **Sansdev Malang** untuk informasi lebih lanjut mengenai deployment produksi yayasan.
