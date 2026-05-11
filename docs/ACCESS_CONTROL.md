# Access Control and Session Flow

This document summarizes how authentication, session validation, remember-me, and permission checks work in the project.

## Request guard flow

All requests pass through `public/index.php`, which boots the session and CSRF helpers. The `App\Core\Router` runs middleware before dispatching to a controller method:

1. `AuthMiddleware::handle()` — if no active session, attempts auto-login via remember-me cookie; otherwise validates timeout and anti-hijacking, then calls `Auth::refreshPermissionsIfStale()`.
2. `GuestMiddleware::handle()` — attempts auto-login via remember-me cookie before checking session; redirects authenticated users away from guest-only pages (login, forgot-password).
3. `PermissionMiddleware::handle($name)` — calls `Auth::hasPermission($name)`; returns a 403 view on failure.

Middleware is declared per route in `routes/web.php`:

```php
['method' => 'GET', 'path' => '/users', 'controller' => 'Users\User@index', 'middleware' => ['auth', 'perm:users']],
```

## Auth hub — `App\Core\Auth`

All authentication, session, and remember-me concerns are centralised in the static class `App\Core\Auth`. No instantiation needed.

| Method | Description |
|---|---|
| `Auth::check()` | Returns `true` if a valid authenticated session exists |
| `Auth::id()` | Returns the current user's ID or `null` |
| `Auth::user()` | Returns current user array (`id`, `name`, `email`, `position`, `image`) or `null` |
| `Auth::isAdmin()` | Returns `true` if the current user's position is Administrator |
| `Auth::hasPermission(string $name)` | Checks session cache; `'*'` grants all (admins) |
| `Auth::permissions()` | Returns the full permission name array from session |
| `Auth::login(array $user, array $permNames)` | Regenerates session ID, writes all session keys, caches permissions |
| `Auth::logout(int $userId)` | Clears remember-me cookie, destroys the session |
| `Auth::checkTimeout()` | Destroys session and returns `false` if idle > `SESSION_LIFETIME` |
| `Auth::checkSecurity()` | Destroys session and returns `false` if IP or User-Agent changed |
| `Auth::refreshPermissionsIfStale()` | Reloads permission cache from DB if `permissions_updated_at` is newer than session timestamp |
| `Auth::issueRememberCookie(int $userId)` | Generates token, stores SHA-256 hash in DB, sets cookie |
| `Auth::attemptRememberLogin()` | Validates cookie token, auto-logs in, rotates token |
| `Auth::clearRememberCookie(int $userId)` | NULLs DB token, expires cookie |

## Session security controls

Implemented in `App\Core\Auth` (called by `AuthMiddleware`):

- Session cookie hardening (`httponly`, `SameSite=Lax`, `use_strict_mode`) — set in `public/index.php` before `session_start()`
- Inactivity timeout via `Auth::checkTimeout()` — reads `SESSION_LIFETIME` from `.env` (default: 1800 s)
- Anti-hijacking validation via `Auth::checkSecurity()` (IP + User-Agent)

If validation fails and no valid remember-me cookie exists, the user is redirected to login and receives a session message.

## Remember Me

Implemented in `App\Core\Auth`. Controlled by three `.env` variables:

| Variable | Default | Description |
|---|---|---|
| `SESSION_LIFETIME` | `1800` | Seconds of inactivity before session expires |
| `REMEMBER_ME_LIFETIME` | `2592000` | Seconds the persistent cookie lives (30 days) |
| `REMEMBER_ME_COOKIE_NAME` | `remember_me` | Cookie name |

**How it works:**

1. User checks "Remember me" on login → `AuthController` calls `Auth::issueRememberCookie($userId)`.
2. A 64-char hex token is generated with `random_bytes(32)`; its SHA-256 hash is stored in `users.remember_token` with an expiry in `users.remember_token_expires`. The plain token goes into the cookie.
3. On every request without an active session, `AuthMiddleware` calls `Auth::attemptRememberLogin()`:
   - Reads the cookie, hashes it, looks up `users` where `remember_token = hash AND expires > NOW() AND status = 1`.
   - On match: calls `Auth::login()` to rebuild the session, then **rotates** the token (new random token replaces both the DB row and the cookie) to mitigate cookie theft.
   - On no match: clears the stale cookie and returns `false`.
4. On logout: `Auth::logout($userId)` NULLs the DB columns, expires the cookie, then destroys the session.

**Security properties:**
- Token never stored in plain text in DB — only SHA-256 hash.
- Token rotated on every successful auto-login.
- Cookie: `HttpOnly` (no XSS), `SameSite=Lax` (mitigates CSRF), `Secure` flag set when HTTPS is detected.
- Deactivated users (`status = 0`) cannot auto-login.
- Expired tokens ignored via `NOW()` comparison in the query.

**DB columns in `users`:**

```sql
remember_token         CHAR(64)  NULL DEFAULT NULL
remember_token_expires DATETIME  NULL DEFAULT NULL
```

## Permission model

Permissions are cached in session at login and checked via `Auth::hasPermission(string $name)`.

- Administrators always get `['*']` in their session cache — `hasPermission()` returns `true` for any name.
- Non-admins get an array of permission name strings loaded from `user_permissions` at login.
- `Auth::hasPermission()` reads only from session — no DB query per call.

## Permission cache refresh contract

Permission changes are cached in session and refreshed when stale.

- Session value: `$_SESSION['permissions_ts']`
- DB value: `users.permissions_updated_at`
- Refresh trigger: `Auth::refreshPermissionsIfStale()` — called by `AuthMiddleware` on every authenticated request — compares both values and reloads from DB via `Permission::getByUserId()` if the DB timestamp is newer.

After changing any user permission assignments, call `$userModel->updatePermissionsTimestamp($userId)` so the affected user's cache is regenerated on their next page load.

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
$this->csrfCheck();               // validates CSRF; returns JSON 403 for AJAX or redirects
$this->requirePermission('products'); // re-checks permission and renders 403 view on failure
```

Views and layouts can gate nav items and action buttons directly:

```php
<?php if (\App\Core\Auth::hasPermission('users')): ?>
    <a href="<?= URL ?>users">Users</a>
<?php endif; ?>
```
