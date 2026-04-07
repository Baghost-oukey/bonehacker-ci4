# Gembala Integration Guide (Nexia SSO + Layanan)

Panduan ini khusus untuk tim Gembala agar implementasi ke Nexia konsisten.

## 1. Data Yang Harus Diambil Dari Nexia

Ambil dari menu `https://erp.pustekno.id/admin/pengaturan-aplikasi` untuk app `gembala`:
- `app_slug`
- `client_id`
- `client_secret`
- `signing_key`
- `oauth_client_id`
- `oauth_client_secret`
- `callback_url`
- `redirect_url`
- `nexia_base_url`

Catatan penting:
- `callback_url` = endpoint callback OAuth Gembala (contoh: `https://gembala.id/auth/callback`)
- `redirect_url` = endpoint entry login dari Nexia (disarankan: `https://gembala.id/login` atau `https://gembala.id/sso/entry`)

## 2. Environment Variable Di Gembala

```env
NEXIA_BASE_URL=https://erp.pustekno.id
NEXIA_APP_SLUG=gembala
NEXIA_APP_DOMAIN=gembala.id
NEXIA_ENVIRONMENT=production

NEXIA_CLIENT_ID=...
NEXIA_CLIENT_SECRET=...
NEXIA_SIGNING_KEY=...

NEXIA_OAUTH_CLIENT_ID=...
NEXIA_OAUTH_CLIENT_SECRET=...
NEXIA_CALLBACK_URL=https://gembala.id/auth/callback
```

## 3. Alur Login User (OAuth Code Flow)

Langkah:
1. User klik tombol login di Gembala.
2. Gembala redirect browser ke `/oauth/authorize` di Nexia.
3. Nexia cek session login. Jika belum login, user diarahkan ke Authentik.
4. Setelah sukses, Nexia redirect balik ke callback Gembala dengan `code` + `state`.
5. Backend Gembala tukar `code` ke `/oauth/token`.
6. Backend Gembala ambil profil user dari `/oauth/user`.
7. Gembala membuat/menyinkronkan user lokal.

Contoh URL authorize:

```text
https://erp.pustekno.id/oauth/authorize
?client_id={NEXIA_OAUTH_CLIENT_ID}
&redirect_uri=https%3A%2F%2Fgembala.id%2Fauth%2Fcallback
&response_type=code
&state={RANDOM_STATE}
&scope=
```

Penting:
- Gunakan redirect browser, jangan `axios/fetch` ke endpoint authorize.
- `client_id` OAuth harus UUID yang valid.
- `Open Application` dari Nexia akan membuka `redirect_url` (bukan `callback_url`).
- Jika ingin auto-login dari Nexia, pastikan `redirect_url` mengarah ke halaman yang otomatis trigger SSO saat belum login.

## 4. Alur Register User Dari Gembala

Untuk tombol register di Gembala:
- arahkan ke `https://erp.pustekno.id/layanan/gembala/register`

Nexia akan menangani:
- login user (jika belum login)
- form pendaftaran layanan
- organisasi/lembaga bila dibutuhkan
- checkout bila plan berbayar
- redirect ke Gembala saat layanan siap diakses

## 5. Cek Lisensi Aplikasi (Level Aplikasi, Tiap 7 Hari)

Endpoint:
- `GET https://erp.pustekno.id/api/platform/apps/gembala/license`

Header:
- `X-Nexia-Client-Id: {NEXIA_CLIENT_ID}`
- `X-Nexia-Client-Secret: {NEXIA_CLIENT_SECRET}`
- `X-Nexia-App-Domain: gembala.id`
- `X-Nexia-Environment: production`

Kontrak status:
- `valid` -> HTTP 200
- `invalid` -> HTTP 403
- `expired` -> HTTP 402
- `revoked` -> HTTP 403

Saat status selain `valid`, mode pembatasan berlaku untuk super admin aplikasi.

Catatan transaksi gratis:
- Pendaftaran plan gratis tetap membuat record di `service_orders` dengan nominal `0` dan status `paid`.
- Jadi jejak transaksi tetap lengkap meskipun tanpa pembayaran gateway.

## 6. Cek Status Layanan User Saat Login (Level User)

Endpoint:
- `POST https://erp.pustekno.id/api/platform/apps/gembala/user-status`

Header:
- `X-Nexia-Client-Id`
- `X-Nexia-Client-Secret`

Payload minimal:

```json
{
  "service_slug": "gembala",
  "email": "user@example.com"
}
```

Gunakan field ini untuk keputusan akses:
- `service_status`
- `billing_status`
- `can_access`
- `next_action`

## 7. Checklist Integrasi Gembala

- OAuth login berjalan end-to-end.
- Tombol register mengarah ke `/layanan/gembala/register`.
- Callback URL sama persis dengan yang didaftarkan di Nexia.
- Redirect URL diarahkan ke endpoint login/entry SSO (bukan homepage statis).
- License check cron 7 hari aktif.
- User-status check dieksekusi saat login sukses.
- Grace handling ada untuk timeout API Nexia.
- Logging ada untuk response gagal (`invalid/expired/revoked`).
