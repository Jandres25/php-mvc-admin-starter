# Access Control and Session Flow

This document summarizes how authentication, session validation, remember-me, and permission checks work in the project.

## Request guard flow

All requests pass through `public/index.php`, which boots the session and helpers. The `App\Core\Router` runs middleware before dispatching to a controller method:

1. `AuthMiddleware::handle()` — if no active session, attempts auto-login via remember-me cookie; otherwise validates timeout and anti-hijacking, then calls `refreshPermissionsIfStale()`.
2. `GuestMiddleware::handle()` — attempts auto-login via remember-me cookie before checking session; redirects authenticated users away from guest-only pages (login, forgot-password).
3. `PermissionMiddleware::handle($name)` — calls `AuthorizationService::hasPermissionByName()` for the given permission; returns a 403 view on failure.

Middleware is declared per route in `routes/web.php`:

```php
['method' => 'GET', 'path' => '/users', 'controller' => 'Users\User@index', 'middleware' => ['auth', 'perm:users']],
```

## Session security controls

Implemented in `app/core/helpers.php` (loaded once at entry point):

- Session cookie hardening (`httponly`, `SameSite=Lax`, `use_strict_mode`) — set in `public/index.php` before `session_start()`
- Inactivity timeout via `checkSessionTimeout()` — reads `SESSION_LIFETIME` from `.env` (default: 1800 s)
- Anti-hijacking validation via `checkSessionSecurity()` (IP + User-Agent)

If validation fails and no valid remember-me cookie exists, the user is redirected to login and receives a session message.

## Remember Me

Implemented in `app/services/RememberMeService.php`. Controlled by three `.env` variables:

| Variable | Default | Description |
|---|---|---|
| `SESSION_LIFETIME` | `1800` | Seconds of inactivity before session expires |
| `REMEMBER_ME_LIFETIME` | `2592000` | Seconds the persistent cookie lives (30 days) |
| `REMEMBER_ME_COOKIE_NAME` | `remember_me` | Cookie name |

**How it works:**

1. User checks "Remember me" on login → `AuthController` calls `RememberMeService::issue($userId)`.
2. A 64-char hex token is generated with `random_bytes(32)`; its SHA-256 hash is stored in `users.remember_token` with an expiry in `users.remember_token_expires`. The plain token goes into the cookie.
3. On every request without an active session, `tryAutoLoginFromRememberCookie()` (helper) → `RememberMeService::attemptLogin()`:
   - Reads the cookie, hashes it, looks up `users` where `remember_token = hash AND expires > NOW() AND status = 1`.
   - On match: rebuilds the session identically to `AuthController::initSession()`, then **rotates** the token (new random token replaces both the DB row and the cookie) to mitigate cookie theft.
   - On no match: clears the stale cookie and returns `false`.
4. On logout: `RememberMeService::clear($userId)` NULLs the DB columns and expires the cookie before `session_destroy()`.

**Security properties:**
- Token never stored in plain text in DB — only SHA-256 hash.
- Token rotated on every successful auto-login.
- Cookie: `HttpOnly` (no XSS), `SameSite=Lax` (mitigates CSRF), `Secure` flag set when HTTPS is detected.
- Deactivated users (`status = 0`) cannot auto-login.
- Expired tokens ignored via `NOW()` comparison in the query.

**DB columns added to `users`:**

```sql
remember_token         CHAR(64)  NULL DEFAULT NULL
remember_token_expires DATETIME  NULL DEFAULT NULL
```

## Permission model

Core service: `App\Services\AuthorizationService`.

- `hasPermissionByName($userId, $permissionName)` is the standard check for menu/page/action gating.
- `isAdmin($userId)` is memoized per request to reduce repeated DB queries.
- If `$_SESSION['user_permissions']` exists, permission checks are resolved from session cache first.

## Permission cache refresh contract

Permission changes are cached in session and refreshed when stale.

- Session value: `$_SESSION['permissions_ts']`
- DB value: `users.permissions_updated_at`
- Refresh trigger: `refreshPermissionsIfStale()` (called by `AuthMiddleware` on every authenticated request) compares both values

After changing any user permission assignments, update the user's timestamp through the model/service flow so the next page load refreshes their cache.

## Practical pattern

For any new protected route, declare middleware in `routes/web.php`:

```php
// Requires authentication only
['method' => 'GET', 'path' => '/profile', 'controller' => 'Users\User@profile', 'middleware' => ['auth']],

// Requires authentication + named permission
['method' => 'GET', 'path' => '/products', 'controller' => 'Products\Product@index', 'middleware' => ['auth', 'perm:products']],
```

Within the controller method, additional inline checks are available via the base `Controller` helpers:

```php
$this->csrfCheck();          // validates CSRF; returns JSON 403 for AJAX or redirects
$this->requirePermission('products'); // re-checks permission and renders 403 view on failure
```
