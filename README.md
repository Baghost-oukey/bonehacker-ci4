# Bonehacker CI4 - Aplikasi Manajemen Kesehatan

Bonehacker CI4 adalah aplikasi web berbasis CodeIgniter 4 yang dirancang untuk manajemen kesehatan, termasuk pengelolaan pasien, antrian, terapis, dan berbagai fitur analitik kesehatan. Aplikasi ini menggunakan arsitektur modular untuk kemudahan pengembangan dan pemeliharaan.

## Persyaratan Sistem

Sebelum menginstal aplikasi ini, pastikan sistem Anda memenuhi persyaratan berikut:

- **PHP**: Versi 8.1 atau lebih tinggi
- **Database**: MySQL atau MariaDB
- **Web Server**: Apache atau Nginx dengan dukungan mod_rewrite
- **Composer**: Untuk manajemen dependensi PHP
- **Git**: Untuk cloning repository

### Ekstensi PHP yang Diperlukan:

**PENTING**: Pastikan ekstensi `intl` sudah aktif/tercentang di konfigurasi PHP Anda. Ekstensi ini sangat penting untuk aplikasi ini.

Ekstensi lainnya yang direkomendasikan:
- `mbstring` (untuk dukungan string multibyte)
- `json` (biasanya sudah aktif secara default di PHP modern)
- `mysqlnd` (untuk koneksi MySQL)
- `libcurl` (untuk HTTP requests)

**Cara mengecek ekstensi PHP**:
- Di XAMPP: Buka phpMyAdmin → PHP Info
- Di Laragon: Klik kanan ikon Laragon → PHP → phpinfo()
- Atau buat file `phpinfo.php` dengan isi `<?php phpinfo(); ?>` dan akses via browser

## Instalasi

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi Bonehacker CI4. Kami akan menjelaskan setiap langkah dengan detail agar mudah diikuti bahkan oleh pemula.

### Persiapan Awal

