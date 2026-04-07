# SSO Flow Diagrams - Nexia ERP

## 🎯 Flow 1: Login dari pustekno.id

```
┌─────────────────────────────────────────────────────────────────────┐
│                         USER JOURNEY                                 │
└─────────────────────────────────────────────────────────────────────┘

1. User membuka pustekno.id
   ┌──────────────────┐
   │  pustekno.id     │
   │  (Homepage)      │
   │                  │
   │  [Login Button]  │ ← User klik
   └──────────────────┘

2. Redirect ke ERP Login
   ┌──────────────────┐
   │ erp.pustekno.id  │
   │  /oauth/authorize│
   │                  │
   │  ?client_id=xxx  │
   │  &redirect_uri=  │
   │   pustekno.id/   │
   │   callback       │
   └──────────────────┘

3. User Login di ERP
   ┌──────────────────┐
   │ erp.pustekno.id  │
   │  /login          │
   │                  │
   │  Email: ____     │
   │  Pass:  ____     │
   │  [Login] ←────   │ User input credentials
   └──────────────────┘
          │
          │ Login Success
          ▼
   ┌──────────────────┐
   │ erp.pustekno.id  │
   │  /oauth/approve  │
   │                  │
   │  Authorize       │
   │  pustekno.id?    │
   │  [Allow] ←────   │ User approve (atau auto-approve)
   └──────────────────┘

4. Redirect kembali dengan Authorization Code
   ┌──────────────────┐
   │  pustekno.id     │
   │  /auth/callback  │
   │                  │
   │  ?code=ABC123    │
   │  &state=xyz      │
   └──────────────────┘
          │
          │ Backend exchange code for token
          ▼
   ┌──────────────────┐
   │ POST to ERP:     │
   │ /oauth/token     │
   │                  │
   │ code=ABC123      │
   │ client_secret=   │
   └──────────────────┘
          │
          │ Response: access_token
          ▼
   ┌──────────────────┐
   │ GET user info:   │
   │ /oauth/user      │
   │                  │
   │ Bearer: token    │
   └──────────────────┘

5. User masuk Dashboard
   ┌──────────────────┐
   │  pustekno.id     │
   │  /dashboard      │
   │                  │
   │  Welcome, User!  │
   │  [Logout]        │
   └──────────────────┘
```

---

## 🎯 Flow 2: Login dari todolist.pustekno.id

```
┌─────────────────────────────────────────────────────────────────────┐
│                         USER JOURNEY                                 │
└─────────────────────────────────────────────────────────────────────┘

1. User membuka todolist.pustekno.id
   ┌──────────────────────┐
   │ todolist.pustekno.id │
   │  (Task List)         │
   │                      │
   │  [Login Button]      │ ← User klik
   └──────────────────────┘

2. Redirect ke ERP Login
   ┌──────────────────────┐
   │  erp.pustekno.id     │
   │  /oauth/authorize    │
   │                      │
   │  ?client_id=yyy      │
   │  &redirect_uri=      │
   │   todolist.pustekno  │
   │   .id/callback       │
   └──────────────────────┘

3. User sudah login di ERP sebelumnya
   ┌──────────────────────┐
   │  erp.pustekno.id     │
   │                      │
   │  ✓ Already logged in │
   │  Auto-approve        │
   │  (No login needed)   │
   └──────────────────────┘
          │
          │ Auto redirect
          ▼
4. Langsung ke Dashboard
   ┌──────────────────────┐
   │ todolist.pustekno.id │
   │  /dashboard          │
   │                      │
   │  Your Tasks:         │
   │  □ Task 1            │
   │  □ Task 2            │
   └──────────────────────┘
```

---

## 🔄 Flow 3: Sequence Diagram (Technical)

