# Konfigurasi Logout SSO — Authentik (SmartSociety)

## Masalah
Setelah pengguna logout dari Nexia/Gembala dan mencoba login kembali via SSO, pengguna **langsung auto-login** tanpa ditampilkan form login SmartSociety (pilihan email/Google). Ini terjadi karena sesi browser Authentik masih hidup meskipun sesi aplikasi sudah dihapus.

## Penyebab
Flow `default-provider-invalidation-flow` yang dipanggil oleh endpoint `end-session` hanya menghapus **sesi aplikasi** (OAuth token), bukan **sesi browser Authentik** (cookie). Tanpa stage "User Logout", cookie browser tetap hidup → auto-login saat SSO berikutnya.

---

## Langkah 1: Tambahkan "User Logout" Stage ke Provider Invalidation Flow

> **Ini langkah paling penting.** Tanpa ini, sesi browser Authentik tidak akan dihapus saat logout.

### 1.1 Buat stage "User Logout" (jika belum ada)
1. Login ke Authentik Admin: `https://auth.smartsociety.id/if/admin/`
2. Navigasi ke **Flows & Stages → Stages**
3. Cek apakah sudah ada stage bertipe **"User Logout"**
4. Jika belum ada:
   - Klik **Create**
   - Pilih tipe: **User Logout Stage**
   - Nama: `user-logout`
   - Klik **Save**

### 1.2 Bind stage ke flow invalidation
1. Navigasi ke **Flows & Stages → Flows**
2. Klik flow **`default-provider-invalidation-flow`**
3. Klik tab **"Stage Bindings"**
4. Klik **"Bind Existing Stage"** atau **"Create Binding"**
5. Pilih stage: **`user-logout`**
6. Set **Order**: `0` (agar dijalankan pertama, sebelum stage lainnya)
7. Klik **Save**

### Hasil
Setelah konfigurasi ini, ketika endpoint `end-session` dipanggil:
- ✅ Sesi aplikasi dihapus (OAuth token di-revoke)
- ✅ Sesi browser Authentik dihapus (cookie di-clear)
- ✅ Pengguna benar-benar logout dari SmartSociety

---

## Langkah 2: Daftarkan Post-Logout Redirect URI

> Agar setelah logout, Authentik **otomatis redirect kembali ke homepage Nexia** (bukan menampilkan halaman "You've logged out of smartsociety").

1. Navigasi ke **Applications → Providers**
2. Klik provider OAuth2 yang terhubung ke aplikasi **smartsociety**
3. Cari bagian **"Redirect URIs/Origins (RegEx)"**
4. Pastikan URI berikut sudah terdaftar:
   ```
   https://erp.pustekno.id/
   ```
5. Jika belum ada, tambahkan URI tersebut
6. Klik **Save**

### Catatan
- URI ini digunakan oleh parameter `post_logout_redirect_uri` pada endpoint `end-session`
- Tanpa mendaftarkan URI ini, Authentik akan menampilkan halaman konfirmasi "You've logged out" tanpa auto-redirect
- Untuk Gembala (saat production), tambahkan juga URL homepage Gembala (misal: `https://gembala.example.com/`)

---

## Langkah 3: Update Kode Nexia

> Setelah Langkah 1 dan 2 selesai, update kode Nexia agar logout redirect ke endpoint `end-session`.

### File: `app/Http/Controllers/Auth/LoginController.php`

Ubah method `logout()` menjadi:

```php
public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    // Redirect ke Authentik End-Session untuk clear sesi SSO + browser.
    // Prasyarat: Langkah 1 & 2 di KONFIGURASI_LOGOUT_SSO_AUTHENTIK.md sudah dilakukan.
    if (config('sso.enabled') && config('services.authentik.base_url')) {
        $authentikBaseUrl = rtrim(config('services.authentik.base_url'), '/');
        $appSlug = config('services.authentik.app_slug', 'smartsociety');
        $endSessionUrl = $authentikBaseUrl . '/application/o/' . $appSlug . '/end-session/'
            . '?post_logout_redirect_uri=' . urlencode(url('/'));
        return Inertia::location($endSessionUrl);
    }

    return Inertia::location(url('/'));
}
```

---

## Verifikasi

Setelah semua langkah selesai, test flow berikut:

### Test 1: Logout → Redirect ke Homepage
1. Login ke Nexia via SSO
2. Klik **Logout**
3. **Harapan:** Langsung redirect ke `https://erp.pustekno.id/` (homepage)
4. **Jika gagal:** Cek Langkah 2 (redirect URI belum terdaftar)

### Test 2: Login Lagi → Tampil Form Login
1. Setelah logout (Test 1), klik **Login** → **Login dengan SSO (SmartSociety)**
2. **Harapan:** SmartSociety menampilkan form login (Email + Google), bukan auto-login
3. **Jika masih auto-login:** Cek Langkah 1 (stage "User Logout" belum ter-bind)

### Test 3: Ganti Akun
1. Login dengan akun A → Logout → Login lagi
2. Di form login SmartSociety, pilih akun B
3. **Harapan:** Masuk ke Nexia dengan akun B

---

## Flow Diagram (Setelah Konfigurasi)

```
Pengguna klik Logout di Nexia
  │
  ├── 1. Clear sesi lokal Nexia (Laravel session)
  │
  ├── 2. Redirect ke: auth.smartsociety.id/application/o/smartsociety/end-session/
  │      ?post_logout_redirect_uri=https://erp.pustekno.id/
  │
  ├── 3. Authentik menjalankan default-provider-invalidation-flow:
  │      ├── Stage: user-logout → HAPUS sesi browser (cookie) ← LANGKAH 1
  │      └── Stage: (default stages lainnya)
  │
  ├── 4. Authentik redirect ke post_logout_redirect_uri ← LANGKAH 2
  │      → https://erp.pustekno.id/
  │
  └── 5. Pengguna kembali ke homepage Nexia ✅

Pengguna klik Login dengan SSO
  │
  ├── 1. Redirect ke Authentik authorization endpoint
  │
  ├── 2. Authentik cek sesi browser → TIDAK ADA (sudah dihapus di step 3 logout)
  │
  ├── 3. Authentik tampilkan form login (Email + Google) ✅
  │
  └── 4. Pengguna pilih akun → Callback ke Nexia → Login berhasil ✅
```

---

## Troubleshooting

### Masalah: Halaman "You've logged out of smartsociety" masih muncul
- **Penyebab:** `post_logout_redirect_uri` belum terdaftar di provider
- **Solusi:** Lakukan Langkah 2

### Masalah: Masih auto-login setelah logout
- **Penyebab:** Stage "User Logout" belum ditambahkan ke flow
- **Solusi:** Lakukan Langkah 1

### Masalah: Permission Denied saat login
- **Penyebab:** Jangan redirect ke `/if/flow/default-invalidation-flow/` — flow ini redirect ke `/if/user/` yang membutuhkan akses internal user
- **Solusi:** Gunakan endpoint `end-session` (bukan invalidation flow langsung)

### Masalah: `prompt=login` menyebabkan auth loop
- **Penyebab:** Parameter `prompt=login` konflik dengan Google Social Source di Authentik — setelah Google berhasil authenticate, Authentik menampilkan form login lagi
- **Solusi:** Jangan gunakan parameter `prompt=login`. Gunakan pendekatan end-session + User Logout stage
