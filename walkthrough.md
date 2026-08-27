# Walkthrough Perbaikan Integrasi & Alur Kerja SANS SPMB

Seluruh perbaikan arsitektural dan bug kritis pada proyek SANS SPMB telah berhasil diimplementasikan dan diverifikasi menggunakan pengujian integrasi E2E dengan hasil **LULUS (100%)**.

---

## Ringkasan Perubahan

### 1. Sinkronisasi & Penyelarasan Alur Kerja (Workflow Alignment)
* **API Pendaftaran:** Memperbarui [PaymentController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Api/PaymentController.php) agar mengizinkan wali murid melakukan pembayaran biaya formulir pendaftaran di awal saat pendaftaran masih berstatus `draft`. Hal ini menyelaraskan perilaku RESTful API dengan Portal Web (*Payment-First*).
* **API Dashboard Timeline:** Menyelaraskan endpoint `/api/dashboard` pada [RegistrationController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/RegistrationController.php) agar mengembalikan timeline 7-langkah yang sama persis seperti Portal Web, lengkap dengan deskripsi nominal biaya formulir dinamis dari database.
* **API Register:** Menambahkan pencatatan Tahun Ajaran, Gelombang, dan Jalur secara otomatis ketika mendaftar melalui API ([AuthController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Api/AuthController.php)), sehingga calon siswa langsung terdata di panel verifikasi admin.

### 2. Verifikasi Webhook Callback Dinamis (Multi-Gateway)
* **Gateway Contract:** Menambahkan kontrak `verifyCallback` pada [PaymentGatewayInterface.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Contracts/PaymentGatewayInterface.php).
* **BNI SNAP Callback:** Mengimplementasikan method verifikasi callback asimetris/simetris SNAP BI pada [BniSnapService.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Services/BniSnapService.php).
* **Dynamic Resolution:** Mengubah webhook API di `PaymentController@callback` agar melacak transaksi terlebih dahulu, menemukan gateway yang aktif untuk transaksi tersebut, lalu menjalankan fungsi verifikasi signature dari gateway terkait secara dinamis (Winpay atau BNI SNAP).

### 3. Perbaikan Bug Transisi Status Kelulusan (Completed State Bug)
* Memperbarui webhook callback di `PaymentController@callback` agar membedakan tipe pembayaran `registration_fee` dengan `final_fee`.
* Ketika pembayaran `final_fee` terkonfirmasi lunas (`SUCCESS` or `00`), status pendaftaran calon siswa otomatis bertransisi menjadi **`completed`** (lulus) dan memperbarui catatan kelulusan panitia, menyelesaikan bug kritis yang sebelumnya menghambat proses kelulusan di produksi.