```
User          pustekno.id      erp.pustekno.id       Database
 │                 │                   │                 │
 │  1. Click Login │                   │                 │
 ├────────────────>│                   │                 │
 │                 │                   │                 │
 │                 │ 2. Redirect to    │                 │
 │                 │    /oauth/authorize                 │
 │<────────────────┼──────────────────>│                 │
 │                 │                   │                 │
 │  3. Show Login Form                 │                 │
 │<────────────────────────────────────┤                 │
 │                 │                   │                 │
 │  4. Submit Credentials              │                 │
 ├────────────────────────────────────>│                 │
 │                 │                   │                 │
 │                 │                   │ 5. Verify User  │
 │                 │                   ├────────────────>│
 │                 │                   │<────────────────┤
 │                 │                   │                 │
 │  6. Show Authorization Page         │                 │
 │<────────────────────────────────────┤                 │
 │                 │                   │                 │
 │  7. Approve     │                   │                 │
 ├────────────────────────────────────>│                 │
 │                 │                   │                 │
 │                 │                   │ 8. Create Code  │
 │                 │                   ├────────────────>│
 │                 │                   │<────────────────┤
 │                 │                   │                 │
 │  9. Redirect with code              │                 │
 │<────────────────┼───────────────────┤                 │
 │                 │                   │                 │
 │                 │ 10. Exchange code │                 │
 │                 │     for token     │                 │
 │                 ├──────────────────>│                 │
 │                 │                   │                 │
 │                 │                   │ 11. Verify Code │
 │                 │                   ├────────────────>│
 │                 │                   │<────────────────┤
 │                 │                   │                 │
 │                 │ 12. Return token  │                 │
 │                 │<──────────────────┤                 │
 │                 │                   │                 │
 │                 │ 13. Get user info │                 │
 │                 ├──────────────────>│                 │
 │                 │<──────────────────┤                 │
 │                 │                   │                 │
 │  14. Redirect to Dashboard          │                 │
 │<────────────────┤                   │                 │
 │                 │                   │                 │
```

---

## 🔐 Security Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                               │
└─────────────────────────────────────────────────────────────────┘

Layer 1: HTTPS
┌──────────────────────────────────────────┐
│  All communications encrypted with TLS   │
│  ✓ erp.pustekno.id → HTTPS              │
│  ✓ pustekno.id → HTTPS                  │
│  ✓ todolist.pustekno.id → HTTPS         │
└──────────────────────────────────────────┘

Layer 2: State Parameter (CSRF Protection)
┌──────────────────────────────────────────┐
│  1. Client generates random state        │
│  2. Store in session                     │
│  3. Send to ERP                          │
│  4. ERP returns same state               │
│  5. Client verifies state matches        │
└──────────────────────────────────────────┘

Layer 3: Authorization Code
┌──────────────────────────────────────────┐
│  ✓ One-time use only                     │
│  ✓ Expires in 10 minutes                 │
│  ✓ Tied to specific client               │
│  ✓ Cannot be reused                      │
└──────────────────────────────────────────┘

Layer 4: Client Secret
┌──────────────────────────────────────────┐
│  ✓ Stored securely on server             │
│  ✓ Never exposed to browser              │
│  ✓ Required to exchange code for token   │
└──────────────────────────────────────────┘

Layer 5: Access Token
┌──────────────────────────────────────────┐
│  ✓ Bearer token                          │
│  ✓ Expires after configured time         │
│  ✓ Can be revoked                        │
│  ✓ Scoped permissions                    │
└──────────────────────────────────────────┘
```

---

## 📊 Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    DATA EXCHANGED                                │
└─────────────────────────────────────────────────────────────────┘

Step 1: Authorization Request
┌──────────────────────────────────────────┐
│  GET /oauth/authorize                    │
│  ?client_id=pustekno-web                 │
│  &redirect_uri=https://pustekno.id/      │
│   callback                               │
│  &response_type=code                     │
│  &scope=user:read                        │
│  &state=random_string_xyz                │
└──────────────────────────────────────────┘

Step 2: Authorization Response
┌──────────────────────────────────────────┐
│  Redirect to:                            │
│  https://pustekno.id/callback            │
│  ?code=AUTH_CODE_123456                  │
│  &state=random_string_xyz                │
└──────────────────────────────────────────┘

Step 3: Token Request
┌──────────────────────────────────────────┐
│  POST /oauth/token                       │
│  {                                       │
│    "grant_type": "authorization_code",   │
│    "code": "AUTH_CODE_123456",           │
│    "client_id": "pustekno-web",          │
│    "client_secret": "secret_key",        │
│    "redirect_uri": "https://pustekno.id/ │
│     callback"                            │
│  }                                       │
└──────────────────────────────────────────┘

Step 4: Token Response
┌──────────────────────────────────────────┐
│  {                                       │
│    "access_token": "eyJ0eXAiOiJKV1...", │
│    "token_type": "Bearer",               │
│    "expires_in": 3600,                   │
│    "refresh_token": "def50200...",       │
│    "scope": "user:read"                  │
│  }                                       │
└──────────────────────────────────────────┘

Step 5: User Info Request
┌──────────────────────────────────────────┐
│  GET /oauth/user                         │
│  Authorization: Bearer eyJ0eXAiOiJKV1...│
└──────────────────────────────────────────┘

Step 6: User Info Response
┌──────────────────────────────────────────┐
│  {                                       │
│    "id": 1,                              │
│    "name": "John Doe",                   │
│    "email": "john@pustekno.id",          │
│    "avatar_url": "https://...",          │
│    "roles": ["employee", "admin"]        │
│  }                                       │
└──────────────────────────────────────────┘
```

