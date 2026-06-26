# 🔐 Laravel Security Best Practices

A curated list of essential cybersecurity practices for your Laravel project.

---

## 1. 🛡️ CSRF Protection (Cross-Site Request Forgery)

**What it is:** CSRF attacks trick authenticated users into submitting malicious requests. Laravel generates a unique token per session to verify that form submissions are legitimate.

**Laravel handles this automatically** via the `VerifyCsrfToken` middleware, but you must include the token in all forms.

```blade
{{-- Blade Form --}}
<form method="POST" action="/admin/login">
    @csrf
    {{-- fields here --}}
</form>
```

```javascript
// For AJAX requests (e.g., Axios), Laravel's frontend scaffold sets this automatically via the meta tag
<meta name="csrf-token" content="{{ csrf_token() }}">
```

> [!IMPORTANT]
> Never exclude routes from CSRF protection unless you are handling webhooks from external services and have an alternative verification strategy (e.g., signature validation).

---

## 2. 🔒 Authentication & Password Hashing

**What it is:** Never store plain-text passwords. Laravel uses **bcrypt** (via `Hash::make()`) by default, which is a one-way hashing algorithm with salting.

```php
// Storing a password
use Illuminate\Support\Facades\Hash;

$user->password = Hash::make($request->password);

// Verifying a password
if (Hash::check($request->password, $user->password)) {
    // correct password
}
```

> [!TIP]
> Use Laravel Breeze, Jetstream, or Fortify for battle-tested authentication scaffolding instead of rolling your own.

---

## 3. 🚦 Authorization with Gates & Policies

**What it is:** Authentication verifies **who you are**; Authorization verifies **what you can do**. Without it, users could access each other's data or perform admin-only actions.

```php
// Define a Policy (app/Policies/PurchaseRequestPolicy.php)
public function update(User $user, PurchaseRequest $pr): bool
{
    return $user->id === $pr->created_by;
}

// Enforce in Controller
public function update(Request $request, PurchaseRequest $pr)
{
    $this->authorize('update', $pr); // throws 403 if unauthorized
    // ...
}

// Enforce in Blade
@can('update', $pr)
    <a href="{{ route('pr.edit', $pr) }}">Edit</a>
@endcan
```

---

## 4. 🧹 Input Validation & Sanitization

**What it is:** Never trust user input. Validate all incoming data before processing. This prevents malformed data, business logic bypasses, and many injection attacks.

```php
// In a Form Request (app/Http/Requests/StorePrRequest.php)
public function rules(): array
{
    return [
        'title'       => ['required', 'string', 'max:255'],
        'quantity'    => ['required', 'integer', 'min:1'],
        'amount'      => ['required', 'numeric', 'min:0'],
        'uploaded_file' => ['nullable', 'file', 'mimes:pdf,docx', 'max:2048'],
    ];
}
```

> [!WARNING]
> **Never** use `$request->all()` directly in mass assignments without a strict whitelist. Always use `$request->validated()` from Form Requests.

---

## 5. 💉 SQL Injection Prevention

**What it is:** SQL injection occurs when user-supplied input is embedded directly into raw SQL queries, allowing attackers to manipulate the database.

Laravel's **Eloquent ORM** and **Query Builder** use PDO prepared statements by default, which automatically prevent SQL injection.

```php
// ✅ SAFE — uses prepared statements
User::where('email', $request->email)->first();

// ✅ SAFE — Query Builder with bindings
DB::table('users')->where('email', $request->email)->first();

// ❌ DANGEROUS — never do this
DB::select("SELECT * FROM users WHERE email = '{$request->email}'");

// ✅ SAFE — if you must use raw SQL, use bindings
DB::select('SELECT * FROM users WHERE email = ?', [$request->email]);
```

---

## 6. 🔑 Mass Assignment Protection

**What it is:** Mass assignment vulnerabilities occur when a user submits extra fields (e.g., `is_admin=1`) that get written to the database unintentionally.

