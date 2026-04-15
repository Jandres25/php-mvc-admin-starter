# Access Control and Session Flow

This document summarizes how authentication, session validation, and permission checks work in the project.

## Request guard flow

Protected pages load `views/layouts/session.php` first, then call:

1. `requireLogin()` to validate authentication, timeout, and session security.
2. `requirePermission('permission_name')` when access must be restricted by permission.

`requireLogin()` also calls `refreshPermissionsIfStale()` on every request to keep the session permission cache in sync.

## Session security controls

Implemented in `session.php`:

- Session cookie hardening (`httponly`, `SameSite=Lax`, `use_strict_mode`)
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
- Refresh trigger: `refreshPermissionsIfStale()` compares both values

After changing any user permission assignments, update the user's timestamp through the model/service flow so the next page load refreshes their cache.

## Practical pattern

For any new protected endpoint:

```php
require_once __DIR__ . '/../../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

requireLogin();
requirePermission('users'); // Replace with your permission
```
