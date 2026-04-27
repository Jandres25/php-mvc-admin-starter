# Access Control and Session Flow

This document summarizes how authentication, session validation, and permission checks work in the project.

## Request guard flow

All requests pass through `public/index.php`, which boots the session and helpers. The `App\Core\Router` runs middleware before dispatching to a controller method:

1. `AuthMiddleware::handle()` — validates the session (authentication, timeout, anti-hijacking) and calls `refreshPermissionsIfStale()`.
2. `GuestMiddleware::handle()` — redirects already-authenticated users away from guest-only pages (login, forgot-password).
3. `PermissionMiddleware::handle($name)` — calls `AuthorizationService::hasPermissionByName()` for the given permission; returns a 403 view on failure.

Middleware is declared per route in `routes/web.php`:

```php
['method' => 'GET', 'path' => '/users', 'controller' => 'Users\User@index', 'middleware' => ['auth', 'perm:users']],
```

## Session security controls

Implemented in `app/core/helpers.php` (loaded once at entry point):

- Session cookie hardening (`httponly`, `SameSite=Lax`, `use_strict_mode`) — set in `public/index.php` before `session_start()`
- Inactivity timeout via `checkSessionTimeout()` (default: 24 hours)
- Anti-hijacking validation via `checkSessionSecurity()` (IP + User-Agent)

If validation fails, the user is redirected to login and receives a session message.

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