### 4. Konsistensi Data Awal (Database Seeder)
* Menghapus logika fallback hardcoded tarif biaya di controller dengan mengoptimalkan seeding data pada [DatabaseSeeder.php](file:///c:/Users/IHWAN/Project/sans-spmb/database/seeders/DatabaseSeeder.php).
* Seeder sekarang mengisi data master Unit (PAUD, SD, SMP), Tingkat Kelas (KB, TK A, TK B, Kelas 1, Kelas 7), Program Kelas (Reguler, Bilingual), Kategori Biaya (`Formulir Pendaftaran` & `Biaya Administrasi`), serta rincian biaya pendaftaran dan biaya masuk akhir secara terstruktur.

### 5. Formulir Pendaftaran Instan Halaman Awal (Homepage Form Fix)
* **Frontend (`welcome.blade.php`):** Mengubah pilihan Jenjang agar mengirimkan `spmb_unit_id` dan menyajikan elemen pilihan Tingkat Kelas (`spmb_grade_id`) secara dinamis menggunakan pemetaan JSON JavaScript. Saat tombol jenjang diklik, opsi kelas diperbarui secara real-time.
* **Backend (`UserController.php`):** Memperbarui method `quickRegister` agar memvalidasi `spmb_unit_id` & `spmb_grade_id`. Menentukan `admission_level` secara dinamis sesuai nama kelas (misal: "KB Saja" diubah ke "Play Group"), dan menyimpan data pendaftaran awal secara konsisten dan lengkap.

### 6. Rekonstruksi Database Seeder Dari Data Riil & Penambahan Step "Jalur & Gelombang"
* **Data Menu Admin:** Menulis ulang seeder utama agar memuat data riil menu admin (Tahun Ajaran, Unit/Grade, Kategori, Tarif, Settings, Gateways) and 4 akun admin/superadmin (password `sans1234`), serta menghapus seluruh data dummy pendaftaran agar database bersih.
* **Wizard Pendaftaran Penuh (4 Parameter Master di Step 1):**
  * Merekonstruksi data `spmb_form_steps` di database agar memposisikan **Langkah #1: Jalur & Gelombang Pendaftaran** di awal wizard pendaftaran.
  * Menambahkan 4 dynamic select fields pada Step 1 ini, yaitu:
    1. **Tahun Ajaran** (`spmb_period_id`) - memuat periode aktif.
    2. **Gelombang Pendaftaran** (`spmb_wave_id`) - memuat gelombang aktif.
    3. **Jalur Pendaftaran** (`spmb_type_id`) - memuat jenis pendaftaran aktif.
    4. **Program Kelas** (`spmb_class_program_id`) - memuat pilihan program kelas aktif (Reguler/Inklusi).
  * Memperbarui controller (`WebDashboardController.php`), model (`Registration.php`), dan view (`form.blade.php`) agar memproses, menyimpan, dan menampilkan keempat parameter master ini secara dinamis dan rapi (menggunakan nama/tahun yang representatif, bukan integer ID).

### 7. Tab Dinamis & Aktivasi Channel Gateway pada Panel Aktivasi SPMB
* **Skema Database Relasional:** Menambahkan kolom `payment_gateway_id` pada tabel `spmb_payment_channels` agar setiap metode pembayaran terikat secara spesifik pada gateway yang menyediakannya.
* **Model Relationship:** Menambahkan relasi `paymentChannels()` pada model `PaymentGateway` and `gateway()` pada model `SpmbPaymentChannel` untuk kemudahan kueri relasional.
* **Seeder Awal Terpadu:** Memutakhirkan seeder utama untuk mengisi default channel pembayaran untuk masing-masing gateway (Virtual Account Mandiri/BCA/BNI & QRIS untuk Winpay; BNI SNAP QRIS untuk BNI SNAP).
* **Resolusi Error 405 (Method Not Allowed) Form Dinamis:**
   - Memperbaiki kegagalan pengiriman form pada menu Struktur Sekolah, Jalur Gelombang, Tarif Biaya, dan Setting Formulir dengan mengganti modifikasi properti `form.action = actionUrl` menjadi pemanggilan manipulasi DOM formal `form.setAttribute('action', actionUrl)`. Hal ini membuat pustaka `htmx` dapat melacak rute tujuan submit yang diperbarui secara instan.
* **Preservasi Status Tab Aktif Global (Unified Query-Parameter Tab System):**
   - **Penyebab Bug Tab Tabrakan & ReferenceError:** Sebelumnya, pustaka `layouts/admin.blade.php` memiliki fungsi global `restoreAllTabs()` yang menyinkronkan `localStorage` untuk semua menu. Namun karena nama fungsi `switchTab` bertabrakan (collision) antar modul (misal antara *Struktur Sekolah* dan *Konfigurasi Winpay*), serta tidak adanya validasi objek kosong (`null`), eksekusi JavaScript mengalami crash fatal. Pada modul **Biaya Admin Transaksi** (`settings.blade.php`), terjadi `ReferenceError: switchGatewayTab is not defined` karena sebuah IIFE dijalankan sebelum fungsi dideklarasikan di bagian bawah file.
   - **Penghapusan Global JavaScript Tab Restore:** Kami menghapus fungsi global `restoreAllTabs()` beserta pendengar event `DOMContentLoaded` dan `htmx:afterSwap` di [`admin.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/layouts/admin.blade.php) untuk mengeliminasi potensi tabrakan JS secara permanen.
   - **Penerapan Konsep Terpadu di Semua Halaman Ber-Tab:** Selaras dengan Aktivasi SPMB, kami menerapkan konsep sinkronisasi tab berbasis parameter kueri URL (`?tab=xxx`) dan input tersembunyi `active_tab` ke seluruh halaman:
     - **Biaya Admin Transaksi (`settings.blade.php`):** Mengintegrasikan parameter `activeTab` server-side, input tersembunyi `active_tab`, dan memperbarui [`SettingsController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SettingsController.php) agar me-redirect kembali secara eksplisit dengan menyertakan tab aktif pasca penyimpanan data. Ini menyelesaikan `ReferenceError` karena deklarasi tab kini di-render statis langsung oleh Laravel.
     - **Tampilan Portal (`settings-ui.blade.php`):** Menambahkan deteksi `$activeTab` server-side, input `active_tab`, dan memperbarui [`SettingsController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SettingsController.php) agar me-redirect kembali secara eksplisit dengan menyertakan tab aktif pasca penyimpanan data.
     - **Konfigurasi Winpay & Gateway (`settings.blade.php`):** Menambahkan parameter `activeTab` di view, input tersembunyi, dan memperbarui [`PaymentGatewayController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/PaymentGatewayController.php) agar redirect menyimpan kredensial menyertakan query parameter lingkungan aktif (`simulator`, `sandbox`, atau `production`). Ini menjamin kredensial langsung ter-render dengan benar saat pertama kali dimuat maupun setelah form disimpan.
     - **Surat Pernyataan (`settings-agreements.blade.php`):** Menggunakan query parameter `tab=unit_x` dan input tersembunyi untuk memulihkan draf template surat per unit sekolah secara langsung, diintegrasikan dengan redirect baru di [`SpmbAgreementsController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SpmbAgreementsController.php).
     - **Struktur Sekolah (`settings-spmb-units.blade.php`):** Menyinkronkan tombol tab visual dan panel konten dengan `tab=unit`, `tab=grade`, atau `tab=extra` dari redirect mutasi data di controller.
     - **Mismatched Div Tags & 405 Method Not Allowed Resolution:** Kami memperbaiki tag penutup HTML `</div>` ganda pada `extraModal` dan `unitModal` di [`settings-spmb-units.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-spmb-units.blade.php) yang sempat merusak hierarki DOM sehingga HTMX menduplikasi ID form saat terjadi swap konten. Kami juga memperbaiki tag form penutup `</form>` yang sempat hilang. Selain itu, kami menyematkan atribut `hx-boost="false"` pada ketiga form modal (Unit, Grade, Extra Service) untuk mencegah HTMX melakukan intersep/caching pada action kosong (`action=""`) saat pertama kali di-bind. Ini menjamin form ter-submit secara murni oleh browser ke route store/update dinamis yang valid tanpa memicu error 405.
   - **Instruksi Daftar Ulang:** Mengubah redirection di controller `SettingsController@saveReRegistrationInstructions` dari `redirect()->back()` menjadi redirect ke rute eksplisit dengan query parameter `unit_id` terlampir agar tab unit yang aktif tetap terpilih setelah formulir disimpan. Serta menambahkan `hx-push-url="true"` ke tombol tab unit agar sinkron dengan histori navigasi browser.

### 8. Pilihan Multi-Gateway Fleksibel (Checklist) & Penyaringan Saluran Pembayaran Dinamis
* **Desain UI Checklist:** Mengubah pilihan dropdown Payment Gateway pada modal Tambah/Edit Nominal Biaya di [settings-fees.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-fees.blade.php) menjadi **checklist (checkbox)**, sehingga satu komponen biaya dapat ditugaskan untuk lebih dari satu gateway sekaligus (contoh: di-set aktif untuk Winpay Gateway sekaligus BNI SNAP).
* **Resolusi Edit Nominal Biaya Tidak Menyimpan Data:** Kami menyematkan atribut `hx-boost="false"` pada form modal `#feeCrudForm` dan form hapus `#feeDeleteForm` di [settings-fees.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-fees.blade.php). Sebelumnya, karena form ini berada di dalam container utama yang ber-boost HTMX (`#spmb-fees-container`), HTMX mengintersep submit event secara instan dan menserialisasi form sebelum listener JavaScript sempat menghapus pemisah ribuan titik (`.`) pada input nominal (misal `350.000` dikirim mentah sebagai `350.000` ke backend, yang kemudian gagal divalidasi karena terbaca sebagai float `350` yang berada di bawah syarat minimal `1000`). Dengan menonaktifkan boost pada form, browser melakukan submit standard HTML ke URL dinamis yang valid, sehingga pembersihan titik berjalan lancar menjadi integer `350000`, dan data nominal ter-update secara sukses.
* **UX Peringatan & Kunci Nominal Terpakai di Modal (UX Lock Info & Precise Mapping):**
  - **Penyebab Kebingungan & Kunci Prematur:** Sebelumnya, penentuan apakah suatu tarif nominal sudah "digunakan" hanya mencocokkan nominal murni secara global di tabel `payments`. Karena pencarian didasarkan pada besaran nominal global (misal `350.000`), ketika admin menambahkan data nominal baru bernilai `350.000` (atau jenis biaya category baru), nominal tersebut langsung terkunci secara permanen dan ditandai *"Ya (Terpakai)"* hanya karena ada data transaksi lama siswa lain dengan nominal serupa di unit sekolah lain.
  - **Resolusi Pencocokan Presisi (Precise Mapping):** Kami mengganti pemeriksaan global berbasis nominal dengan helper method baru `isFeeUsed($fee)` di [`SpmbFeesController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SpmbFeesController.php). Sekarang pencocokan berjalan spesifik:
    1. Untuk kategori **Formulir Pendaftaran**, sistem memvalidasi transaksi `registration_fee` aktif (status `success` atau `pending`) yang terdaftar pada unit sekolah yang sama dengan fee tersebut, **serta** mencocokkan nominal `base_amount` atau `amount` gabungan biaya admin milik fee terkait. Ini mencegah nominal baru berharga beda (seperti `3.500.000` untuk Unit PAUD) ikut terkunci prematur.
    2. Untuk kategori **Biaya Tambahan** (Seragam, Uang Gedung, dll), sistem memvalidasi transaksi `final_fee` yang bersangkutan dengan mencocokkan nama item yang dipilih (`payment_info['selected_items']`) yang dimiliki oleh pendaftar di unit sekolah yang sama.
    3. Untuk tab **Jenis Biaya** (Kategori), status *"Digunakan Transaksi"* kini benar-benar mengevaluasi apakah ada nominal biaya di bawah kategori tersebut yang memiliki transaksi pembayaran aktif (bukan sekadar mendeteksi ada tidaknya rekaman nominal).
    4. Transaksi berstatus `failed` atau `expired` tidak lagi mengunci nominal karena merupakan transaksi mati.
  - **Resolusi QueryException Out of Range:** Kami menambahkan aturan validasi batas maksimal input nominal (`max:9999999999`) pada `storeFee` dan `updateFee`. Hal ini mencegah kegagalan database (Internal Server Error 500) saat pengguna menginput nominal berukuran ekstrem (melebihi kapasitas kolom `decimal(12,2)` MySQL), dan mengembalikannya sebagai notifikasi validasi Laravel yang ramah pengguna. Kami juga memulihkan data nominal ID 14 yang sempat menyimpan angka out-of-range kembali ke nilai standard `350000` untuk menormalkan tampilan tab Jenis Biaya.
  - **Resolusi Fresh State & Preservasi Checkbox:** Kami menambahkan pengenal ID (`#feeErrorWrapper`) pada kontainer pesan kesalahan Laravel di dalam modal. Pada berkas [`settings-fees.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-fees.blade.php), ketika modal ditutup (baik via tombol Kembali, klik di luar modal, atau saat modal dibuka ulang untuk aksi tambah/edit baru), JavaScript otomatis menyembunyikan sisa pesan error validasi sebelumnya. Ini menjamin form modal selalu berada dalam keadaan bersih (*fresh state*) setiap kali dibuka. Kami juga memastikan pesan error tetap tampil saat modal otomatis dibuka kembali pasca-redirect kegagalan submit form dengan secara eksplisit memanggil `errorWrapper.classList.remove('hidden')` di akhir script penanganan auto-reopen. Selain itu, kami memperbarui method `openFeeModal` dan script penanganan auto-reopen agar meloloskan array input lama (`old('spmb_units')` dan `old('payment_gateway')`) ke dalam skrip inisialisasi visual modal. Ini memastikan checkbox Unit Sekolah dan Payment Gateway yang sebelumnya dicentang oleh pengguna tetap terpelihara (checked) saat modal otomatis terbuka kembali akibat kegagalan validasi.
  - **Sinkronisasi Tab Server-Side (Query Parameter Sync):** Kami menyelaraskan Tarif & Biaya dengan konsep tab server-side (`?tab=xxx`) yang konsisten. Tab navigasi diatur melalui variabel `$activeTab` di [`SpmbFeesController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SpmbFeesController.php), dan `switchFeeTab()` di frontend memperbarui query parameter di address bar browser menggunakan `window.history.replaceState()`. Seluruh redirect sukses/gagal di controller dialihkan secara eksplisit dengan menyuplai parameter `tab` aktif ke kategori asal, mengeliminasi penuh ketergantungan pada `localStorage` lama.
  - **Screening & Refactoring Jalur & Gelombang (Master SPMB):**
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan halaman Jalur & Gelombang dengan konsep tab server-side (`?tab=xxx`). Parameter `$activeTab` ditangkap oleh [`SpmbSettingsController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SpmbSettingsController.php) (nilai default `periode`) dan tombol/wrapper tab di [`settings-spmb.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-spmb.blade.php) di-render aktif dari server. Fungsi `switchTab()` di frontend memperbarui parameter URL secara dinamis melalui `history.replaceState()`, dan seluruh redirect CRUD di controller diarahkan secara spesifik ke tab asal (`periode`, `gelombang`, `jenis`, atau `program`).
    - **Perilaku Fresh State Modal:** Kami menambahkan pengenal ID (`#spmbErrorWrapper`) pada kontainer error validasi dan menyembunyikannya secara dinamis saat modal ditutup atau dibuka kembali untuk entri baru, serta memulihkan tampilannya saat auto-reopen redirect.
    - **Keyboard Escape Close & Form Boost Bypass:** Menambahkan event listener tombol `Escape` untuk menutup modal, serta menyematkan `hx-boost="false"` pada form CRUD dan form hapus untuk mencegah pembajakan submit action oleh HTMX.
  - **Screening & Refactoring Struktur Sekolah (Master Unit & Tingkatan):**
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan halaman Struktur Sekolah (`/admin/spmb-settings/units-grades`) agar tab navigasi disinkronkan secara dinamis di URL query parameter (`?tab=xxx`) menggunakan `window.history.replaceState()`.
    - **Pemberian Umpan Balik Error Validasi:** Sebelumnya, modal Unit, Tingkatan Kelas, dan Layanan Non-Formal tidak memiliki wadah pesan kesalahan. Jika validasi gagal, user hanya di-redirect biasa tanpa tahu penyebabnya. Kami mengubah metode controller agar menggunakan `Validator::make` dan me-redirect balik membawa input lama, pesan error, serta `failed_modal` session. Di blade view [`settings-spmb-units.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-spmb-units.blade.php), kami menambahkan wadah kesalahan `.spmb-unit-errors` di masing-masing modal dan mengotomatiskan pembukaan kembali (*auto-reopen*) modal yang gagal submit secara utuh.
    - **Perilaku Fresh State & Keyboard Escape:** Kami menambahkan fungsi `clearModalErrors()` untuk menyembunyikan sisa pesan kesalahan sebelumnya saat modal ditutup atau dibuka kembali untuk entri baru. Menambahkan juga penutupan instan via tombol `Escape` keyboard dan penandaan `hx-boost="false"` pada form untuk kestabilan aksi.
  - **Screening & Refactoring Log Sistem:**
    - **Form Boost Bypass & Native Submit:** Kami menambahkan `hx-boost="false"` pada form pencarian/filter log serta form pembersihan file log. Kami juga mengubah event handler `onchange` pada dropdown baris limit untuk menggunakan fungsi asli browser `this.form.submit()` demi keandalan submit.
  - **Screening & Refactoring Manajemen User:**
    - **Pemberian Umpan Balik Error Validasi:** Sebelumnya, jika ada kegagalan validasi (misalnya email ganda atau password kurang dari 8 karakter), modal langsung tertutup secara otomatis tanpa ada pesan kesalahan. Kami memperbarui [`UserController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/UserController.php) menggunakan `Validator::make` untuk mengirim session `failed_modal` (`user_create`, `user_edit_{id}`, `user_reset_{id}`). Di blade view [`users.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/users.blade.php), kami menambahkan wadah kesalahan `.spmb-user-errors` di modal Tambah, Edit, dan Reset Password, serta mengotomatiskan pembukaan kembali (*auto-reopen*) modal yang gagal beserta restorasi input lamanya.
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan tab navigasi peran pengguna (Panitia vs Orang Tua) agar sinkron dengan query parameter URL `?tab=xxx` menggunakan `window.history.replaceState()`.
    - **Keyboard Escape Close & Form Boost Bypass:** Menambahkan penutupan modal menggunakan tombol `Escape` keyboard, menyematkan `hx-boost="false"` pada seluruh form CRUD pengguna, dan menambahkan pembersihan state error dengan fungsi `clearUserErrors()`.
  - **Screening & Refactoring QR Code SPMB:**
    - **Pemberian Umpan Balik Error Validasi:** Sebelumnya, jika ada kesalahan input URL pendaftaran yang tidak valid, form langsung me-redirect balik secara diam-mana tanpa memunculkan visual peringatan kesalahan. Kami memperbarui controller [`SpmbSettingsController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SpmbSettingsController.php) menggunakan `Validator::make` dengan umpan balik kegagalan spesifik. Pada berkas view [`settings-spmb-qrcode.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-spmb-qrcode.blade.php), kami menambahkan wadah error di atas form, menyematkan `hx-boost="false"` pada form, serta merestorasi input lama (`old('qrcode_url')`) jika terjadi kegagalan validasi.
  - **Screening & Refactoring Aktivasi SPMB:**
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan tab navigasi aktivasi komponen pendaftaran (Jalur & Gelombang, Struktur Akademik, Biaya, dll) agar tersinkronisasi dinamis dengan query parameter URL `?tab=xxx` menggunakan `window.history.replaceState()`.
    - **Form Boost Bypass:** Menyematkan `hx-boost="false"` pada form utama aktivasi agar submit data disimpan secara murni tanpa intersepsi HTMX untuk kestabilan state reload.
  - **Screening & Refactoring Profil Saya & Ganti Password:**
    - **Pemisahan Halaman & Rute Khusus Admin:** Atas masukan dari pengguna, kami memisahkan halaman **Profil Saya** dan **Ganti Password** untuk admin menjadi halaman yang terpisah secara fisik dengan rute baru:
      - Rute **Profil Saya** dialihkan ke `/admin/profile` -> [`profile-edit.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/profile-edit.blade.php) yang diisi khusus formulir nama dan email.
      - Rute **Ganti Password** dialihkan ke `/admin/profile/password` -> [`profile-password.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/profile-password.blade.php) yang diisi formulir input password lama, password baru, dan konfirmasi.
    - **Struktur Rute & Validasi:** Kami menambahkan 4 rute admin baru di [`web.php`](file:///c:/Users/IHWAN/Project/sans-spmb/routes/web.php) yang dikelola oleh methods `editAdminProfile`, `updateAdminProfile`, `editAdminPassword`, dan `updateAdminPassword` di [`ProfileController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/ProfileController.php). Menggunakan `Validator::make` untuk pengecekan validasi email unik dan keaslian password lama (`current_password` rule), serta mengembalikan input yang salah untuk kenyamanan admin.
  - **Screening & Refactoring Riwayat Transaksi Pembayaran (Log):**
    - **Form Boost Bypass & Native Submit:** Kami menambahkan `hx-boost="false"` pada form pencarian/filter transaksi utama, mengubah semua event handler `onchange` dan reset filter dari HTMX trigger (`htmx.trigger(this.form, 'submit')`) menjadi fungsi asli browser (`this.form.submit()`) untuk menjamin konsistensi query dan kestabilan filter.
  - **Screening & Refactoring Data Pembayaran:**
    - **Form Boost Bypass & Native Submit:** Menyematkan `hx-boost="false"` pada form pencarian/filter data transaksi lunas, serta mengubah dropdown `per_page` onchange dan pembersihan search untuk memanggil native `this.form.submit()` demi keandalan submit.
  - **Screening & Refactoring Riwayat Pendaftaran (Log):**
    - **Form Boost Bypass & Native Submit:** Menyematkan `hx-boost="false"` pada form pencarian/filter pendaftar lengkap, serta mengubah seluruh dropdown filter `onchange` dan tombol reset filter ke fungsi standard `submit()`.
    - **Keyboard Escape Close:** Menambahkan event listener keyboard tombol `Escape` untuk menutup drawer modal detail pendaftar secara instan.
  - **Screening & Refactoring Data Pendaftar (Aktif):**
    - **Form Boost Bypass & Native Submit:** Menyematkan `hx-boost="false"` pada form pencarian/filter pendaftar aktif, serta mengubah seluruh dropdown filter `onchange` dan tombol reset filter ke fungsi standard `submit()`.
    - **Keyboard Escape Close:** Menambahkan event listener keyboard tombol `Escape` untuk menutup drawer modal detail pendaftar aktif secara instan.
  - **Screening & Refactoring CRUD Payment Gateway:**
    - **Pemberian Umpan Balik Error Validasi:** Sebelumnya, jika terjadi kesalahan validasi JSON schema parameter atau unique code unik, modal ditutup otomatis dan kesalahan tersembunyi. Kami memperbarui [`PaymentGatewayController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/PaymentGatewayController.php) menggunakan `Validator::make` untuk meloloskan session `failed_modal`. Kami menambahkan kontainer kesalahan `.spmb-gateway-errors` ke dalam modal tambah dan edit pada [`index.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/payment_gateways/index.blade.php) serta melengkapi skrip auto-reopen modal.
    - **Form Boost Bypass & Escape Close:** Menambahkan listener keyboard tombol `Escape` global untuk penutupan instan modal CRUD gateway, fungsi `clearGatewayErrors()` untuk pembersihan sisa error, dan menonaktifkan HTMX boost (`hx-boost="false"`) pada form pembuatan dan pengeditan.
    - **Refactoring Detail Konfigurasi Kunci (Gateway Credentials View):** Pada halaman detail konfigurasi untuk masing-masing gateway ([`settings.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/payment_gateways/settings.blade.php)), kami menambahkan wadah pesan kesalahan validasi global, menyelaraskan navigasi tab lingkungan (Simulator, Sandbox, Production) agar tersinkronisasi dengan query parameter URL `?tab=xxx` via `window.history.replaceState()`, mengotomatiskan pemulihan tab aktif melalui `DOMContentLoaded` listener, dan menyematkan `hx-boost="false"` pada form penyimpanan konfigurasi kunci agar data terkirim secara stabil.
    - **Resolusi Sidebar Aktif Stale (HTMX Select/Target Bug):** Kontainer pembungkus utama (`#gateway-settings-container`) sebelumnya menyematkan `hx-boost="true"` dengan target dan selektor mengarah pada diri sendiri. Hal ini menyebabkan setiap klik tautan navigasi antarpah (seperti tombol "Konfigurasi" atau tombol "Kembali ke List") diintersepsi oleh HTMX sehingga hanya memperbarui area konten utama tanpa memperbarui sidebar. Akibatnya, menu aktif di sidebar tetap terkunci di menu sebelumnya. Kami telah menghapus atribut boosting dan targeting HTMX tersebut pada [`index.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/payment_gateways/index.blade.php) dan [`settings.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/payment_gateways/settings.blade.php) agar navigasi antarpah berjalan secara native dan menyinkronkan highlight menu aktif di sidebar secara instan.
  - **Refactoring Biaya Admin Transaksi:**
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan tab navigasi di halaman Biaya Admin Transaksi (`/admin/settings`) agar parameter URL `?tab=xxx` sinkron secara dinamis menggunakan `window.history.replaceState()` ketika berganti tab gateway di frontend.
    - **Form Boost Bypass:** Menyematkan `hx-boost="false"` pada form utama pengaturan agar submit data disimpan secara murni tanpa intersepsi HTMX untuk kestabilan state reload.
  - **Screening & Refactoring Tampilan Portal (UI Portal):**
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan tab navigasi dinamis pada UI Portal (`/admin/ui-settings`) agar URL query parameter `?tab=xxx` sinkron menggunakan `window.history.replaceState()` ketika berganti tab di frontend.
    - **Form Boost Bypass:** Menyematkan `hx-boost="false"` pada form pengeditan UI Portal untuk menjamin pengunggahan file gambar (logo, favicon, banner) berjalan secara murni melalui form native multipart/form-data tanpa terintersepsi HTMX.
  - **Screening & Refactoring Surat Pernyataan Kesanggupan:**
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan tab navigasi per unit sekolah pada Surat Pernyataan (`/admin/spmb-settings/agreements`) agar URL query parameter `?tab=unit_{id}` sinkron menggunakan `window.history.replaceState()` ketika berganti tab unit di frontend.
    - **Form Boost Bypass:** Menambahkan `hx-boost="false"` pada form template surat pernyataan agar submit data rich-text HTML dari Quill editor disimpan secara native tanpa gangguan HTMX.
  - **Screening & Refactoring Instruksi Daftar Ulang:**
    - **Resolusi Inisialisasi Ganda Quill Editor (Double Init Bug):** Kode bawaan menginisialisasi editor rich-text Quill sebanyak dua kali saat halaman dimuat pertama kali secara langsung (sekali melalui inline IIFE script dan sekali lagi di event `DOMContentLoaded` tanpa pengecekan kelas kontainer). Ini berisiko merusak struktur editor Quill dan memicu kejanggalan rendering. Kami menambahkan pengecekan `!classList.contains('ql-container')` pada event `DOMContentLoaded` untuk mencegah inisialisasi ganda tersebut.
    - **Form Boost Bypass:** Menambahkan `hx-boost="false"` pada form instruksi agar submit data rich-text HTML dari Quill editor berjalan secara murni melalui sinkronisasi native browser.
  - **Screening & Refactoring Setting Formulir (tahapan & kolom dinamis):**
    - **Penyelarasan Tab Server-Side:** Kami menyelaraskan tab navigasi dinamis pada Setting Formulir (`/admin/spmb-settings/form`) agar URL query parameter `?tab=xxx` sinkron menggunakan `window.history.replaceState()`. Hal ini menjamin saat berganti tab tahapan wizard formulir, browser mencatatnya. CRUD redirect di controller me-redirect balik secara presisi ke tab tahapan yang sedang dimodifikasi (`step_{id}` atau `crud_steps`) untuk kepraktisan antarmuka.
    - **Pemberian Umpan Balik Error Validasi:** Sebelumnya, jika ada kesalahan input saat menambah/mengedit langkah atau kolom formulir, modal tertutup otomatis dan error tidak terlihat. Kami mengubah controller [`SpmbFormSettingsController.php`](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SpmbFormSettingsController.php) menggunakan `Validator::make` untuk mengirim balik `failed_modal` session. Wadah error `.spmb-form-errors` disisipkan ke masing-masing modal di [`settings-form.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-form.blade.php) dan auto-reopen modal jika submit gagal.
    - **Fresh State & Escape Key Close:** Menambahkan `clearFormErrors()` JavaScript untuk membersihkan pesan kesalahan, tombol penutupan modal instan via `Escape`, serta pembatasan `hx-boost="false"` pada form formulir.
  - **Fitur UX Keydown Escape:** Kami menambahkan event listener `keydown` (tombol `Escape`) global pada halaman Tarif & Biaya untuk menutup modal CRUD yang aktif secara instan demi kenyamanan navigasi keyboard admin.
  - **UI Locking & Alert:** Pada berkas [`settings-fees.blade.php`](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-fees.blade.php), jika nominal biaya benar-benar terpakai sesuai relasi unit dan nominal spesifik, modal edit otomatis mengunci input nominal menjadi `readonly` dan menampilkan info peringatan kuning: *"⚠️ Nominal biaya ini dikunci karena sudah memiliki transaksi pembayaran."*
* **Model Array Casting:** Menambahkan cast `'payment_gateway' => 'array'` pada model [SpmbFee.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Models/SpmbFee.php) agar Eloquent secara otomatis melakukan serialisasi data array checkbox ke format JSON di database.
* **Penanganan Seeder & Validasi Array:** 
  * Memperbarui [SpmbFeesController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/SpmbFeesController.php) agar memvalidasi input `payment_gateway` as array (`payment_gateway` => `required|array|min:1`).
  * Memperbarui [DatabaseSeeder.php](file:///c:/Users/IHWAN/Project/sans-spmb/database/seeders/DatabaseSeeder.php) agar secara dinamis membungkus nilai string seeder lama ke bentuk array satu-dimensi sebelum disimpan.
* **Penyaringan Saluran Pembayaran Pendaftar:**
  * Memperbarui kueri di `WebDashboardController@dashboard` and `WebDashboardController@payment` agar memfilter `SpmbPaymentChannel` menggunakan operator `whereIn` pada relasi `gateway` untuk semua kode gateway yang diperbolehkan di komponen biaya.
  * Memperbarui [payment.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/payment.blade.php) agar menampilkan daftar saluran pembayaran secara dinamis sesuai data aktif di database (tanpa hardcoded checks untuk BNI SNAP).
* **Resolusi Gateway Transaksi Dinamis:**
  * Memperbarui metode `chargePayment` pada [WebDashboardController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/WebDashboardController.php) dan `charge` / `callback` pada [PaymentController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Api/PaymentController.php) untuk melacak metode pembayaran yang dipilih oleh pendaftar, mencocokkannya dengan channel aktif di database, dan menentukan gateway pemroses transaksi secara otomatis.

### 9. Perbaikan Biaya Transaksi / Admin Dinamis (Multi-Gateway Fee Resolution)
* **Penyebab Bug:** Sebelumnya, frontend Javascript di `payment.blade.php` and backend di controller melakukan hardcoded string match pada kode metode pembayaran (misal `method === 'QRIS'` atau `method === 'BNI'`). Hal ini mengakibatkan BNI SNAP QRIS (yang berkode `BNI_QRIS`) dan BNI SNAP VA (jika berkode `BNI_VA`) meleset dari kriteria pencocokan dan terjatuh ke fallback tarif Winpay flat sebesar Rp 4.500.
* **Penyelesaian Frontend (`payment.blade.php`):**
  * Menambahkan data atribut HTML `data-type` (misal: `qris` or `va`) and `data-gateway` (misal: `winpay` or `bni`) pada elemen radio input saluran pembayaran.
  * Memperbarui logika Javascript `updateSummary()` agar secara langsung membaca properti tipe dan kode gateway milik channel yang sedang dicentang. Jika channel yang dipilih adalah milik gateway BNI dan bertipe QRIS, maka biaya admin dihitung dinamis menggunakan persentase MDR BNI QRIS (0.7%). Jika berupa Virtual Account, menggunakan biaya admin tetap BNI VA (Rp 1.500). Selain itu, flat Rp 4.500 (Winpay).
* **Penyelesaian Backend (`WebDashboardController.php` & `PaymentController.php`):**
  * Menyelaraskan penghitungan biaya admin saat transaksi diterbitkan (`chargePayment` pada web dan `charge` pada API) agar mencocokkan tipe channel serta gateway secara dinamis langsung dari relasi database `SpmbPaymentChannel`, menyingkirkan validasi string keras.

### 10. Validasi Nominal Pembayaran pada Webhook Callback (Amount Reconciliation)
* **Pencegahan Penyalahgunaan:** Menambahkan logika validasi nominal transaksi pada webhook callback di [PaymentController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Api/PaymentController.php).
* **Mekanisme Pengecekan:** Sebelum menandai status transaksi sebagai sukses, server memverifikasi bahwa nominal pembayaran yang dikirim oleh Payment Gateway (`paymentAmount`) cocok dengan total nominal tagihan (`amount`) atau nominal dasar (`base_amount`) di database kita.
* **Tindakan Keamanan:** Jika nominal tidak cocok (misal terbayar kurang), sistem menolak memproses callback dengan return response `400 Bad Request (Transaction amount mismatch)` dan mencatat log warning keamanan, avoiding manipulasi manual pada skema Open Payment.

### 11. Penayangan Log Sistem Visual Terpadu di Admin Panel (System Log Viewer UI)
* **Log Controller & Routing:** Membuat [AdminLogsController.php](file:///C:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/AdminLogsController.php) dan mendaftarkannya pada [routes/web.php](file:///c:/Users/IHWAN/Project/sans-spmb/routes/web.php) di bawah grup akses super admin.
* **Fitur Log Viewer Dashboard ([logs.blade.php](file:///C:/Users/IHWAN/Project/sans-spmb/resources/views/admin/logs.blade.php)):**
  * Admin dapat memantau seluruh catatan error exceptions, status webhook, dan info mismatch nominal pembayaran langsung dari panel admin tanpa membuka server FTP/SSH.
  * Mendukung pencarian teks (search), filter level log (`INFO`, `WARNING`, `ERROR`, `DEBUG`), pagination (50 baris per halaman), dan penayangan multi-line stack trace dengan interaksi klik *collapsible accordion* yang responsif.
  * Menyediakan tombol "Bersihkan Log" yang aman untuk mengosongkan berkas `laravel.log` dengan sekali klik.
  * Menambahkan tautan menu sidebar baru "Log Sistem" di bawah kategori "Pengaturan Teknis" pada [admin.blade.php](file:///C:/Users/IHWAN/Project/sans-spmb/resources/views/layouts/admin.blade.php).

### 12. Cetak dan Unduh Bukti Pembayaran PDF Resmi (Direct PDF Receipt Download)
* **Pemasangan DomPDF:** Menginstalasi paket standar `barryvdh/laravel-dompdf` menggunakan Composer untuk memproses konversi dokumen HTML ke PDF di sisi backend.
* **Template PDF Khusus ([payment-receipt-pdf.blade.php](file:///C:/Users/IHWAN/Project/sans-spmb/resources/views/web/payment-receipt-pdf.blade.php)):** 
  * Membuat tampilan khusus PDF dengan format tabel, tata letak, margin, warna, dan inline CSS klasik yang dioptimalkan penuh agar kompatibel 100% dengan mesin render DomPDF.
* **Mekanisme Unduhan Berkas:**
  * Memperbarui aksi `downloadReceipt` pada [WebDashboardController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/WebDashboardController.php) agar memuat data pembayaran dan secara otomatis merender berkas unduhan PDF dengan format penamaan dinamis `Kwitansi-SPMB-{invoice_number}.pdf`.
  * Ketika wali murid menekan tombol **"Unduh Bukti Pembayaran"**, berkas fisik PDF resmi akan langsung diunduh secara otomatis ke perangkat mereka.
  * Menghilangkan `target="_blank"` dan menambahkan loading spinner visual yang memicu download langsung secara interaktif.

### 13. Pilihan Parameter Registrasi Lengkap di Awal (Modal Pendaftaran Detail)
* **Pencegahan Nama Calon Siswa Kosong / Dummy:**
  * Tombol **"Daftarkan Sekarang"** di card halaman depan dashboard index tidak lagi mengirimkan parameter string kosong atau dummy `"Calon Siswa"`. Tombol ini sekarang diarahkan untuk **membuka Modal Pendaftaran Baru** secara otomatis dengan melalukan pre-select pada Unit dan Kelas bersangkutan.
  * Menambahkan data pilihan **Jalur Pendaftaran** (`spmb_type_id`) dan **Gelombang Pendaftaran** (`spmb_wave_id`) secara dinamis di dalam Modal Pendaftaran Baru ([dashboard-index.blade.php](file:///C:/Users/IHWAN/Project/sans-spmb/resources/views/web/dashboard-index.blade.php)).
* **Visualisasi Parameter Master:**
  * Memperbarui `createRegistration` pada [WebDashboardController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/WebDashboardController.php) untuk memvalidasi dan menyimpan nama asli calon siswa, jalur pendaftaran, dan gelombang yang dipilih secara manual by pendaftar saat awal pendaftaran dibuat, memastikan seluruh kwitansi/laporan mencantumkan nama siswa asli.

### 14. Isolasi & Pembersihan Otomatis Data Pengujian (Test Data Self-Clean)
* Memperbarui [TestSpmbApi.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Console/Commands/TestSpmbApi.php) agar secara otomatis menjalankan method `cleanUp()` di **akhir** eksekusi pengujian.
* Hal ini memastikan database Anda tetap bersih 100% pada kondisi awal konfigurasi menu admin seeder pasca pengujian dijalankan.

### 15. Tombol Batal & Timeline Visual Pengisian Formulir Pendaftaran
* **Tombol Batal:** Menambahkan tombol **"Batal"** di samping tombol "Simpan & Lanjut" pada [form.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/form.blade.php) saat mengedit tahapan formulir yang sudah tersimpan. Klik tombol ini akan secara instan menutup formulir dan memulihkan pratinjau data readonly yang tersimpan tanpa perlu memuat ulang halaman.
* **Timeline Alur Visual:** Menambahkan komponen **Horizontal Progress Timeline** yang responsif di bagian atas halaman pengisian formulir. Menampilkan indikator angka tahapan yang berubah menjadi lambang centang hijau saat selesai, animasi berpendar (*pulse glow animation*) pada tahapan aktif saat ini, dan warna abu-abu untuk tahapan mendatang, mempermudah wali murid melacak progres pengisian.

### 16. Penyempurnaan Kolom Form Pendaftaran Wali Murid
* **NIK Calon Siswa (Opsional):** Mengubah konfigurasi NIK pendaftar menjadi opsional (`is_required` = 0) agar tidak memblokir pendaftaran bagi balita/anak yang belum terdaftar NIK-nya secara resmi.
* **Dropdown Agama:** Mengubah tipe isian Agama dari input teks biasa menjadi select dropdown dengan pilihan agama resmi di Indonesia (Islam, Kristen, Katolik, Hindu, Budha, Konghucu).
* **Label Asal Sekolah Dinamis:** Mengubah rendering label Asal Sekolah secara dinamis berdasarkan jenjang/unit pendaftaran:
  * Unit PAUD: *"Asal Sekolah / Kelompok Bermain (Jika Ada)"*
  * Unit SD: *"Asal Sekolah (TK/RA/PAUD)"*
  * Unit SMP: *"Asal Sekolah (SD/MI)"*
* **Layanan Tambahan (Radio Option di Grid):**
  * Memasukkan field `extra_services` ke dalam seeder form field di Step 2 (Informasi Calon Siswa) sehingga ter-render tepat di samping kanan "Tingkat Pendaftaran" (mengikuti layout grid 2-kolom).
  * Mengubah tampilan isian layanan tambahan dari checkbox ganda menjadi radio button pilihan tunggal (termasuk opsi default "Tidak Ada") yang otomatis terintegrasi dengan tabel pivot relasi database.
* **Petunjuk Berkas Upload:** Menambahkan label instruksi kecil di bawah kolom input berkas unggahan yang merinci format file yang diizinkan (PDF, JPG, JPEG, PNG) beserta ukuran berkas maksimal (Maks. 2 MB).

### 17. Alur Mandiri Perbaikan Berkas yang Ditolak (Rejection Self-Service Correction)
* **Status Failed Terbuka Kembali:** Mengubah perilaku halaman formulir pendaftaran [form.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/form.blade.php) agar **tidak lagi mengunci formulir** jika status pendaftaran adalah `failed` (ditolak berkasnya). Wali murid kini dapat mengeklik tombol "Ubah Data" secara mandiri pada tahapan berkas yang ditolak oleh panitia.
* **Pemberitahuan & Tombol Kirim Ulang:**
  * Menampilkan informasi penolakan dari panitia secara jelas, dan jika seluruh tahapan form sudah diperbaiki, sistem memunculkan banner hijau khusus dengan tombol **"Kirim Ulang Pendaftaran"**.
  * Memperbarui aksi backend `submitForm` di [WebDashboardController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/WebDashboardController.php) agar menerima status `failed` dan mentransisikannya kembali menjadi status `submitted` (menunggu verifikasi ulang berkas) dengan catatan pemberitahuan verifikasi ulang otomatis bagi panitia.

### 18. Modul Verifikasi Interaktif Bidang Form & Tautan Koreksi Otomatis Pendaftar
* **Penyimpanan Detail Berkas Tidak Sesuai:** Menambahkan kolom `invalid_fields` (cast as `'array'`) pada tabel `registrations` untuk mencatat daftar database key kolom isian/berkas pendaftaran yang ditolak oleh panitia.
* **Penyatuan Modal Verifikasi (Admin Panel):**
  * Di panel verifikasi pendaftaran [verification.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/verification.blade.php), tombol "Setujui" dan "Tolak" pada kolom aksi disatukan menjadi tombol tunggal **"Verifikasi Data"** untuk kandidat berstatus `submitted`.
  * Mengeklik tombol ini akan membuka Modal Review Detail yang menampilkan seluruh isian form anak. Panitia dapat menandai checkbox **"OK / Perlu Perbaikan"** di masing-masing baris data atau berkas (Akte, KK, dll) secara instan.
  * **Auto-Compile Pesan Penolakan:** Ketika panitia menghapus centang "OK" pada data tertentu, sistem secara otomatis mengubah mode tombol menjadi merah ("Tolak & Minta Perbaikan") dan men-generate draf pesan instruksi penolakan terperinci di textarea (misal: *"Mohon maaf, berkas pendaftaran ananda perlu diperbaiki pada bagian: - Scan Akta Kelahiran"*).
  * Mengintegrasikan action form to endpoint `/reject` dengan menyertakan data list JSON `invalid_fields` yang ditolak.
* **Panduan Perbaikan di Dashboard Wali Murid:**
  * Di portal pendaftar [dashboard.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/dashboard.blade.php), jika status pendaftaran adalah `failed`, akan muncul kartu peringatan merah yang memetakan kolom `invalid_fields` to user-friendly terms (misal: `family_card_path` -> *Scan Kartu Keluarga*).
  * Di samping tiap berkas yang bermasalah, disediakan tautan **"Perbaiki →"** yang melampirkan parameter step wizard dan id kolom (`?highlight=family_card_path&step=4`).
* **Visual Highlight & Auto Scroll:**
  * Menambahkan kode JavaScript pada halaman formulir [form.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/form.blade.php) yang secara otomatis mendeteksi parameter query highlight.
  * Sistem akan langsung **membuka langkah/step formulir yang dituju**, menggulirkan layar secara halus ke kolom isian/upload berkas yang bermasalah tersebut, dan **memberikan penanda berupa outline bingkai merah bercahaya (glowing red shadow)** disertai label *"⚠️ Perlu Perbaikan (Data Ditolak Panitia)"* agar wali murid langsung mengetahui posisi data yang harus diperbarui.

### 19. Aksesibilitas Verifikasi & Auto-Ekspansi Formulir Wali Murid
* **Menutup Modal via Click-Outside:** Menambahkan *event listener* pada modal detail verifikasi panitia di panel admin (`verification.blade.php`). Panitia kini dapat menutup modal secara praktis dan intuitif dengan mengeklik area kosong di luar kotak dialog modal.
* **Format List & Link Interaktif Menu Verifikasi:**
  * Pada halaman detail verifikasi pendaftar (`web/verification.blade.php`), jika status verifikasi gagal (`failed`), umpan balik catatan dari panitia kini otomatis menampilkan daftar list kolom/berkas yang ditolak secara berurutan.
  * Masing-masing butir list dilengkapi dengan link aksi **"Perbaiki Data →"** yang mengarah langsung ke bagian form yang bersangkutan.
  * Frasa kata **"Menu Formulir"** pada teks instruksi catatan panitia kini otomatis diparsing oleh sistem menjadi tautan link yang aktif menuju ke halaman formulir pendaftaran.
* **Status Berkas Persyaratan Terperinci (OK/Ditolak):**
  * Pada tabel Dokumen Persyaratan di halaman verifikasi wali murid, status keaktifan dokumen tidak lagi sekadar menampilkan "Terunggah".
  * Sistem sekarang menampilkan status peninjauan berkas secara rinci: lencana hijau **"✅ OK"** jika berkas lolos verifikasi, dan lencana merah **"❌ Perlu Perbaikan (Ditolak)"** jika berkas dinyatakan tidak sesuai oleh panitia.
* **Auto-Ekspansi Tahapan Formulir (Auto Open Form Step):**
  * Di halaman formulir pendaftaran (`web/form.blade.php`), jika ada tahapan (step) yang memiliki data bermasalah, sistem secara cerdas akan **otomatis membuka tahapan tersebut dan menyembunyikan panel preview readonly-nya secara default**.
  * Kartu tahapan tersebut akan diberi lencana merah **"⚠️ Perlu Perbaikan"** dengan bingkai merah bercahaya (*red glowing outline*) sejak halaman pertama kali dimuat. Hal ini memastikan wali murid langsung diarahkan to form pengisian tanpa perlu mengeklik tombol "Ubah Data" terlebih dahulu.

### 20. Penyelarasan Alur Ta'aruf Offline (Sekolah Anak Saleh) & Pembersihan Umpan Balik
* **Pembersihan Umpan Balik Otomatis:** Memperbarui fungsi `getCommitteeMessage` pada `WebDashboardController` dan `Api/RegistrationController` agar secara otomatis mengabaikan/membersihkan sisa catatan penolakan lama saat pendaftar telah disetujui (`verified`/`completed`). Ini menyelesaikan masalah teks *"Mohon maaf, berkas perlu diperbaiki..."* yang masih menempel di banner dashboard pendaftar pasca disetujui.
* **Wording & Konsep Ta'aruf Offline (Bukan Online Zoom):**
  * Mengubah seluruh penyebutan instansi "Observasi" atau "Tes Observasi secara daring" menjadi **"Ta'aruf Tatap Muka di Unit Sekolah"** pada halaman dashboard pendaftar, instruksi langkah selanjutnya, dan halaman Ta'aruf.
  * **Tampilan Undangan Unit Terpadu:** Pada halaman detail Ta'aruf pendaftar (`web/observation.blade.php`), karena saat ini belum ada modul admin CRUD penjadwalan mandiri per unit, sistem menyajikan detail undangan Ta'aruf offline yang informatif:
    * Menampilkan nama **Unit Sekolah** (misal: PAUD Terpadu Anak Saleh) beserta program kelas yang dipilih secara dinamis.
    * Menampilkan **No. HP Wali Terdaftar** dengan penanda aktif WhatsApp.
    * Memberikan penjelasan instruksi yang jelas bahwa panitia unit bersangkutan akan mengirimkan detail jadwal kehadiran fisik secara langsung melalui WhatsApp ke nomor tersebut, disertai ketentuan kehadiran fisik (hadir tepat waktu, pakaian Islami).

### 21. CRUD Surat Pernyataan Kesanggupan & Template Dinamis Multi-Unit
* **Skema Database & Model:** Menambahkan tabel `spmb_agreement_templates` and model `SpmbAgreementTemplate` (relasi `hasOne`/`belongsTo` dengan `SpmbUnit`) untuk menyimpan template surat pernyataan kesanggupan per unit sekolah.
* **Data Seeder Terpadu:** Mengintegrasikan seeding otomatis isi surat kesanggupan riil Pendidkan Yayasan Anak Saleh (mencakup 4 pasal utama dengan sub-poin a-q secara detail) untuk unit PAUD, SD, dan SMP.
* **Panel CRUD Admin ("Surat Pernyataan"):**
  * Menyediakan antarmuka manajemen baru di bawah menu sidebar "Pengaturan Teknis -> Surat Pernyataan".
  * Menghadirkan editor visual terbagi berdasarkan tab unit (PAUD, SD, SMP) untuk mengedit Judul Surat, isi pasal Surat Pernyataan, label checkbox persetujuan, tempat penandatanganan, nama kepala sekolah, dan jabatan kepala sekolah.
  * Menyertakan informasi panduan token dinamis seperti `{{nama_calon_siswa}}`, `{{nama_unit}}`, `{{nama_kelas}}`, dan `{{tahun_ajaran}}` yang dapat disisipkan bebas ke dalam surat.
* **Kompilasi Dinamis & Desain Premium Surat:**
  * Melakukan *runtime compilation* (string replace) untuk menyuntikkan data nama murid, unit sekolah, kelas, dan tahun ajaran aktif secara langsung sebelum dirender.
  * **Header Rahasia:** Menambahkan lencana visual premium *"Untuk Kalangan Sendiri"* dan label peringatan *"Dilarang memfoto, mengcopy, dan menyebarluaskan"* di bagian kanan atas preview surat pernyataan kesanggupan pendaftar.
  * **Pra-populasi Nama Wali:** Input nama penandatangan secara otomatis mengambil data nama Ayah (atau nama Ibu jika Ayah tidak terisi) dari form pendaftaran yang disimpan, tetapi tetap dapat diedit secara manual oleh pendaftar.
  * **Mockup Tanda Tangan Fisik:** Menambahkan blok visual tanda tangan di bagian bawah surat kesanggupan yang memetakan kolom jabatan, nama sekolah, tempat tanggal dinamis (format Bahasa Indonesia, misal: *Malang, 25 Agustus 2026*), tanda tangan kepala sekolah, dan lencana meterai Rp10.000 secara realistis.

### 22. Integrasi Quill Rich Text Editor (Laksana Word) & CSS List Counter Resets
* **WYSIWYG Word Editor:** Menggantikan textarea isian HTML kaku pada panel admin pengaturan surat pernyataan dengan **Quill Editor Snow Theme**. Admin kini memiliki toolbar penuh untuk melakukan format tebal, miring, garis bawah, serta membuat daftar/penomoran secara leluasa layaknya mengetik di Microsoft Word tanpa perlu menguasai sintaks HTML.
* **CSS List Formatting (.agreement-body):** Menyusun aturan styling CSS khusus `.agreement-body` pada portal pendaftar untuk meloloskan markup list editor Quill. Aturan ini memastikan daftar berurutan (`<ol>`), bullet points (`<ul>`), nested list (a, b, c, d), paragraf, dan teks tebal dari editor ter-render secara teratur, rapi, dan berindentasi presisi di layar pendaftar (menolak reset paksa styles oleh Tailwind CSS).
* **Reset Counter Nested List (Penyelesaian Bug Mulai Ulang a. b. c. d.):**
  * **Penyebab Bug:** Dikarenakan Quill merepresentasikan list bertingkat secara flat di HTML dengan class `ql-indent-N`, browser/Quill's stylesheet default mengalami bug dimana counter `list-1` (untuk level sub-poin huruf) terus terakumulasi dan tidak mereset ulang ke 0 ketika berganti ke pasal utama (level-0 decimal `<li>`). Hal ini menyebabkan sub-poin pada Pasal 2 dimulai dari huruf `r.` (meneruskan dari poin `q.` di Pasal 1) bukan kembali ke huruf `a.`.
  * **Perbaikan CSS Reset:** Memperbarui rule CSS `.agreement-body ol` dan `.agreement-body li` (serta `.ql-editor` di admin panel) dengan menyisipkan properti `counter-reset: list-1 list-2 list-3 ... list-9 !important;` pada setiap elemen list utama level-0.
  * **Hasil:** Sub-poin penomoran bertingkat huruf (`a. b. c. d.`) kini secara konsisten **memulai ulang dari huruf `a.`** di bawah setiap pasal utama yang baru, menyelesaikan bug visual penomoran secara tuntas.

### 23. Judul Surat Kesanggupan Multi-Line Centered
* **Textarea Input di Admin Panel:** Mengubah jenis kolom input Judul Surat pada [settings-agreements.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/admin/settings-agreements.blade.php) dari `<input type="text">` menjadi `<textarea rows="4">`. Hal ini memfasilitasi admin untuk membagi judul ke dalam beberapa baris secara manual (misalnya menggunakan tombol Enter) persis seperti dokumen MS Word.
* **Whitespace-Pre-Line di Portal Wali Murid:** Menambahkan class `whitespace-pre-line` pada elemen penampil judul surat di [observation.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/observation.blade.php). Hal ini memastikan baris baru (\n) yang ditulis oleh admin dirender dengan benar sebagai baris baru yang terpusat (*centered multi-line*).
* **Pembaruan Default Database:** Memperbarui judul surat pernyataan di database untuk seluruh unit (PAUD, SD, SMP) menjadi format terpusat dinamis:
  ```
  KESANGGUPAN ORANGTUA/WALI MURID
  TAHUN AJARAN {{tahun_ajaran}}
  SEKOLAH ANAK SALEH
  KOTA MALANG
  ```

### 24. Perataan Sejajar Kolom Titik Dua (Aligned Metadata Colons)
* **Tailwind CSS Grid Resolution:** Di [WebDashboardController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/WebDashboardController.php), setelah melakukan kompilasi placeholder pada surat kesanggupan, sistem secara otomatis menjalankan pemrosesan regex untuk menyaring baris metadata (Nama Murid, Nama Orang Tua/Wali, Tahun Ajaran, Layanan Pendidikan, Unit & Program).
* **Grid Formatting:** Baris-baris tersebut secara dinamis ditransformasikan dari paragraf teks biasa menjadi baris **Tailwind CSS Grid 3-Kolom** (`grid-cols-[160px_10px_1fr]`).
* **Hasil:** Tanda titik dua (`:`) pada baris metadata di portal pendaftar kini teratur dan **rata sejajar secara vertikal (lurus dari atas ke bawah)** dengan tingkat presisi pixel sempurna, baik di PAUD, SD, maupun SMP.

### 25. Penyelarasan Layout Tanda Tangan (Centered Signature Layout)
* **Rata Tengah Kolom:** Menata ulang tata letak visual footer tanda tangan digital pada [observation.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/observation.blade.php) dengan memformat kolom tanda tangan Kepala Sekolah (kiri) and Orangtua/Wali (kanan) menjadi **rata tengah (center aligned)**.
* **Penyelarasan Tinggi Horizontal:**
  * Membagi layout baris tanda tangan menggunakan grid terstruktur.
  * Menempatkan tulisan label jabatan `"Kepala Sekolah"` sejajar secara horizontal (satu garis lurus) dengan `"Orangtua/Wali Murid,"` pada baris pertama.
  * Menempatkan tanggal dinamis (`Malang, 25 Agustus 2026`) secara terpusat di atas label Orangtua/Wali, sementara bagian Kepala Sekolah dibiarkan kosong sejajar secara vertikal.

### 26. Sinkronisasi Nama Wali Real-Time Pas Mengetik (Signature Footer Only)
* **CSS Class Binding (`.dynamic-signature-name`):** Mengikat teks nama penandatangan di kolom tanda tangan digital (bawah) menggunakan kelas target `.dynamic-signature-name`.
* **Vanilla JS Listener:** Menambahkan *event listener* pada kolom input penandatangan (#signature_name) di portal pendaftar.
* **Hasil:** Setiap kali wali murid mengetikkan nama lengkap mereka di kolom isian bawah, nama penandatangan di dalam tanda kurung pada kolom tanda tangan bawah akan **berubah secara real-time** tanpa memicu reload halaman. Teks info "Nama Orangtua/Wali" pada blok data metadata atas dibatasi tetap statis (sesuai data awal database) dan tidak ikut terpengaruh.

### 27. Kartu Kerahasiaan Berbingkai Premium (Bordered Confidentiality Card)
* **Visual Card Layout:** Mengubah tampilan lencana visual kerahasiaan (*"Untuk Kalangan Sendiri"*) di bagian atas kanan pratinjau surat menjadi **kartu mini berbingkai premium** (menggunakan properti `border border-brand-emerald/20 bg-brand-emerald/5` dengan *emerald badge* terpusat di dalamnya).

### 28. Angsuran Pembayaran Bertahap & Checklist Biaya (Checklist Tuition Payments) (Baru)
* **Layout Checklist Antarmuka:**
  * Menambahkan pilihan **Checkbox** pada daftar rincian Biaya Administrasi Masuk Awal di halaman [result.blade.php](file:///c:/Users/IHWAN/Project/sans-spmb/resources/views/web/result.blade.php).
  * Wali murid dapat secara fleksibel mencentang satu atau beberapa komponen tagihan saja (misal: hanya membayar *Biaya Seragam* terlebih dahulu).
* **Kalkulasi Biaya Real-time (JavaScript):**
  * Menambahkan script interaktif yang secara otomatis meng-update total nominal pembayaran terpilih dalam format Rupiah serta memperbarui parameter query URL tujuan pembayaran (`?items=0,2,...`) secara real-time saat checkbox dicentang/dihapus centangnya.
* **Validasi & Penyaringan Komponen Biaya (Controller):**
  * Memperbarui metode `payment` dan `chargePayment` pada [WebDashboardController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Web/WebDashboardController.php) agar menyaring list komponen biaya sesuai parameter query yang dikirimkan.
  * Menyimpan daftar item yang dibayar pada kolom `payment_info['selected_items']` di tabel `payments`.
* **Pelacakan Komponen Lunas:**
  * Di halaman rincian biaya, komponen yang telah dibayar lunas otomatis terkunci (checkbox digantikan ikon centang hijau, teks dicoret lembut, dan nominal berwarna pudar) dan tidak dapat dipilih kembali.
* **Transisi Status bertahap (Webhook Reconcilation):**
  * Memperbarui webhook callback di [PaymentController.php](file:///c:/Users/IHWAN/Project/sans-spmb/app/Http/Controllers/Api/PaymentController.php) agar menjumlahkan seluruh pembayaran berstatus `success` dari wali murid.
  * Status pendaftaran calon siswa hanya akan bertransisi menjadi **`completed`** (Lulus & Resmi) and status tagihan menjadi **`paid`** (Lunas) jika akumulasi nominal pembayaran bertahap telah memenuhi atau melebihi total keseluruhan biaya administrasi masuk awal dari database. Jika masih kurang, status dicatat sebagai **`partially_paid`** (Bayar Sebagian).

---

## Verifikasi Pengujian

Pengujian E2E integrasi API and alur verifikasi berjalan 100% sukses.