---

## 🎨 UI Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER INTERFACE                                │
└─────────────────────────────────────────────────────────────────┘

pustekno.id - Before Login
┌────────────────────────────────────────┐
│  PUSTEKNO.ID                           │
│  ────────────────────────────────────  │
│                                        │
│  Selamat Datang di Pustekno            │
│                                        │
│  ┌──────────────────────────────────┐ │
│  │  Login dengan ERP Pustekno       │ │
│  └──────────────────────────────────┘ │
│                                        │
└────────────────────────────────────────┘

erp.pustekno.id - Login Page
┌────────────────────────────────────────┐
│  ERP PUSTEKNO                          │
│  ────────────────────────────────────  │
│                                        │
│  Login ke ERP                          │
│                                        │
│  Email:    [________________]          │
│  Password: [________________]          │
│                                        │
│  ┌──────────────┐  ┌───────────────┐  │
│  │    Login     │  │ Google Login  │  │
│  └──────────────┘  └───────────────┘  │
│                                        │
│  pustekno.id ingin mengakses akun Anda│
└────────────────────────────────────────┘

erp.pustekno.id - Authorization Page
┌────────────────────────────────────────┐
│  ERP PUSTEKNO                          │
│  ────────────────────────────────────  │
│                                        │
│  Authorize pustekno.id?                │
│                                        │
│  pustekno.id ingin:                    │
│  ✓ Membaca profil Anda                 │
│  ✓ Mengakses data karyawan             │
│                                        │
│  ┌──────────────┐  ┌───────────────┐  │
│  │    Allow     │  │     Deny      │  │
│  └──────────────┘  └───────────────┘  │
│                                        │
└────────────────────────────────────────┘

pustekno.id - After Login
┌────────────────────────────────────────┐
│  PUSTEKNO.ID          [John Doe ▼]     │
│  ────────────────────────────────────  │
│                                        │
│  Dashboard                             │
│                                        │
│  Selamat datang, John Doe!             │
│                                        │
│  ┌────────────┐  ┌────────────┐       │
│  │  Projects  │  │   Tasks    │       │
│  └────────────┘  └────────────┘       │
│                                        │
└────────────────────────────────────────┘
```

---

## 🔄 Logout Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    LOGOUT SCENARIOS                              │
└─────────────────────────────────────────────────────────────────┘

Scenario 1: Logout dari pustekno.id saja
┌──────────────────────────────────────────┐
│  User klik Logout di pustekno.id         │
│  ↓                                       │
│  Hapus session lokal                     │
│  ↓                                       │
│  User logout dari pustekno.id            │
│  ↓                                       │
│  Tapi masih login di ERP                 │
│  ↓                                       │
│  Bisa langsung SSO lagi tanpa login      │
└──────────────────────────────────────────┘

Scenario 2: Logout dari semua aplikasi
┌──────────────────────────────────────────┐
│  User klik Logout di pustekno.id         │
│  ↓                                       │
│  Hapus session lokal                     │
│  ↓                                       │
│  Revoke token di ERP                     │
│  ↓                                       │
│  Redirect ke ERP logout                  │
│  ↓                                       │
│  User logout dari semua aplikasi         │
└──────────────────────────────────────────┘

Scenario 3: Logout dari ERP
┌──────────────────────────────────────────┐
│  User logout dari erp.pustekno.id        │
│  ↓                                       │
│  Revoke semua access tokens              │
│  ↓                                       │
│  pustekno.id & todolist masih punya      │
│  session lokal                           │
│  ↓                                       │
│  Saat refresh, token invalid             │
│  ↓                                       │
│  Auto redirect ke login                  │
└──────────────────────────────────────────┘
```

---

**Dibuat:** 2026-02-04  
**Author:** Antigravity AI  
**Untuk:** Pustekno SSO Implementation
