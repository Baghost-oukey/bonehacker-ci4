# Fitur Riwayat Perubahan Data Pasien

## Deskripsi
Fitur ini mencatat setiap perubahan data pasien untuk keperluan audit dan investigasi bug. Setiap kali data pasien diupdate, sistem akan otomatis mencatat:
- Field apa yang berubah
- Nilai lama dan nilai baru
- Siapa yang mengubah
- Kapan diubah
- IP address dan user agent

## Komponen yang Dibuat

### 1. Database Migration
**File:** `app/Database/Migrations/2026-05-16-100000_PatientHistoryTable.php`

**Tabel:** `patient_history`
- `id` - Primary key
- `patient_id` - ID pasien (foreign key ke `patients.id`)
- `field_name` - Nama field yang berubah
- `old_value` - Nilai lama
- `new_value` - Nilai baru
- `changed_by` - User ID yang mengubah (foreign key ke `users.id`)
- `changed_at` - Timestamp perubahan
- `ip_address` - IP address user
- `user_agent` - Browser/device info

**Index:**
- Primary key pada `id`
- Index pada `patient_id` (untuk query cepat)
- Index pada `changed_at` (untuk sorting)

### 2. Model
**File:** `app/modules/patients/Models/MPatientHistory.php`

**Methods:**
- `getPatientHistory($patientId, $limit = null)` - Ambil history untuk 1 pasien
- `getGroupedHistory($patientId)` - Ambil history yang dikelompokkan per sesi edit
- `logChange($patientId, $fieldName, $oldValue, $newValue, $userId)` - Log 1 perubahan
- `logMultipleChanges($patientId, $changes, $userId)` - Log multiple perubahan sekaligus

### 3. Controller Update
**File:** `app/modules/patients/Controllers/Patients.php`

**Perubahan:**
- Method `update()` - Ditambahkan logic untuk auto-log perubahan
- Method `getHistory($id)` - Endpoint baru untuk fetch history via AJAX

**Field yang di-track:**
- Nama
- Jenis Kelamin
- Umur
- No. HP
- Alamat
- Wilayah
- Negara
- Status Rentan
- Domestik
- Informasi Pasien
- Keterangan Rentan

### 4. View Component
**File:** `app/modules/patients/Views/component/card_history_changes.php`

**Fitur:**
- Timeline view dengan design modern
- Menampilkan perubahan dikelompokkan per sesi edit
- Menampilkan: tanggal, user, IP address, field yang berubah
- Auto-load saat halaman dibuka
- Tombol refresh untuk reload data
- Empty state jika belum ada history
- Loading state saat fetch data

### 5. Routes
**File:** `app/Config/Routes.php`

**Route baru:**
```php
$routes->get('patient/get-history/(:num)', '\App\Modules\patients\Controllers\Patients::getHistory/$1');
```

## Cara Kerja

### 1. Saat Update Pasien
Ketika user mengupdate data pasien melalui form:
1. Controller mengambil data lama dari database
2. Membandingkan dengan data baru dari form
3. Untuk setiap field yang berubah, catat ke tabel `patient_history`
4. Semua perubahan dalam 1 sesi edit dicatat dengan timestamp yang sama

### 2. Menampilkan History
Di halaman profil pasien (`patient/show/{id}`):
1. Card "Riwayat Perubahan Data" ditampilkan setelah card File
2. JavaScript auto-load history via AJAX
3. History ditampilkan dalam format timeline
4. Perubahan dikelompokkan per sesi edit

## Estimasi Storage

**Per 1 edit pasien:**
- Rata-rata 5 field berubah = 5 rows
- 1 row ≈ 200 bytes
- Total: ~1 KB per edit

**Proyeksi:**
- 100 edit/hari = ~100 KB/hari = ~3 MB/bulan = ~36 MB/tahun
- 1000 edit/hari = ~1 MB/hari = ~30 MB/bulan = ~360 MB/tahun

**Kesimpulan:** Tidak akan membengkak, sangat ringan!

## Manfaat

### 1. Investigasi Bug
Ketika terjadi bug seperti "data pasien 7719 menimpa pasien 120":
- Bisa lihat kapan data berubah
- Siapa yang mengubah (atau apakah otomatis)
- Data apa yang berubah
- Dari nilai apa ke nilai apa

### 2. Audit Trail
- Jejak lengkap semua perubahan data
- Compliance untuk data sensitif
- Accountability untuk setiap perubahan

### 3. Recovery
- Bisa lihat nilai lama jika perlu restore
- Bisa trace back perubahan yang salah

### 4. Monitoring
- Deteksi perubahan yang mencurigakan
- Identifikasi pattern bug

## Testing

### Manual Test
1. Buka halaman pasien: `http://bonehacker-ci4.test/patient/show/120`
2. Edit data pasien (misal: ubah nama, HP, alamat)
3. Save
4. Scroll ke card "Riwayat Perubahan Data"
5. Lihat apakah perubahan tercatat

### Expected Result
- Timeline menampilkan perubahan terbaru
- Menampilkan field yang berubah dengan nilai lama → nilai baru
- Menampilkan nama user yang edit
- Menampilkan tanggal dan waktu
- Menampilkan IP address

## Troubleshooting

### History tidak muncul
1. Cek apakah migration sudah dijalankan: `php spark migrate`
2. Cek apakah tabel `patient_history` ada di database
3. Cek console browser untuk error JavaScript
4. Cek route sudah benar: `patient/get-history/{id}`

### History tidak tercatat saat update
1. Cek apakah ada error di log CodeIgniter
2. Pastikan model `MPatientHistory` ter-load dengan benar
3. Cek apakah ada perubahan data (jika tidak ada perubahan, tidak akan tercatat)

### Performance lambat
1. Pastikan index pada `patient_id` dan `changed_at` sudah ada
2. Jika data sudah sangat banyak (>1 juta rows), pertimbangkan archiving
3. Tambahkan pagination jika perlu

## Future Improvements (Opsional)

1. **Filter by field** - User bisa filter hanya lihat perubahan field tertentu
2. **Export history** - Export ke Excel/PDF
3. **Restore data** - Fitur untuk restore ke nilai lama
4. **Notification** - Notif jika ada perubahan data penting
5. **Archiving** - Auto-archive history > 1 tahun ke file
6. **Comparison view** - Side-by-side comparison nilai lama vs baru
7. **Bulk changes detection** - Deteksi jika ada perubahan massal yang mencurigakan

## Maintenance

### Cleanup (Jika Diperlukan)
Jika ingin hapus history lama (misal > 1 tahun):
```sql
DELETE FROM patient_history 
WHERE changed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Monitoring Size
Cek ukuran tabel:
```sql
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'your_database_name'
AND table_name = 'patient_history';
```