```php
// In your Model — use $fillable (whitelist) or $guarded (blacklist)
class User extends Model
{
    // ✅ Whitelist approach (recommended)
    protected $fillable = ['name', 'email', 'password'];

    // OR blacklist approach
    // protected $guarded = ['is_admin', 'role'];
}

// In Controller — always use validated() not all()
User::create($request->validated());
```

---

## 7. ⏱️ Rate Limiting (Brute-Force Protection)

**What it is:** Without rate limiting, attackers can attempt thousands of login attempts per second (brute force). Rate limiting throttles requests from a single IP or user.

```php
// In routes/web.php or RouteServiceProvider
Route::middleware('throttle:5,1') // 5 attempts per 1 minute
    ->post('/admin/login', [AdminAuthController::class, 'store']);

// Custom rate limiter in App\Providers\RouteServiceProvider.php
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

> [!TIP]
> Laravel's built-in `auth` rate limiter (used by Fortify) also locks by email + IP combination, which is more robust.

---

## 8. 🗂️ Secure File Uploads

**What it is:** Malicious users can upload PHP scripts disguised as images or documents to execute arbitrary code on your server.

```php
// Validate file type strictly
$request->validate([
    'document' => ['required', 'file', 'mimes:pdf,xlsx,docx', 'max:5120'],
]);

// Store OUTSIDE the public directory (use storage disk)
$path = $request->file('document')->store('procurement/documents', 'local');
// Files stored in storage/app/procurement/ are NOT web-accessible

// Generate a temporary signed URL for legitimate downloads
return Storage::temporaryUrl($path, now()->addMinutes(30));
```

> [!CAUTION]
> Never store uploaded files inside the `public/` directory directly. Always use `storage/app/` with controlled access via `Storage::download()` or signed URLs.

---

## 9. 🔐 Sensitive Data Encryption

**What it is:** Sensitive fields (e.g., secret keys, tokens, PII) should be encrypted at rest so that even if the database is compromised, the data is unreadable.

```php
// In your Model — cast a field to encrypted
protected $casts = [
    'api_token'      => 'encrypted',
    'national_id'    => 'encrypted',
];

// Manual encryption/decryption
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString('sensitive-value');
$decrypted = Crypt::decryptString($encrypted);
```

> [!IMPORTANT]
> Your `APP_KEY` in `.env` is the encryption key. **Never commit `.env` to version control.** Back up the key securely — losing it means losing access to all encrypted data.

---

## 10. 🛑 Secure HTTP Headers

**What it is:** HTTP response headers can instruct browsers to enforce security policies (e.g., no clickjacking, no MIME sniffing, enforce HTTPS).

Add a middleware to append security headers on every response:

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);

    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=()');

    return $response;
}
```

Register it in `bootstrap/app.php` (Laravel 11) or `Http/Kernel.php` (Laravel 10 and below).

---

## 11. 🧩 XSS Prevention (Cross-Site Scripting)

**What it is:** XSS attacks inject malicious scripts into web pages viewed by other users. Blade's `{{ }}` syntax auto-escapes output, but `{!! !!}` does not.

```blade
{{-- ✅ SAFE — auto-escaped --}}
{{ $user->name }}

{{-- ❌ DANGEROUS — only use when you fully trust the content --}}
{!! $user->bio !!}

{{-- ✅ If you must render HTML, sanitize first --}}
{!! strip_tags($user->bio, '<b><i><p>') !!}
```