Sebelum memulai instalasi, pastikan Anda memiliki:
- **Git**: Alat untuk mengunduh kode dari GitHub. Download dari [git-scm.com](https://git-scm.com/)
- **Composer**: Manajer paket PHP. Download dari [getcomposer.org](https://getcomposer.org/)
- **PHP 8.1+**: Pastikan PHP terinstall dan dapat diakses via command line
- **MySQL/MariaDB**: Database server untuk menyimpan data
- **Web Server**: Apache/Nginx atau XAMPP/Laragon untuk menjalankan aplikasi

### 1. Mengunduh (Clone) Kode Aplikasi

Buka Command Prompt/Terminal di komputer Anda, lalu jalankan perintah berikut:

```bash
# Pindah ke folder tempat Anda ingin menyimpan aplikasi
# Contoh: cd C:\xampp\htdocs (untuk XAMPP) atau cd C:\laragon\www (untuk Laragon)

# Unduh aplikasi dari GitHub
git clone https://github.com/Baghost-oukey/bonehacker-ci4.git

# Masuk ke folder aplikasi yang baru diunduh
cd bonehacker-ci4
```

**Penjelasan**: Perintah `git clone` akan mengunduh seluruh kode aplikasi dari GitHub ke komputer Anda. Folder `bonehacker-ci4` akan dibuat otomatis.

### 2. Menginstall Dependensi (Paket Tambahan)

Setelah kode terunduh, install paket-paket PHP yang diperlukan:

```bash
# Pastikan Anda berada di folder bonehacker-ci4
composer install
```

**Penjelasan**: Composer akan mengunduh dan menginstall semua library yang dibutuhkan aplikasi, seperti framework CodeIgniter, library untuk membuat PDF, QR code, dll. Proses ini mungkin memakan waktu beberapa menit tergantung koneksi internet.

**Tips untuk Pemula**:
- Jika muncul error "composer not found", pastikan Composer sudah terinstall dan PATH sudah dikonfigurasi
- Jalankan command prompt sebagai Administrator jika di Windows

### 3. Mengatur Konfigurasi Aplikasi

1. **Salin file konfigurasi**:
   ```bash
   # Salin file env menjadi .env
   cp env .env
   ```
   **Penjelasan**: File `.env` berisi pengaturan aplikasi seperti database dan URL. Kita salin dari `env` agar tidak mengubah file asli.

2. **Edit file .env**:
   Buka file `.env` dengan text editor (Notepad++, VS Code, dll.) dan ubah bagian berikut:

   ```env
   # Cari dan ubah baris berikut:
   app.baseURL = 'http://localhost/bonehacker-ci4/public'

   # Bagian database - sesuaikan dengan database Anda:
   database.default.hostname = localhost
   database.default.database = bonehacker_db  # Buat database baru dengan nama ini
   database.default.username = root           # Username database Anda
   database.default.password =                # Password database (kosongkan jika tidak ada)
   database.default.DBDriver = MySQLi
   ```

   **Panduan Detail**:
   - `app.baseURL`: URL untuk mengakses aplikasi. Jika menggunakan XAMPP, biasanya `http://localhost/bonehacker-ci4/public`
   - `database.default.database`: Buat database baru di phpMyAdmin dengan nama `bonehacker_db`
   - Username dan password database: Default XAMPP biasanya `root` tanpa password

### 4. Membuat Struktur Database

Jalankan perintah untuk membuat tabel-tabel database:

```bash
# Jalankan migrasi database
php spark migrate
```

**Penjelasan**: Perintah ini akan membuat semua tabel yang diperlukan seperti tabel users, patients, history, dll. berdasarkan file migrasi yang ada.

**Troubleshooting**:
- Jika error "php not found", pastikan PHP sudah ditambahkan ke PATH sistem
- Jika error database connection, periksa kembali pengaturan di file `.env`

### 5. Mengisi Data Awal

Isi database dengan data contoh:

```bash
# Isi semua data awal sekaligus
php spark db:seed
```

Atau isi data tertentu satu per satu:

```bash
# Data pengguna
php spark db:seed UsersSeeder

# Data negara
php spark db:seed CountriesSeeder

# Data wilayah
php spark db:seed RegionSeeder

# Data jabatan
php spark db:seed JabatanSeeder

# Tag untuk riwayat medis
php spark db:seed MedhisTagsSeeder

# Tag untuk hasil pemeriksaan
php spark db:seed ResultsTagsSeeder

# Tag untuk komplain
php spark db:seed ComplaintTag
```

**Penjelasan**: Seeder akan mengisi database dengan data awal seperti daftar negara, jabatan, dll. agar aplikasi bisa berjalan dengan data contoh.

### 6. Mengimpor Data Pasien dan Riwayat Medis

Jika Anda memiliki data pasien dan riwayat medis dari sistem lama, import menggunakan perintah khusus:

```bash
# Import data pasien
php spark data:import-patient

# Import data riwayat medis
php spark data:import-history
```

**Penjelasan**: Command ini akan mengimpor data dari file sumber yang sudah dikonfigurasi. Pastikan file data sudah ditempatkan di lokasi yang benar sebelum menjalankan.

### 7. Mengatur Web Server

**Untuk XAMPP**:
1. Jalankan XAMPP Control Panel
2. Start Apache dan MySQL
3. Buka browser dan akses: `http://localhost/bonehacker-ci4/public`

**Untuk Laragon**:
1. Jalankan Laragon
2. Klik "Start All"
3. Aplikasi akan otomatis terdeteksi dan dapat diakses

**Untuk Server Lain**:
- Pastikan document root mengarah ke folder `public/`
- Aktifkan mod_rewrite untuk Apache

### 8. Mengakses Aplikasi

Buka browser dan kunjungi URL yang sudah dikonfigurasi di `app.baseURL`. Anda akan melihat halaman login aplikasi Bonehacker CI4.

**Akun Default** (jika sudah di-seed):

***Sebagai Admin**
- Username: admin
- Password: admin123

***Sebagai Users**
- Username: users
- Password: 123456789

---

**Catatan Penting untuk Pemula**:
- Selalu backup database sebelum menjalankan migrasi
- Jika ada error, baca pesan error dengan teliti
- Pastikan semua service (Apache, MySQL) sedang berjalan
- Gunakan command prompt dengan hak Administrator di Windows
- Jika bingung, tanyakan di forum atau buat issue di GitHub

**Langkah Selanjutnya**: Setelah instalasi berhasil, Anda dapat mulai menggunakan fitur-fitur aplikasi seperti manajemen pasien, antrian, dan statistik.

```bash
# Import data pasien
php spark data:import-patient

# Import data history/riwayat medis
php spark data:import-history
```

Command ini akan memproses dan mengimpor data dari file sumber yang telah dikonfigurasi.

### 7. Konfigurasi Web Server

Pastikan web server Anda mengarah ke folder `public/` sebagai root directory. Untuk Apache, tambahkan file `.htaccess` jika belum ada.

### 8. Akses Aplikasi

Buka browser dan akses URL yang telah dikonfigurasi di `app.baseURL`. Anda akan diarahkan ke halaman login.

## Fitur Aplikasi

Bonehacker CI4 menyediakan berbagai fitur komprehensif untuk manajemen kesehatan:

### 1. Sistem Autentikasi (Auth Module)
- Login/logout pengguna
- Manajemen sesi pengguna
- Kontrol akses berbasis peran

### 2. Dashboard dan Beranda
- Tampilan utama dengan ringkasan data
- Navigasi ke berbagai modul
- Statistik dasar kesehatan

### 3. Manajemen Pasien (Patients Module)
- Pendaftaran pasien baru
- Pencarian dan filter pasien
- Manajemen data pribadi pasien
- Riwayat kunjungan
- Validasi nomor telepon

### 4. Sistem Antrian (Antrean Module)
- Pembuatan antrian pasien
- Pemrosesan antrian
- Status antrian (menunggu, diproses, selesai)
- Manajemen daftar antrian
- Integrasi dengan data pasien

### 5. Manajemen Terapis
- Pendaftaran dan pengelolaan data terapis
- Penugasan terapis ke pasien
- Manajemen jadwal terapis

### 6. Manajemen Wilayah (Region Module)
- Hierarki wilayah (provinsi, kabupaten, kecamatan, desa)
- CRUD operasi untuk data wilayah
- Integrasi dengan data alamat pasien

### 7. Sistem Jabatan (Jabatan Module)
- Manajemen jabatan dalam organisasi kesehatan
- Pengaturan hierarki jabatan

### 8. Journal dan Laporan
- Pencatatan aktivitas medis
- Ekspor laporan ke Excel dan PDF
- Arsip dokumentasi kesehatan

### 9. Statistik dan Analitik
- **Statistik Umum**: Ringkasan data kesehatan keseluruhan
- **Statistik Berdasarkan Tag**: Analisis berdasarkan kategori medis
- **Statistik Hasil Pemeriksaan**: Analisis hasil diagnosis
- **Statistik Gender**: Distribusi data berdasarkan jenis kelamin
- **Statistik Daerah**: Analisis geografis data kesehatan

### 10. Manajemen Komplain
- Pencatatan keluhan pasien
- Kategorisasi komplain dengan tag
- Pelacakan status penyelesaian

### 11. Integrasi WhatsApp
- API WhatsApp untuk komunikasi
- Log aktivitas WhatsApp
- Notifikasi otomatis

### 12. Manajemen File dan Media
- Upload dan penyimpanan file pasien
- Manajemen foto terapis
- Penyimpanan dokumen medis

### 13. Fitur Tambahan
- **Greeting System**: Sistem salam otomatis
- **QR Code Generation**: Pembuatan kode QR untuk berbagai keperluan
- **PDF Generation**: Ekspor dokumen ke format PDF
- **Spreadsheet Export**: Ekspor data ke Excel
- **DataTables Integration**: Tampilan tabel interaktif dengan fitur pencarian dan pagination

## Teknologi yang Digunakan

- **Framework**: CodeIgniter 4
- **Template Engine**: Blade (via jenssegers/blade)
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Libraries**:
  - DomPDF & TCPDF untuk generasi PDF
  - PhpSpreadsheet untuk manipulasi Excel
  - Endroid QR Code untuk generasi QR code
  - DataTables untuk tabel interaktif

## Struktur Proyek

```
bonehacker-ci4/
├── app/
│   ├── Commands/          # Custom CLI commands
│   ├── Config/            # Configuration files
│   ├── Controllers/       # Base controllers
│   ├── Database/
│   │   ├── Migrations/    # Database migrations
│   │   └── Seeds/         # Database seeders
│   ├── Filters/           # Request filters
│   ├── Helpers/           # Custom helpers
│   ├── Libraries/         # Custom libraries
│   ├── Models/            # Database models
│   ├── modules/           # Modular features
│   └── Views/             # View templates
├── public/                # Web root directory
├── vendor/                # Composer dependencies
├── writable/              # Writable files (logs, cache, etc.)
└── tests/                 # Unit tests
```

## Penggunaan

### Menjalankan Server Development

```bash
php spark serve
```

Aplikasi akan berjalan di `http://localhost:8080`.

### Menjalankan Test

```bash
composer test
```

### Membersihkan Cache

```bash
php spark cache:clear
```

## Kontribusi

Untuk berkontribusi pada pengembangan aplikasi ini:

1. Fork repository
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## Lisensi

Aplikasi ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detail lebih lanjut.

## Dukungan

Jika Anda mengalami masalah atau memiliki pertanyaan:

- Buat issue di [GitHub Repository](https://github.com/Baghost-oukey/bonehacker-ci4/issues)
- Diskusikan di forum CodeIgniter

---

**Catatan**: Pastikan untuk selalu backup database sebelum menjalankan migrasi atau seeder dalam environment production.
