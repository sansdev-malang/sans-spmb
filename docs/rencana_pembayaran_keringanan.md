# Rencana Implementasi: Sistem Keringanan & Cicilan Pembayaran SPMB
Dokumen ini disusun sebagai spesifikasi teknis dan panduan implementasi fitur **Keringanan Potongan Biaya & Kebijakan Pembayaran Dicicil (Model Hibrida Fleksibel)** pada sistem SPMB Sekolah Anak Saleh.

---

## 1. Latar Belakang & Kebutuhan Lapangan

Pada penerimaan siswa baru, pihak sekolah/yayasan seringkali menghadapi kondisi khusus calon wali murid:
1. **Permohonan Keringanan (Diskon/Potongan)**: Mendapatkan potongan biaya karena prestasi, anak guru/karyawan, keluarga tidak mampu, atau kebijakan pimpinan yayasan.
2. **Kebutuhan Cicilan Biaya Masuk (DSP/Pangkal)**: Wali murid mengajukan permohonan untuk mencicil biaya masuk dalam beberapa kali pembayaran.
3. **Karakteristik Komponen Biaya Sekolah**:
   * **Biaya Wajib Lunas di Awal**: Biaya operasional fisik seperti *Seragam, Buku/Modul, dan Perlengkapan* harus segera dibelanjakan ke vendor/konveksi sehingga **tidak boleh dicicil**.
   * **Biaya yang Boleh Dicicil**: Biaya investasi/pengembangan seperti *Uang Gedung (DSP) atau Infaq Pembangunan* **diizinkan dicicil** hingga batas waktu yang disepakati.

Sistem harus mampu mengakomodasi kebutuhan di atas secara **fleksibel, aman, transparan bagi wali murid**, dan **tidak membebani wali murid dengan biaya admin gateway yang berulang-ulang**.

---

## 2. Arsitektur Model Hibrida (Fleksibel: Global & Per Komponen)

Sistem mengadopsi pendekatan **Model Hibrida Terintegrasi**:

```
+-----------------------------------------------------------------------------------+
|                            ADMINISTRASI / KEUANGAN                                |
|  - Menentukan Potongan Biaya (Diskon) & Alasan                                    |
|  - Memilih Mode Cicilan: [Tidak Dicicil] | [Cicil Global] | [Cicil Biaya Tertentu]|
|  - Menentukan Minimal Nominal Cicilan per Transaksi (Misal: Rp 1.000.000)         |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
|                             PORTAL WALI MURID                                     |
|  - Rincian Biaya: Menampilkan tanda [Wajib Lunas] dan [Boleh Dicicil]             |
|  - Pembayaran 1 Kali Transaksi Winpay (Hemat Biaya Admin Fee)                     |
|  - Input Nominal Cicilan (Divalidasi >= Batas Minimal Pembayaran Awal)            |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
|                        BACKEND WATERFALL ALLOCATION & STATUS                      |
|  - Pembayaran sukses masuk -> Prioritas melunasi [Biaya Wajib] lebih dulu         |
|  - Sisa dana dialokasikan mengurangi saldo [Biaya Boleh Dicicil]                  |
|  - Status: Sisa > 0 -> 'partially_paid' | Sisa = 0 -> 'paid / completed'          |
+-----------------------------------------------------------------------------------+
```

### Keunggulan Utama Model Ini:
1. **Satu Transaksi Pembayaran (Single Invoice)**: Wali murid tidak perlu membuat invoice terpisah untuk seragam dan gedung. Cukup satu invoice pembayaran Winpay.
2. **Otomatisasi Kas Keuangan**: Sistem backend secara transparan mendistribusikan uang masuk ke pos kas yang tepat (pos seragam terisi penuh duluan, lalu pos gedung berkurang bertahap).
3. **Kontrol Penuh Admin**: Fleksibel untuk diterapkan pada kasus per kasus pendaftar sesuai persetujuan pimpinan yayasan.

---

## 3. Desain Tampilan (UI/UX)

### A. Sisi Admin Panel (Detail Pendaftar SPMB)
Pada tab/bagian administrasi keuangan calon siswa di admin panel:

```
+---------------------------------------------------------------------------------+
| PENYESUAIAN BIAYA & KEBIJAKAN KERINGANAN                                        |
+---------------------------------------------------------------------------------+
| Potongan Biaya (Diskon): [ Rp 1.000.000                  ]                      |
| Catatan / Alasan:       [ Disetujui Yayasan - Keringanan Anak Guru            ] |
|                                                                                 |
| Kebijakan Pembayaran:                                                           |
| ( ) Wajib Lunas Sekaligus (Standar)                                             |
| ( ) Boleh Dicicil Seluruh Komponen Biaya (Global)                               |
| (o) Boleh Dicicil Pada Komponen Tertentu:                                       |
|                                                                                 |
|   Komponen Biaya          Nominal        Kebijakan Cicilan                      |
|   ---------------------------------------------------------------------------   |
|   1. Formulir & Asesmen   Rp   300.000   [ ] Wajib Lunas Awal                   |
|   2. Seragam & Modul      Rp 1.500.000   [ ] Wajib Lunas Awal                   |
|   3. Uang Gedung (DSP)    Rp 4.000.000   [X] Diizinkan Dicicil                  |
|                                                                                 |
| Batas Minimal Cicilan per Transaksi: [ Rp 1.000.000      ]                      |
+---------------------------------------------------------------------------------+
| Ringkasan Perhitungan:                                                          |
| - Total Biaya Awal:        Rp 5.800.000                                         |
| - Potongan Diskon:        (Rp 1.000.000)                                        |
| - Total Tagihan Bersih:    Rp 4.800.000                                         |
| - Biaya Wajib Lunas Awal:  Rp 1.800.000                                         |
| - Minimal Pembayaran Ke-1: Rp 2.800.000 (Wajib Lunas + Minimal Cicilan Gedung)  |
+---------------------------------------------------------------------------------+
|                                                   [ Simpan Pengaturan Biaya ]   |
+---------------------------------------------------------------------------------+
```

---

### B. Sisi Portal Pendaftar (Wali Murid)
Halaman pembayaran calon siswa di portal pendaftar yang mendapatkan persetujuan cicilan:

```
+---------------------------------------------------------------------------------+
| INFORMASI TAGIHAN & PEMBAYARAN MASUK                                            |
+---------------------------------------------------------------------------------+
| 💡 Selamat! Anda telah disetujui mendapatkan Keringanan Biaya sebesar           |
|    Rp 1.000.000 dan diizinkan melakukan pembayaran bertahap (cicilan).          |
+---------------------------------------------------------------------------------+
| RINCIAN KOMPONEN BIAYA:                                                         |
| • Formulir & Asesmen:   Rp   300.000   [🔒 Wajib Lunas Awal]                    |
| • Seragam & Modul:      Rp 1.500.000   [🔒 Wajib Lunas Awal]                    |
| • Uang Gedung (DSP):    Rp 3.000.000   [🔓 Diizinkan Dicicil] (Setelah Potongan)|
| ------------------------------------------------------------------------------- |
| Total Tagihan Bersih:   Rp 4.800.000                                            |
| Total Telah Terbayar:   Rp         0                                            |
| Sisa Tagihan Saat Ini:  Rp 4.800.000                                            |
+---------------------------------------------------------------------------------+
| OPSI PEMBAYARAN:                                                                |
| ( ) Bayar Lunas Sisa Tagihan (Rp 4.800.000)                                     |
| (o) Bayar Sebagian (Cicilan Bertahap)                                           |
|                                                                                 |
|     Nominal Pembayaran Sekarang: [ Rp 2.800.000                        ]        |
|     * Batas minimal pembayaran transaksi ini: Rp 2.800.000                      |
|       (Rp 1.800.000 biaya wajib lunas + Rp 1.000.000 minimal cicilan gedung)    |
+---------------------------------------------------------------------------------+
|                                                   [ LANJUT KE PEMBAYARAN ➔ ]    |
+---------------------------------------------------------------------------------+
```

---

## 4. Spesifikasi Database & Skema Data

### 1. Modifikasi Tabel `registrations`
Menambahkan kolom-kolom pendukung keringanan dan aturan cicilan:
* `discount_amount` (`decimal(12,2) unsigned default 0`): Nilai nominal potongan biaya.
* `discount_notes` (`varchar(255) nullable`): Keterangan/alasan keringanan potongan.
* `installment_mode` (`enum('none', 'all', 'selective') default 'none'`): 
  * `none`: Wajib lunas sekaligus.
  * `all`: Semua biaya boleh dicicil.
  * `selective`: Hanya komponen biaya tertentu yang boleh dicicil.
