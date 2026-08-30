# Rencana Implementasi: Sistem Keringanan & Cicilan Pembayaran SPMB
Dokumen ini disusun sebagai panduan diskusi bersama pimpinan yayasan/sekolah untuk menentukan arah implementasi teknis fitur **Keringanan Potongan Biaya & Pembayaran Dicicil** pada sistem SPMB Sekolah Anak Saleh.

---

## 1. Latar Belakang & Tujuan
Pada kondisi riil di lapangan, terdapat calon wali murid yang mengajukan permohonan keringanan biaya (potongan) atau pengajuan untuk mencicil biaya administrasi akhir (DSP) kepada pihak sekolah/yayasan. 
Jika pengajuan disetujui, sistem harus dapat:
* Memotong total tagihan secara nominal sesuai nominal keringanan yang disetujui.
* Mengizinkan wali murid membayar biaya tersebut secara bertahap (dicicil) melalui Portal Pendaftar menggunakan Payment Gateway (Winpay), dengan batas minimal pembayaran yang ditentukan oleh administrator.

---

## 2. Pilihan Pendekatan Alokasi Pembayaran

Untuk mekanisme cicilan dinamis (Opsi A), terdapat 2 variasi metode pencatatan yang dapat dipilih oleh pimpinan:

### Rangkuman Perbandingan Pilihan
| Kriteria | Opsi A1: Dicicil Per Nama Biaya (Gedung, Seragam, dll) | Opsi A2: Dicicil Terhadap Total Tagihan Global |
| :--- | :--- | :--- |
| **Kemudahan Wali Murid** | ⚠️ Cukup rumit (harus memilih item mana yang dicicil). | **Sangat Mudah** (tinggal bayar nominal, sisa tagihan langsung berkurang). |
| **Biaya Transaksi (Admin Fee)** | ⚠️ Boros (terkena charge Winpay berkali-kali untuk item berbeda). | **Lebih Hemat** (transaksi dapat digabung dalam satu kali bayar). |
| **Alokasi Kas Sekolah** | **Sangat Rapi** (sistem langsung memisahkan kas Gedung/Seragam). | **Perlu Aturan Tambahan** (kas dibagi otomatis di backend secara proporsional). |
| **Kompleksitas Teknis** | Tinggi (harus melacak status pelunasan per baris biaya). | Rendah (cukup melacak sisa saldo tagihan pendaftaran). |

---

### Opsi A1: Pembayaran Cicilan Per Item Biaya (Component-Level)
Sistem melacak pelunasan pada setiap nama biaya secara independen.

* **Cara Kerja:**
  Wali murid masuk ke Portal, melihat daftar biaya (misal: Uang Gedung Rp 3.500.000, Seragam Rp 1.500.000). Mereka mencentang opsi "Cicil" di samping masing-masing biaya, memasukkan nominal cicilan untuk Uang Gedung (misal: Rp 1.500.000) dan nominal untuk Seragam (misal: Rp 500.000), lalu menekan bayar.
* **Database Schema:**
  Dibutuhkan tabel pivot pembayaran detail `payment_details` untuk merekam alokasi nominal transaksi masuk terhadap masing-masing ID komponen biaya (`spmb_fee_id`).

---

### Opsi A2: Pembayaran Cicilan Terhadap Total Tagihan (Lump-Sum Balance) -- *Rekomendasi Tim Teknis*
Sistem menggabungkan seluruh komponen biaya menjadi satu nilai tagihan utuh, lalu wali murid mencicil saldo tagihan global tersebut.

* **Cara Kerja:**
  Wali murid melihat total tagihan akhir mereka adalah Rp 5.000.000. Mereka memilih opsi "Bayar Sebagian (Cicil)", memasukkan angka pembayaran (misal: Rp 2.000.000), lalu membayar. Sisa tagihan otomatis berkurang menjadi Rp 3.000.000.
* **Alokasi Otomatis (Backend Priority):**
  Untuk kebutuhan pembukuan sekolah, sistem di backend akan otomatis membagi uang masuk Rp 2.000.000 tersebut dengan prioritas pelunasan item tertentu terlebih dahulu (misalnya: melunasi Seragam & Buku dulu sebesar Rp 1.500.000, sisanya Rp 500.000 dialokasikan ke Uang Gedung).
* **Database Schema:**
  Sederhana. Hanya perlu menambahkan kolom potongan (`discount_amount`) dan status cicilan di tabel `registrations` untuk divalidasi saat transaksi Winpay sukses.

---

## 3. Konsep UI/UX (Rancangan Tampilan)

### A. Sisi Admin Panel (Kelola Pendaftar)
Pada halaman detail pendaftar di admin panel, akan ditambahkan bagian **"Keringanan & Pembayaran"**:
```
+-------------------------------------------------------------+
| PENYESUAIAN BIAYA & KERINGANAN                              |
+-------------------------------------------------------------+
| Potongan Biaya (Diskon): [ Rp 1.000.000                  ]  |
| Alasan Keringanan:      [ Disetujui Yayasan (Anak Guru)  ]  |
|                                                             |
| [X] Izinkan Pembayaran Dicicil                              |
| Minimal Pembayaran Sekali Cicil: [ Rp 1.000.000          ]  |
+-------------------------------------------------------------+
| Ringkasan Baru:                                             |
| - Biaya Awal:    Rp 6.000.000                               |
| - Potongan:     (Rp 1.000.000)                              |
| - Total Tagihan: Rp 5.000.000                               |
+-------------------------------------------------------------+
|                                              [ Simpan Data ]|
+-------------------------------------------------------------+
```

### B. Sisi Portal Pendaftar (Wali Murid)
Tampilan pada halaman pembayaran akhir wali murid setelah disetujui dicicil:
```
+-------------------------------------------------------------+
| DETAIL PEMBAYARAN BIAYA ADMINISTRASI                        |
+-------------------------------------------------------------+
| 💡 Selamat, Anda disetujui mendapat keringanan potongan     |
|    sebesar Rp 1.000.000 & diizinkan mencicil pembayaran.    |
+-------------------------------------------------------------+
| - Total Tagihan: Rp 5.000.000                              |
| - Telah Dibayar: Rp 2.000.000                              |
| - Sisa Tagihan:  Rp 3.000.000                              |
+-------------------------------------------------------------+
| PILIH METODE BAYAR:                                         |
| ( ) Bayar Lunas Sisa Tagihan (Rp 3.000.000)                 |
| (o) Bayar Sebagian (Cicil)                                  |
|     Nominal Bayar: [ Rp 1.500.000                       ]   |
|     * Batas minimal cicilan sekali bayar: Rp 1.000.000      |
+-------------------------------------------------------------+
|                                           [ PROSES BAYAR  ] |
+-------------------------------------------------------------+
```

---

## 4. Langkah Implementasi Teknis (Setelah Keputusan Diambil)
1. **Migrasi Database:** Menambahkan field `discount_amount`, `discount_notes`, `allow_installment`, dan `min_installment_amount` pada tabel `registrations`.
2. **Kalkulator Tagihan:** Memperbarui fungsi hitung total tagihan agar mengurangi `discount_amount` dari snapshot biaya.
3. **Integrasi Callback Winpay:** Memodifikasi logic `PaymentController` agar jika tipe pembayaran adalah sebagian/cicilan, status registrasi diubah ke `partially_paid` (bukan langsung `completed`), kecuali sisa saldo tagihan sudah mencapai Rp 0.
4. **Desain Halaman Portal:** Menambahkan form input nominal cicilan dinamis dengan validasi JavaScript di sisi pendaftar.
