# Implementasi Login SSO SmartSociety

Dokumen ini menjelaskan implementasi fitur Single Sign-On (SSO) SmartSociety yang ada pada project `nexia-enterprise`. SSO ini menggunakan provider **Authentik** secara internal melalui protokol OAuth2 / OpenID Connect (OIDC) terintegrasi dengan Laravel Socialite.

## 1. Konfigurasi Environment & Services

Informasi provider dan kredensial dikonfigurasi melalui `.env` dan di-bind pada `config/services.php`:

**`config/services.php`:**
```php
'authentik' => [
    'client_id'     => env('AUTHENTIK_CLIENT_ID'),
    'client_secret' => env('AUTHENTIK_CLIENT_SECRET'),
    'redirect'      => env('AUTHENTIK_REDIRECT_URI'),
    'base_url'      => env('AUTHENTIK_BASE_URL'),
],
```

## 2. Controller & Alur Autentikasi (`SocialiteController`)

Proses otentikasi ditangani oleh `App\Http\Controllers\Auth\SocialiteController`.

Terdapat dua method utama untuk flow SSO:
1. **`redirectToAuthentik()`**: Merespon request login awal dan mengarahkan user ke halaman login SSO menggunakan driver Socialite `authentik`.
2. **`handleAuthentikCallback()`**: Menangani response callback (kembalian) dari SSO setelah user berhasil login.

### Alur Callback SSO (`handleAuthentikCallback`):
1. **Mendapatkan Data Profil**: Menangkap data user dari driver Socialite Authentik.
2. **Validasi Email**: Mengekstrak provider ID dan alamat email. Email kemudian di-*canonicalize* (khusus Gmail, misalnya menghapus titik pada part lokal).
3. **Pencarian / Binding Identitas**: 
   - Sistem mencari apakah user sudah pernah login menggunakan SSO ini melalui tabel `user_identities`.
   - Jika belum, akan melakukan fallback dengan pencarian email yang serupa pada database `users`.
   - Mengatasi kasus peralihan/perubahan dari alias Google.
4. **Login Session**: Jika akun berhasil disesuaikan atau dibuat baru (Auto-Registration), user di-login ke sistem (via `Auth::login()`).
5. **Redirection**: Mengarahkan user ke halaman yang di-*intend* sebelumnya, atau secara *default* dialihkan ke halaman dashboard.

## 3. Komponen Frontend (React/Inertia.js)

Tombol "Login dengan SSO" diimplementasikan pada file view `resources/js/Pages/Auth/Login.jsx`. Tombol ini bersifat dinamis dan hanya muncul jika konfigurasi SSO diaktifkan dari backend (`ssoLoginUrl` tersedia).

**Implementasi Tombol (Login.jsx):**
```jsx
{ssoLoginUrl && (
    <div>
        <a
            href={ssoLoginUrl}
            className="flex w-full items-center justify-center gap-3 rounded-xl bg-[#fd4b2d] hover:bg-[#e44329] text-white font-semibold py-3 px-4 transition-colors duration-200 shadow-md shadow-red-500/20"
        >
            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L2 7l10 5 10-5-10-5zm0 10L2 7v10l10 5 10-5V7l-10 5z" />
            </svg>
            Login dengan SSO (Nexia)
        </a>
        {/* Divider ... */}
    </div>
)}
```

## 4. Keamanan dan Penanganan Pengguna
- **Account Linking**: Sistem otomatis menautkan akun SSO ke akun lokal berdasarkan alamat email (dan canonicalisasi email Gmail).
- **Auto-registration**: Jika pengguna baru melakukan SSO namun tidak terdapat pada sistem, sistem otomatis membuatkan akun lokal baru dengan password acak. Role default yang di-assign untuk user baru ini adalah `customer`.
- **User Identity Storage**: Token, Metadata profil SSO, provider_user_id, and waktu `last_login_at` dicatat dengan rapi pada model `UserIdentity`.

## Catatan Tambahan (Backward Compatibility)
Implementasi ini juga memelihara *backward compatibility* dengan rute otentikasi lama Google. Jika user mengakses route `/auth/google` atau callback-nya, hal tersebut akan otomatis dialihkan ke alur SSO primary yang baru yaitu `route('auth.authentik')`.

## 5. Integrasi SSO Platform Aplikasi (Kasus: Gembala)

Integrasi antara Nexia (sebagai Identity Provider) and aplikasi-aplikasi dalam ekosistem (seperti Gembala) menggunakan protokol OAuth2 standar (via Laravel Passport) yang dimodifikasi untuk menambahkan lapisan pengecekan lisensi/langganan.

