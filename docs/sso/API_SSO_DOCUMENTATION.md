# Nexia ERP SSO & Layanan API Documentation

Dokumen ini menggantikan versi lama yang masih memakai narasi Sanctum sebagai jalur utama.

Status saat ini:
- SSO antar aplikasi: OAuth 2.0 Authorization Code via Laravel Passport.
- Login user Nexia: diarahkan ke `auth.smartsociety.id` (Authentik) melalui Socialite.
- Validasi lisensi aplikasi: endpoint `api/platform/apps/*` dengan `client_id` + `client_secret` aplikasi.

## Base URL

- Production: `https://erp.pustekno.id`
- Development lokal: `http://localhost`

## 1. OAuth 2.0 Endpoints (Dipakai Aplikasi Client)

- `GET /oauth/authorize`
- `POST /oauth/token`
- `GET /oauth/user`

### 1.1 Authorization Endpoint

Gunakan redirect browser biasa (bukan AJAX/XHR) ke:

```text
GET {NEXIA_BASE_URL}/oauth/authorize
  ?client_id={NEXIA_OAUTH_CLIENT_ID}
  &redirect_uri={URL_CALLBACK}
  &response_type=code
  &state={RANDOM_CSRF_STATE}
  &scope=
```

Catatan:
- `client_id` harus UUID dari tabel `oauth_clients`.
- `redirect_uri` harus sama dengan yang terdaftar di Nexia.
- Jika user belum login di Nexia, akan diarahkan otomatis ke flow Authentik.
- Jangan panggil endpoint ini via `fetch/axios` (akan kena CORS/preflight redirect).

### 1.2 Exchange Code ke Token

```bash
curl -X POST https://erp.pustekno.id/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=authorization_code" \
  -d "client_id=${NEXIA_OAUTH_CLIENT_ID}" \
  -d "client_secret=${NEXIA_OAUTH_CLIENT_SECRET}" \
  -d "redirect_uri=${URL_CALLBACK}" \
  -d "code=${AUTHORIZATION_CODE}"
```

### 1.3 Ambil Profil User

```bash
curl -X GET https://erp.pustekno.id/oauth/user \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
```

Contoh respons ringkas:

```json
{
  "id": 123,
  "name": "User Demo",
  "email": "user@example.com",
  "roles": ["customer"],
  "permissions": []
}
```

## 2. Platform App Registry & License API

Semua endpoint berikut wajib kirim kredensial aplikasi:
- Header `X-Nexia-Client-Id`
- Header `X-Nexia-Client-Secret`

Opsional:
- `X-Nexia-App-Domain`
- `X-Nexia-Environment`

### 2.1 Endpoint List

- `POST /api/platform/apps/{app_slug}/validate`
- `GET /api/platform/apps/{app_slug}/license`
- `GET /api/platform/apps/{app_slug}/service-status`
- `POST /api/platform/apps/{app_slug}/user-status`
- `POST /api/platform/apps/{app_slug}/heartbeat`

### 2.2 Kontrak Respons Lisensi

`GET /api/platform/apps/{app_slug}/license` atau `POST /validate`

Status baku:
- `valid` -> HTTP `200`, `code=LICENSE_VALID`, `admin_mode=normal`
- `invalid` -> HTTP `403`, `code=LICENSE_INVALID`, `admin_mode=restricted`
- `expired` -> HTTP `402`, `code=LICENSE_EXPIRED`, `admin_mode=restricted`
- `revoked` -> HTTP `403`, `code=LICENSE_REVOKED`, `admin_mode=restricted`

Contoh sukses (`valid`):

```json
{
  "success": true,
  "code": "LICENSE_VALID",
  "license_status": "valid",
  "app_slug": "gembala",
  "domain": "gembala.id",
  "environment": "production",
  "admin_mode": "normal",
  "refresh_after": "2026-02-20T12:00:00+00:00"
}
```

Contoh gagal (`invalid/expired/revoked`):

```json
{
  "success": false,
  "code": "LICENSE_INVALID",
  "license_status": "invalid",
  "admin_mode": "restricted",
  "required_action": "update_license"
}
```

### 2.3 Kontrak Respons Status User Layanan

`POST /api/platform/apps/{app_slug}/user-status`

Request minimum:

```json
{
  "service_slug": "gembala",
  "email": "user@example.com"
}
```

Jika user/subscription ada:
- `service_status`: `active|pending|suspended|cancelled|terminated`
- `billing_status`: `paid|unpaid|...`
- `can_access`: `true|false`
- `next_action`: `enter_service|complete_payment|contact_support|renew_or_reactivate|contact_sales`

Jika tidak ada subscription:
- `service_status=not_found`
- `can_access=false`
- `next_action=register_service`

### 2.4 Catatan Service Order

- Semua transaksi layanan dicatat di `service_orders`.
- Untuk plan gratis, sistem tetap membuat order bernilai `0` dengan status `paid` (auto approved), agar audit transaksi tetap konsisten.

## 3. Region API (Untuk Form Wilayah)

- `GET /api/regions/countries`
- `GET /api/regions/provinces`
- `GET /api/regions/regencies/{provinceCode}`
- `GET /api/regions/districts/{regencyCode}`
- `GET /api/regions/villages/{districtCode}`

Sumber data utama: `https://region.smartsociety.id/api/*`

## 4. Environment Minimum di Aplikasi Client

Gunakan variabel ini di aplikasi seperti Gembala/Puspa/SmartGovt:

- `NEXIA_BASE_URL` (contoh `https://erp.pustekno.id`)
- `NEXIA_APP_SLUG` (contoh `gembala`)
- `NEXIA_APP_DOMAIN` (contoh `gembala.id`)
- `NEXIA_ENVIRONMENT` (`production|staging|development`)
- `NEXIA_CLIENT_ID` (kredensial app untuk endpoint lisensi/status)
- `NEXIA_CLIENT_SECRET` atau `NEXIA_SIGNING_KEY`
- `NEXIA_OAUTH_CLIENT_ID` (UUID OAuth client untuk login SSO)
- `NEXIA_OAUTH_CLIENT_SECRET` (secret OAuth client)

## 5. Endpoint Legacy (Tidak Untuk Integrasi Baru)

Endpoint lama `api/auth/*` berbasis token aplikasi tetap ada untuk kompatibilitas internal/legacy, tetapi bukan jalur utama integrasi SSO aplikasi perusahaan.

Untuk integrasi baru, gunakan:
- OAuth (`/oauth/authorize`, `/oauth/token`, `/oauth/user`)
- Platform app endpoints (`/api/platform/apps/*`)
- Layanan onboarding via web Nexia (`/layanan/{app_slug}/register`)
