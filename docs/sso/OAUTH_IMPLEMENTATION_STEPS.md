# OAuth 2.0 True SSO Implementation - Step by Step

## ✅ Phase 1: Install Laravel Passport (COMPLETED)

```bash
./vendor/bin/sail composer require laravel/passport
```

**Status:** ✅ DONE - Passport v13.4.3 installed

---

## 🔧 Phase 2: Configure Passport

### Step 1: Run Passport Migrations

```bash
./vendor/bin/sail artisan migrate
```

Ini akan membuat tabel:
- `oauth_auth_codes`
- `oauth_access_tokens`
- `oauth_refresh_tokens`
- `oauth_clients`
- `oauth_personal_access_clients`

### Step 2: Install Passport

```bash
./vendor/bin/sail artisan passport:install
```

Ini akan:
- Generate encryption keys
- Create "Personal Access Client"
- Create "Password Grant Client"

**PENTING:** Simpan Client ID dan Secret yang dihasilkan!

### Step 3: Update User Model

**File: `app/Models/User.php`**

Tambahkan trait `HasApiTokens` dari Passport:

```php
<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens; // Ganti dari Sanctum
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles; // Ganti HasApiTokens dari Sanctum ke Passport
    
    // ... rest of the model
}
```

### Step 4: Configure AuthServiceProvider

**File: `app/Providers/AuthServiceProvider.php`**

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        // Token lifetimes
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Enable implicit grant for trusted clients
        Passport::enableImplicitGrant();
    }
}
```

### Step 5: Update config/auth.php

**File: `config/auth.php`**

Ubah driver API dari `sanctum` ke `passport`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'passport', // Ubah dari 'sanctum' ke 'passport'
        'provider' => 'users',
    ],
],
```

---

## 🌐 Phase 3: Create OAuth Routes & Controllers

### Step 1: Create OAuth Controller

**File: `app/Http/Controllers/OAuth/OAuthController.php`**

```php
<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\Http\Controllers\AuthorizationController;

class OAuthController extends Controller
{
    /**
     * Show the authorization page
     */
    public function authorize(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            // Store the intended URL to redirect back after login
            session(['oauth_intended' => $request->fullUrl()]);
            return redirect()->route('login');
        }

        // Get client information
        $clientRepository = new ClientRepository();
        $client = $clientRepository->find($request->client_id);

        if (!$client) {
            return response()->json(['error' => 'Invalid client'], 400);
        }

        // Auto-approve for trusted clients
        $trustedClients = [
            config('oauth.clients.pustekno.id'),
            config('oauth.clients.todolist.id'),
        ];

        if (in_array($client->id, $trustedClients)) {
            // Auto-approve and redirect
            return $this->approveAuthorization($request);
        }

        // Show authorization page
        return view('oauth.authorize', [
            'client' => $client,
            'scopes' => $request->scope ?? '',
            'request' => $request,
        ]);
    }

    /**
     * Approve authorization
     */
    public function approveAuthorization(Request $request)
    {
        // This will be handled by Passport's AuthorizationController
        // We just need to forward the request
        $controller = app(AuthorizationController::class);
        return $controller->authorize($request);
    }

    /**
     * Get authenticated user info
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'address' => $user->address,
            'city' => $user->city,
            'province' => $user->province,
            'postal_code' => $user->postal_code,
            'country' => $user->country,
            'credit_balance' => $user->credit_balance,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'created_at' => $user->created_at,
        ]);
    }
}
```

### Step 2: Create OAuth Routes

**File: `routes/web.php`**

Tambahkan di bagian atas:

```php
use App\Http\Controllers\OAuth\OAuthController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;

// OAuth 2.0 Authorization Endpoints
Route::group(['middleware' => ['web']], function () {
    // Authorization endpoint (requires login)
    Route::get('/oauth/authorize', [OAuthController::class, 'authorize'])
        ->name('oauth.authorize');
    
    Route::post('/oauth/authorize', [AuthorizationController::class, 'approve'])
        ->middleware('auth')
        ->name('oauth.authorize.approve');
    
    Route::delete('/oauth/authorize', [AuthorizationController::class, 'deny'])
        ->middleware('auth')
        ->name('oauth.authorize.deny');
});

// OAuth 2.0 Token Endpoint (public)
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
    ->middleware('throttle')
    ->name('oauth.token');

// OAuth 2.0 User Info Endpoint (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('/oauth/user', [OAuthController::class, 'user'])
        ->name('oauth.user');
});
```

### Step 3: Create Authorization View