* `installment_allowed_fee_ids` (`json nullable`): Array ID komponen biaya (`spmb_fee_id`) yang diizinkan dicicil jika mode = `selective`.
* `min_installment_amount` (`decimal(12,2) unsigned default 0`): Batas minimal pembayaran cicilan per transaksi.
* `installment_approved_by` (`foreignId nullable`): User ID admin yang menyetujui.
* `installment_approved_at` (`timestamp nullable`): Tanggal dan waktu persetujuan.

### 2. Modifikasi / Penyesuaian Tabel `payments` & Riwayat Transaksi
Setiap kali wali murid membayar cicilan melalui Winpay:
* Transaksi tercatat di tabel `payments` dengan `payment_type = 'installment'` atau `'re_registration'`.
* Status transaksi sukses Winpay callback akan:
  1. Menghitung akumulasi pembayaran `total_paid = SUM(payments.amount WHERE status = 'settled')`.
  2. Menghitung sisa saldo `remaining_balance = total_net_fee - total_paid`.
  3. Jika `remaining_balance > 0`, set `registrations.status = 'partially_paid'`.
  4. Jika `remaining_balance <= 0`, set `registrations.status = 'completed'` / `'paid'`.

---

## 5. Logika Backend & Rumus Kalkulasi

### 1. Menghitung Total Tagihan Bersih
```php
$grossFee = $registration->calculateTotalGrossFee(); // Total sebelum diskon
$discount = $registration->discount_amount ?? 0;
$netFee = max(0, $grossFee - $discount);
```

### 2. Menghitung Batas Minimal Pembayaran Transaksi (Dynamic Minimum)
```php
$totalPaid = $registration->payments()->where('status', 'settled')->sum('amount');
$remaining = max(0, $netFee - $totalPaid);

if ($registration->installment_mode === 'none') {
    // Wajib lunas penuh
    $minPayment = $remaining;
} elseif ($registration->installment_mode === 'all') {
    // Cicil global: minimal sesuai min_installment_amount atau sisa tagihan jika sisa < min
    $minPayment = min($remaining, $registration->min_installment_amount ?: 500000);
} elseif ($registration->installment_mode === 'selective') {
    // Komponen selektif:
    $mandatoryFeesTotal = $registration->getMandatoryFeesTotal(); // Biaya yang TIDAK boleh dicicil
    $mandatoryRemaining = max(0, $mandatoryFeesTotal - $totalPaid);
    
    $installmentPart = min($remaining - $mandatoryRemaining, $registration->min_installment_amount ?: 500000);
    $minPayment = $mandatoryRemaining + $installmentPart;
}
```

### 3. Logika Alokasi Pembukuan Otomatis (Waterfall Allocation)
Ketika dana pembayaran masuk Rp $X$:
1. Alokasikan terlebih dahulu untuk melunasi item **Wajib Lunas Awal** (Formulir, Seragam, dsb).
2. Sisa dana dialokasikan ke item **Boleh Dicicil** (Uang Gedung).
3. Bukti kwitansi / invoice menampilkan rincian alokasi ini secara transparan.

---

## 6. Rencana Tahapan Implementasi (Action Plan)

| Tahap | Aktivitas | Target Output |
| :--- | :--- | :--- |
| **Tahap 1** | **Migrasi Database** | Menambahkan kolom `discount_amount`, `discount_notes`, `installment_mode`, `installment_allowed_fee_ids`, `min_installment_amount` pada tabel `registrations`. |
| **Tahap 2** | **Model & Helper Logic** | Menambahkan method helper di model `Registration.php` (`getNetFeeAttribute`, `getMinInstallmentRequiredAttribute`, `getInstallmentStatusLabelAttribute`). |
| **Tahap 3** | **Admin Panel UI & Controller** | Menambahkan form input persetujuan keringanan & cicilan pada halaman detail pendaftar di Admin (`admin.candidates.show` & `CandidatesController`). |
| **Tahap 4** | **Portal Pembayaran Wali Murid** | Memperbarui view pembayaran portal agar menampilkan rincian status per item, kalkulasi sisa saldo, dan form pilihan nominal cicilan dengan validasi real-time. |
| **Tahap 5** | **Integrasi Payment Gateway & Webhook** | Menyesuaikan pembuatan transaksi Winpay (`WinpayService`) untuk nominal parsial dan webhook handler untuk update status `partially_paid` / `completed`. |
| **Tahap 6** | **Kwitansi & Reporting** | Penyesuaian cetak bukti pembayaran & ekspor Excel agar menyertakan data riwayat cicilan serta sisa piutang pendaftar. |