### Alur Login & Pengecekan Langganan (Gembala -> Nexia):
1. **Inisiasi Login (Gembala):** Pengguna menekan tombol "Login via Nexia" di halaman Gembala. Gembala mengarahkan pengguna ke endpoint `/oauth/authorize` di server Nexia.
2. **Autentikasi Pengguna (Nexia):** Jika pengguna belum login, Nexia akan memproses login pengguna (yang akan diarahkan ke Authentik SmartSociety secara transparan). Setelah berhasil login, Nexia mengarahkan kembali ke sistem Gembala beserta *authorization code*.
3. **Pertukaran Token (Gembala -> Nexia):** Gembala menukar kode tersebut dengan Access Token melalui endpoint `/oauth/token`.
4. **Pengecekan Profil & Langganan (Gembala -> Nexia):** Menggunakan Access Token, Gembala mengambil data profil pengguna melalui endpoint `GET /api/v1/user`.
5. **Validasi Status Aktif di Database Nexia:**
   - Nexia akan secara dinamis mengecek database `unified_subscriptions` (atau model `Subscription`) berdasarkan `user_id`.
   - Nexia secara spesifik mencari data langganan yang berelasi dengan `app_slug = 'gembala'` yang berstatus `active` atau `pending`.
   - Hasil pengecekan nyata dari *database* ini dikembalikan di dalam *array* `subscriptions[]` yang menyatu dengan data profil pengguna.
6. **Keputusan Akses (Sistem Gembala):**
   - Gembala membaca porsi `subscriptions` dari JSON balasan Nexia.
   - **Sukses:** Jika terdapat nama aplikasi 'gembala' dengan status logis `active`, Gembala mengizinkan login, membuat/memperbarui *session user* secara internal, and mengizinkan pengguna masuk ke Dashboard.
   - **Ditolak:** Jika pengguna belum pernah mendaftar/berlangganan aplikasi tersebut, atau statusnya masih `pending` (belum dibayar/diverifikasi admin), Gembala akan **secara sepihak membatalkan proses login** and melempar kembali ke form login beserta peringatan merah di layar agar pengguna segera mengatur pembayarannya di Nexia terlebih dahulu.

### Keamanan Kredensial API (Fleksibilitas Kunci Ganda):
Secara sistematis, Nexia mengamankan validasi integrasi ini (seperti endpoint verifikasi lisensi) dengan membandingkan token and kredensial rahasia (ID and Secret). 
Sistem Nexia telah diatur ulang pada `PlatformAppController@resolveAppByCredentials` agar mampu menerima proses verifikasi baik menggunakan *Platform Credential API* reguler, maupun menggunakan gabungan *OAuth Client ID* and *OAuth Client Secret* bawaan Passport. Hal ini membuat aplikasi di bawah naungan Nexia (seperti Gembala) cukup membekali dirinya dengan satu set *Client Keys* rahasia di file `.env`-nya.

---

## 6. Daftar Bug & Masalah Aktif (SSO)

### 🔴 BUG 1: Logout dari Nexia → "Permission Denied" di SmartSociety

**Status:** Sedang Ditangani  
**Deskripsi:**  
Saat pengguna menekan tombol *Logout* di dashboard Nexia (`erp.pustekno.id`), sistem diarahkan ke alur *Invalidation* global milik Authentik (`/if/flow/default-invalidation-flow/`). Authentik berhasil menghapus sesi, namun kemudian **mengarahkan pengguna ke halaman `auth.smartsociety.id/if/user/`** (antarmuka admin Authentik internal) **alih-alih kembali ke Nexia**. Karena pengguna bukan admin internal SmartSociety, muncul error **"Permission Denied: Interface can only be accessed by internal users"**.

**Root Cause:**  
Authentik konfigurasi pada tenant SmartSociety **menolak/mengabaikan parameter `?next=`** pada alur Invalidation. Sehingga setelah sesi dimusnahkan, tidak ada arahan balik ke aplikasi asal.

**Letak Kode:**  
`app/Http/Controllers/Auth/LoginController.php` → method `logout()` (baris ~205)

**Solusi yang dicoba & statusnya:**
- ❌ Menonaktifkan redirect ke Authentik (cukup logout lokal) → **Gagal**: Login SSO selanjutnya auto-login otomatis tanpa menampilkan opsi email/Google
- 🔄 Menggunakan `?next=` dengan URL yang di-encode ke rute `auth.authentik` → **Belum diuji di produksi**