**File: `resources/views/oauth/authorize.blade.php`**

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorize Application - Nexia ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-6">
                <h2 class="text-3xl font-bold text-gray-900">Authorize Application</h2>
                <p class="mt-2 text-sm text-gray-600">
                    <strong>{{ $client->name }}</strong> would like to access your account
                </p>
            </div>

            <div class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">This application will be able to:</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>✓ Read your profile information</li>
                        <li>✓ Access your email address</li>
                        <li>✓ View your roles and permissions</li>
                    </ul>
                </div>
            </div>

            <div class="flex gap-4">
                <form method="POST" action="{{ route('oauth.authorize.approve') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $request->client_id }}">
                    <input type="hidden" name="redirect_uri" value="{{ $request->redirect_uri }}">
                    <input type="hidden" name="response_type" value="{{ $request->response_type }}">
                    <input type="hidden" name="scope" value="{{ $scopes }}">
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                        Authorize
                    </button>
                </form>

                <form method="POST" action="{{ route('oauth.authorize.deny') }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $request->client_id }}">
                    
                    <button type="submit" class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </form>
            </div>

            <p class="mt-6 text-xs text-center text-gray-500">
                You are logged in as <strong>{{ auth()->user()->email }}</strong>
            </p>
        </div>
    </div>
</body>
</html>
```

---

## 🔑 Phase 4: Create OAuth Clients

### Step 1: Create Client for pustekno.id

```bash
./vendor/bin/sail artisan passport:client --name="Pustekno Website"
```

**Pilih:**
- Which user ID should the client be assigned to? → **Enter** (skip)
- What should we name the client? → **Pustekno Website**
- Where should we redirect the request after authorization? → **https://pustekno.id/auth/callback**

**Simpan:**
- Client ID
- Client Secret

### Step 2: Create Client for todolist.pustekno.id

```bash
./vendor/bin/sail artisan passport:client --name="Todolist Website"
```

**Pilih:**
- Which user ID should the client be assigned to? → **Enter** (skip)
- What should we name the client? → **Todolist Website**
- Where should we redirect the request after authorization? → **https://todolist.pustekno.id/auth/callback**

**Simpan:**
- Client ID
- Client Secret

### Step 3: Store Client IDs in Config

**File: `config/oauth.php`** (create new file)

```php
<?php

return [
    'clients' => [
        'pustekno' => [
            'id' => env('OAUTH_PUSTEKNO_CLIENT_ID'),
            'secret' => env('OAUTH_PUSTEKNO_CLIENT_SECRET'),
            'redirect' => env('OAUTH_PUSTEKNO_REDIRECT_URI', 'https://pustekno.id/auth/callback'),
        ],
        'todolist' => [
            'id' => env('OAUTH_TODOLIST_CLIENT_ID'),
            'secret' => env('OAUTH_TODOLIST_CLIENT_SECRET'),
            'redirect' => env('OAUTH_TODOLIST_REDIRECT_URI', 'https://todolist.pustekno.id/auth/callback'),
        ],
    ],
];
```

### Step 4: Update .env

```env
# OAuth Clients
OAUTH_PUSTEKNO_CLIENT_ID=1
OAUTH_PUSTEKNO_CLIENT_SECRET=your-client-secret-here
OAUTH_PUSTEKNO_REDIRECT_URI=https://pustekno.id/auth/callback

OAUTH_TODOLIST_CLIENT_ID=2
OAUTH_TODOLIST_CLIENT_SECRET=your-client-secret-here
OAUTH_TODOLIST_REDIRECT_URI=https://todolist.pustekno.id/auth/callback
```

---

## 🔐 Phase 5: Integrate Google Login with OAuth

### Update Login Controller

**File: `app/Http/Controllers/Auth/GoogleController.php`**

Setelah Google login sukses, user bisa langsung di-redirect ke OAuth authorize:

```php
public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->user();
        
        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
            ]
        );

        Auth::login($user);

        // Check if there's an OAuth authorization pending
        if (session()->has('oauth_intended')) {
            $intendedUrl = session()->pull('oauth_intended');
            return redirect($intendedUrl);
        }

        return redirect()->intended('/dashboard');
    } catch (\Exception $e) {
        return redirect('/login')->with('error', 'Google login failed');
    }
}
```

---

## 📝 Phase 6: Update API Documentation

Create new documentation for OAuth endpoints:

**File: `OAUTH_API_DOCUMENTATION.md`**

---

## ✅ Testing Checklist

### Local Testing (Development)

- [ ] Install Passport
- [ ] Run migrations
- [ ] Create OAuth clients
- [ ] Test authorization flow
- [ ] Test token exchange
- [ ] Test user info endpoint
- [ ] Test Google login integration

### Production Deployment

- [ ] Update .env with production URLs
- [ ] Create OAuth clients for production
- [ ] Test HTTPS endpoints
- [ ] Configure CORS
- [ ] Test from pustekno.id
- [ ] Test from todolist.pustekno.id
- [ ] Monitor logs

---

## 🚀 Next Steps

1. **Run migrations** untuk create OAuth tables
2. **Install Passport** untuk generate keys
3. **Create OAuth clients** untuk pustekno.id dan todolist.pustekno.id
4. **Test locally** dengan Postman
5. **Deploy to production**

---

**Created:** 2026-02-04  
**Status:** In Progress  
**Current Phase:** Phase 2 - Configure Passport
