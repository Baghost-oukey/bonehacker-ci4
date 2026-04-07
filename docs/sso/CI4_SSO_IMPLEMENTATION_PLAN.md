# Rencana Implementasi SSO Nexia (CodeIgniter 4)

Dokumen ini adalah panduan adaptasi dari dokumentasi Nexia (Laravel) untuk diterapkan pada framework **CodeIgniter 4** di project Bonehacker, memastikan keselarasan penuh dengan ekosistem Nexia (seperti Gembala).

## 1. Persiapan Library
Karena CI4 tidak memiliki library bawaan seperti Laravel Passport/Socialite, kita akan menggunakan library standar industri:

```bash
composer require league/oauth2-client
```

## 2. Konfigurasi Environment (`.env`)
Tambahkan variabel berikut ke file `.env` Anda sesuai standar Nexia:

```env
# Nexia App Identity (Untuk Validasi Lisensi)
NEXIA_BASE_URL=https://erp.pustekno.id
NEXIA_APP_SLUG=bonehacker
NEXIA_APP_DOMAIN=bonehacker.id
NEXIA_CLIENT_ID=...          # Kredensial App Registry
NEXIA_CLIENT_SECRET=...      # Kredensial App Registry

# Nexia OAuth (Untuk Login SSO)
NEXIA_OAUTH_CLIENT_ID=...    # UUID dari OAuth Clients
NEXIA_OAUTH_CLIENT_SECRET=...
NEXIA_REDIRECT_URI=http://localhost:8080/auth/callback
```

## 3. Alur Keamanan Berlapis (Alignment with Nexia/Gembala)

Implementasi di Bonehacker harus mengikuti 3 lapisan pengecekan agar sinkron dengan Nexia:

### Lapisan 1: Validasi Lisensi Aplikasi (App Level)
Sebelum memperbolehkan login, Bonehacker harus memverifikasi dirinya sendiri ke Nexia.
- **Endpoint**: `GET {NEXIA_BASE_URL}/api/platform/apps/{NEXIA_APP_SLUG}/license`
- **Header**: 
    - `X-Nexia-Client-Id: {NEXIA_CLIENT_ID}`
    - `X-Nexia-Client-Secret: {NEXIA_CLIENT_SECRET}`
- **Logika**: Jika respons bukan `200 OK` (misal `403` atau `402`), aplikasi harus masuk ke mode *Restricted* atau menampilkan pesan pembatasan lisensi.

### Lapisan 2: Autentikasi OAuth (User Level)
Proses standar penukaran `code` menjadi `access_token` melalui endpoint `/oauth/token`.

### Lapisan 3: Pengecekan Status User (Service Level)
Setelah mendapatkan profil user dari `/oauth/user`, Bonehacker wajib mengecek hak akses user tersebut di Nexia.
- **Endpoint**: `POST {NEXIA_BASE_URL}/api/platform/apps/{NEXIA_APP_SLUG}/user-status`
- **Header**: (Gunakan kredensial yang sama dengan Lisensi)
- **Payload**: `{"email": "user@example.com"}`
- **Logika**: 
    - Jika `can_access == false`, batalkan proses login di Bonehacker dan tampilkan pesan kesalahan sesuai `next_action` (misal: "Akun Ditangguhkan" atau "Harap Lakukan Pembayaran").

## 4. Struktur Controller yang Diusulkan (`Auth\SsoController.php`)

### A. Method `login()`
1. Jalankan pengecekan **Lapisan 1 (Lisensi)**.
2. Jika valid, arahkan user ke URL Authorize Nexia.
3. Simpan `state` di session CI4.

### B. Method `callback()`
1. Validasi `state` dan tukar code menjadi **Access Token**.
2. Ambil profil user dari `/oauth/user`.
3. Jalankan pengecekan **Lapisan 3 (User Status)**.
4. Jika `can_access == true`:
    - Cari user di database lokal berdasarkan email.
    - Sinkronkan data profil (Nama, Avatar).
    - Login-kan user ke sistem CI4.
5. Jika gagal, arahkan kembali ke login dengan `Flash Data` pesan error.

## 5. Penyesuaian Database
Disarankan membuat tabel `user_identities` untuk mencatat metadata SSO:

```sql
CREATE TABLE user_identities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    provider VARCHAR(50) DEFAULT 'nexia',
    provider_user_id VARCHAR(255) NOT NULL, -- UUID user dari Nexia
    last_login_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## 6. Integrasi Tampilan (View Login)
Tambahkan tombol login di `app/Views/auth/login.php`:

```html
<a href="<?= base_url('auth/sso/login') ?>" class="btn btn-danger btn-block">
    <img src="/assets/img/nexia-logo.png" width="20" class="mr-2"> Login dengan Nexia
</a>
```

---
**Status:** Rancangan Final (Aligned with Nexia & Gembala)  
**Target:** Integrasi SSO Robust di Bonehacker CI4  