> [!WARNING]
> Avoid using `{!! !!}` for any user-controlled content. If you need to render rich text, use a library like [HTMLPurifier](http://htmlpurifier.org/) to whitelist safe HTML.

---

## 12. 🔗 Secure Session Management

**What it is:** Sessions store authentication state. Misconfigured sessions can lead to session hijacking or fixation attacks.

```php
// config/session.php — recommended settings
'secure'    => env('SESSION_SECURE_COOKIE', true),  // HTTPS only
'http_only' => true,                                 // Not accessible via JS
'same_site' => 'lax',                               // CSRF protection
'lifetime'  => 120,                                  // 2 hours

// Regenerate session ID after login (prevents session fixation)
// Laravel's Auth::login() does this automatically, but if manual:
$request->session()->regenerate();

// On logout, invalidate the session
$request->session()->invalidate();
$request->session()->regenerateToken();
```

---

## 13. 📋 Logging & Monitoring (Audit Trail)

**What it is:** Security incidents need to be detectable. Log authentication events, permission failures, and sensitive actions so you have an audit trail.

```php
use Illuminate\Support\Facades\Log;

// Log a failed login attempt
Log::warning('Failed login attempt', [
    'email' => $request->email,
    'ip'    => $request->ip(),
    'at'    => now(),
]);

// Log a sensitive action
Log::info('Purchase Request approved', [
    'pr_id'      => $pr->id,
    'approved_by'=> auth()->id(),
    'at'         => now(),
]);
```

> [!TIP]
> Consider using a dedicated audit log package like [owen-it/laravel-auditing](https://laravel-auditing.com/) which automatically logs model changes (create/update/delete) with the user who made them.

---

## 14. 📦 Dependency Security

**What it is:** Outdated or vulnerable packages are a common attack vector. Regularly audit your dependencies.

```bash
# Check for known vulnerabilities in PHP packages
composer audit

# Check for outdated packages
composer outdated

# Update packages
composer update

# For Node/JS dependencies
npm audit
npm audit fix
```

> [!IMPORTANT]
> Subscribe to [Laravel security advisories](https://laravel.com/docs/security) and run `composer audit` as part of your CI/CD pipeline.

---

## 15. 🌍 Environment Configuration Security

**What it is:** Your `.env` file contains secrets (DB credentials, API keys, APP_KEY). Exposure of this file is catastrophic.

```bash
# .gitignore — ensure .env is excluded
.env
.env.*
!.env.example

# Verify no sensitive data in .env.example
APP_KEY=           # leave blank
DB_PASSWORD=       # leave blank
MAIL_PASSWORD=     # leave blank
```

```php
// config/app.php — disable debug mode in production
'debug' => env('APP_DEBUG', false), // Never set to true in production

// In .env for production
APP_ENV=production
APP_DEBUG=false
```

> [!CAUTION]
> `APP_DEBUG=true` in production exposes full stack traces, database queries, environment variables, and source file paths — a goldmine for attackers.

---

## 16. 🔏 Signed URLs for Sensitive Actions

**What it is:** For sensitive one-time links (e.g., email verification, password reset, document download), use cryptographically signed URLs that expire.

```php
// Generate a signed URL (expires in 30 minutes)
$url = URL::temporarySignedRoute(
    'document.download',
    now()->addMinutes(30),
    ['document' => $document->id]
);

// In the route/controller, verify the signature
Route::get('/document/{document}/download', function (Request $request, Document $document) {
    if (! $request->hasValidSignature()) {
        abort(401, 'Invalid or expired link.');
    }
    return Storage::download($document->path);
})->name('document.download');
```

---

## Quick Reference Summary

| Practice | Risk Mitigated | Laravel Tool |
|---|---|---|
| CSRF Token | CSRF attacks | `@csrf`, `VerifyCsrfToken` middleware |
| Password Hashing | Credential theft | `Hash::make()` |
| Gates & Policies | Unauthorized access | `Gate`, `Policy`, `@can` |
| Input Validation | Injection, logic bypass | Form Requests, `validated()` |
| Eloquent ORM | SQL Injection | Query Builder / Eloquent |
| `$fillable` | Mass assignment | Model `$fillable` |
| Rate Limiting | Brute force | `throttle` middleware |
| Secure File Upload | RCE via uploads | `mimes` validation, `storage` disk |
| Encryption | Data breach | `Crypt`, `encrypted` cast |
| Security Headers | XSS, Clickjacking | Custom middleware |
| Blade `{{ }}` escaping | XSS | Blade templating |
| Session config | Session hijacking | `config/session.php` |
| Logging | Incident detection | `Log` facade, laravel-auditing |
| `composer audit` | Vulnerable packages | Composer CLI |
| `.env` protection | Secret exposure | `.gitignore`, `APP_DEBUG=false` |
| Signed URLs | Unauthorized downloads | `URL::temporarySignedRoute()` |