**Aksi yang dibutuhkan:**  
Di dashboard SmartSociety, admin perlu mengizinkan redirect URL `https://erp.pustekno.id/auth/authentik` and `https://erp.pustekno.id/` sebagai *Allowed Redirect URIs* pada Application Authentik yang bersangkutan agar parameter `?next=` diakui oleh alur Invalidation.

---

### 🔴 BUG 2: Tombol "Login dengan SSO" → Langsung Login Tanpa Opsi Email/Google

**Status:** Sedang Ditangani  
**Deskripsi:**  
Ketika pengguna mengklik tombol **"Login dengan SSO (SmartSociety)"** dari halaman login `erp.pustekno.id`, sistem **langsung menyetujui login otomatis** dengan akun yang sebelumnya pernah digunakan, **tanpa pernah menampilkan opsi pilihan metode login** (Email / Login dengan Google) dari SmartSociety.

**Root Cause:**  
SmartSociety (Authentik) masih memiliki *session cookie* aktif di browser dari login sebelumnya. Karena driver Socialite custom (`App\Socialite\AuthentikProvider`) hanya meneruskan parameter dasar OAuth2, Authentik mengabaikan semua parameter paksa standar OIDC:
- ❌ `prompt=select_account` → Diabaikan
- ❌ `prompt=login` → Diabaikan  
- ❌ `max_age=0` → Diabaikan

**Letak Kode:**  
`app/Socialite/AuthentikProvider.php` → `getAuthUrl()` (hanya membangun URL dasar tanpa dukungan parameter tambahan)  
`app/Http/Controllers/Auth/SocialiteController.php` → `redirectToAuthentik()`

**Solusi yang dicoba & statusnya:**
- ❌ Mengirim `prompt=select_account` via `$driver->with([...])` → **Diabaikan Authentik**
- ❌ Mengirim `max_age=0` → **Diabaikan Authentik**
- 🔄 Mematikan sesi Authentik paksa melalui `default-invalidation-flow` sebelum redirect login baru → **Belum diuji di produksi**

**Aksi yang dibutuhkan:**  
Solusi jangka panjang yang tepat adalah mengaktifkan **OIDC Logout / End-Session Endpoint** pada konfigurasi Application di dashboard SmartSociety, agar URL `https://auth.smartsociety.id/application/o/nexia-erp/end-session/` (sesuaikan dengan slug aplikasi) dapat dipanggil sebelum pengguna diarahkan login ulang.

---

### ✅ BUG 3 (SELESAI): Tombol "Not You?" di Authorize Application Tidak Berfungsi

**Status:** Selesai (Perlu Deploy)  
**Deskripsi:**  
Tombol "Not you? Login as different user" di halaman *Authorize Application* Nexia sebelumnya hanya mengarahkan ke `/login` biasa Nexia, bukan ke SmartSociety. Sehingga ketika pengguna menekan SSO lagi, SmartSociety langsung auto-login akun lama.

**Solusi Diimplementasikan:**  
Rute `POST /oauth/switch-account` diperbarui untuk menghancurkan sesi lokal lalu langsung mengeksekusi alur *Invalidation* SmartSociety sebelum melempar pengguna ke endpoint login SSO baru.

---

### ✅ BUG 4 (SELESAI): Notifikasi Akun Tersuspend Tidak Muncul di Gembala

**Status:** Selesai  
**Deskripsi:**  
Ketika akun pengguna berstatus *suspended* mencoba login via SSO di Gembala, penolakan login terjadi namun **tidak ada notifikasi yang terlihat** di layar login Gembala.

**Solusi Diimplementasikan:**  
`SsoController.php` Gembala diperbarui untuk memanggil `Filament\Notifications\Notification::make()` untuk menampilkan Toast merah berjudul "Akses Ditolak" dengan pesan yang sesuai status.

---

### ⚠️ CATATAN: Fitur "Remember Me" pada Halaman Login

**Status:** Perlu Dikonfirmasi  
**Deskripsi:**  
Halaman login Nexia masih menampilkan checkbox **"Remember Me"** (Ingat saya). Ketika pengguna menggunakan jalur SSO (SmartSociety), opsi ini **tidak relevan** karena manajemen sesi dikendalikan sepenuhnya oleh Authentik, bukan oleh cookie lokal Nexia.

**Aksi yang dibutuhkan:**  
Konfirmasi apakah checkbox ini perlu disembunyikan khusus saat mode SSO aktif, atau cukup dibiarkan muncul (tapi diabaikan saat login SSO).
