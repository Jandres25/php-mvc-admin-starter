# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP MVC admin starter with authentication, user management, and permission-based access control. Uses AdminLTE 3 UI, PDO/MySQL, and a custom PSR-4 autoloader (no Composer).

> **Note for AI Agents:** See `docs/AI_SETUP.md` for details on how this project orchestrates AI skills and MCP servers. Custom skills for interacting with this repository should be placed in `.claude/skills/`.

## Setup

```bash
# 1. Import database schema and seed data
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeder.sql

# 2. Configure environment
cp .env.example .env
# Edit .env: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, TIMEZONE

# 3. Create upload directory and set write permissions
mkdir -p public/uploads/users
cp public/img/user_default.jpg public/uploads/users/
chmod 777 public/uploads/users/
```

> **Note:** Apache runs as `daemon` on LAMPP. The uploads directory must be world-writable (`777`) or owned by the web server user, otherwise `move_uploaded_file()` silently fails.

**Requirements:** PHP 8.2+ with PDO and GD extensions, Apache/Nginx, MariaDB/MySQL.

**Default credentials:** `admin@sistema.com` / `admin123`

**Seed dataset reference:** see `docs/SEEDING.md` for all seeded users, permission matrix, and rerun behavior.

**Local URL:** `http://localhost/php-mvc-admin-starter/`

**Current release tag:** `3.1.0`

## No Build Process

This is a pure PHP application. There are no npm, Composer, Makefile, or test suite commands. Frontend assets (CSS/JS) are static files in `public/`. There is no formal test suite — testing is done manually via browser.

## Architecture

### Request Flow

All HTTP requests are routed through `public/index.php` (Front Controller) via Apache rewriting. The `App\Core\Router` matches the URI and HTTP method against `routes/web.php`, runs registered middleware, then dispatches to the appropriate controller method.

Clean URL examples:
- `/` or `/dashboard` — dashboard
- `/login` — login page
- `/users/create` — create user form
- `/users` (POST) — store new user
- `/users/5/edit` — edit user

**Entry point:** `public/index.php` starts the session, loads `app/config/config.php` and `app/core/helpers.php`, then instantiates the router.

**Helpers:** `app/core/helpers.php` defines the global functions `isAuthenticated()`, `checkSessionTimeout()`, `checkSessionSecurity()`, `getCurrentUser()`, `refreshPermissionsIfStale()`, `generateCSRFToken()`, `verifyCSRFToken()`, and `regenerateCSRFToken()`. It is loaded once at the entry point.

### MVC Structure

```
app/
├── config/       # Bootstrap: autoloader, .env loader, DB singleton, config array
├── controllers/  # Feature controllers (auth/, users/, permissions/, dashboard/)
├── core/         # Controller.php, Model.php, Router.php, AssetRegistry.php, helpers.php
├── middleware/   # AuthMiddleware, GuestMiddleware, PermissionMiddleware
├── models/       # App\Models
└── services/     # App\Services (AuthorizationService, ImageService, MailService)
routes/           # web.php — all route definitions
database/         # schema.sql and seeder.sql
views/            # PHP templates; layouts/header.php pulls in all CSS/JS
public/           # Static assets (lib/, core/, plugins/, modules/) + index.php
libs/             # Vendored libraries (TCPDF, PHPMailer)
```

### Custom Autoloader

`app/config/autoload.php` implements PSR-4-like loading: namespace separators map to lowercase directory paths relative to the project root, with explicit support for `App\...` classes under `app/`. Register once in `app/config/config.php`.

### Database Connection

`app/config/Connection.php` exposes a singleton `Connection::getInstance()` returning a configured PDO object. All queries use prepared statements.

### Permissions

`app/services/AuthorizationService.php` checks whether a user has a named permission (e.g., `'users'`, `'permissions'`, `'admin'`). Use `$authService->hasPermissionByName($userId, 'permission_name')` to gate access. Navigation items and action buttons are conditionally rendered based on these checks.

Permission names are checked against `$_SESSION['user_permissions']` (cached at login). The cache is automatically refreshed by `refreshPermissionsIfStale()` in `helpers.php` — called on every request by `AuthMiddleware` — which compares `$_SESSION['permissions_ts']` against the `permissions_updated_at` column in `users`. Call `$userModel->updatePermissionsTimestamp($userId)` after any permission change so the affected user's cache is regenerated on their next page load.

`AuthorizationService::isAdmin()` is memoized per-request via `static array $adminCache` — safe to call multiple times in the same request without extra DB queries.

**Middleware:** Route-level protection is declared in `routes/web.php` via the `middleware` key:
- `'auth'` — `AuthMiddleware`: validates session, timeout, anti-hijacking, then calls `refreshPermissionsIfStale()`.
- `'guest'` — `GuestMiddleware`: redirects authenticated users away from login/forgot-password pages.
- `'perm:NAME'` — `PermissionMiddleware`: checks `AuthorizationService::hasPermissionByName()` for the given permission name; returns 403 view on failure.

### AJAX Pattern

Controller methods that handle AJAX calls return JSON via `$this->jsonResponse($data)`. CSRF tokens are generated via `generateCSRFToken()` (global in `helpers.php`) and validated with `$this->csrfCheck()` (calls `verifyCSRFToken()` internally); call `regenerateCSRFToken()` after every successful POST. AJAX routes are declared in `routes/web.php` like any other route and protected by the same middleware.

When an AJAX action is followed by `location.reload()` in JS, set `$_SESSION['message']` and `$_SESSION['icon']` in the PHP endpoint before echoing the JSON — `messages.php` will pick them up and fire the SweetAlert2 toast on the reloaded page.

Additional reference: `docs/ACCESS_CONTROL.md` and `docs/AJAX_AND_MODULES.md`.

### Frontend Modules

Feature-specific JS lives in `public/js/modules/{feature}/`. Core utilities (AJAX helpers, DataTables setup, jQuery Validate config) are in `public/js/core/`. All third-party libraries are already bundled in `public/js/lib/` and `public/css/lib/`.

**Loading order:** `footer.php` loads Bootstrap and AdminLTE, then conditional plugins (`$plugins`), then `public/js/core/ui-components.js` (auto-initializes Select2 and tooltips via `ComponentUtils.initAll()`), then `$module_scripts`. Always register page-specific JS via `$module_scripts = ['feature/file']` at the top of the view — never use inline `<script>` blocks before `footer.php`, as Bootstrap plugins (e.g. `.tab()`) will not yet be available.

`public/css/core/ui-components.css` is loaded globally in `header.php` and requires no per-page declaration.

Similarly, register page-specific CSS via `$module_styles = ['feature/file']` at the top of the view.

**Conditional plugin loading:** Third-party plugins (DataTables, Select2, jQuery Validate) are loaded on demand via `$plugins` passed to `Controller::render()`. Asset maps are centralized in `App\Core\AssetRegistry` and consumed by layouts. Available plugins: `datatables`, `datatables-export`, `select2`, `validate`, `chart`. Example (inside a controller method):

```php
$this->render('users/index', $data, ['datatables', 'datatables-export'], ['users/index-users']);
```

**Form validation:** Pass `['select2', 'validate']` as the plugins argument. `public/js/core/common-validate.js` (loaded automatically with `validate`) configures jQuery Validate globally for Bootstrap 4 — `errorPlacement` inside `.form-group`, `highlight`/`unhighlight`, `onkeyup: false`. Each module calls `$('#form').validate({ rules, messages, submitHandler })` with its own rules. For uniqueness checks against the DB, use `remote` rules pointing to `/users/check-email` or `/users/check-document`.

**Auth standalone pages:** `views/auth/*.php` do not use `layouts/footer.php`, so they must include validation assets manually (`jquery.validate.min.js`, `additional-methods.min.js`, `common-validate.js`) before loading their module script. Keep auth input markup as `form-group > input-group` so `.invalid-feedback` placement from `common-validate.js` renders correctly.

## Coding Conventions

- **Namespaces:** use `App\Controllers\*`, `App\Models\*`, `App\Services\*`, and `App\Core\*` consistently.
- **CSRF:** Generate token with `generateCSRFToken()`, validate via `$this->csrfCheck()` (controller helper that calls `verifyCSRFToken()` and returns JSON 403 for AJAX or redirects for regular POSTs). Call `regenerateCSRFToken()` after every successful POST. All helpers live in `app/core/helpers.php`.
- **Input sanitization:** Use `trim()` at the model layer (`sanitizeData()`). Apply `htmlspecialchars()` exclusively at the view layer on all output — never in the model or before storing in the DB.
- **Passwords:** Always `password_hash($pass, PASSWORD_DEFAULT)` / `password_verify()`.
- **Images:** Route all upload/resize/delete through `ImageService`.
- **JS:** ES6+ with JSDoc comments. Use `SweetAlert2` for confirmations, `DataTables` for lists, `Select2` for dropdowns.
- **Select2 auto-init:** `ComponentUtils.initAll()` (called automatically on `DOMContentLoaded` by `ui-components.js`) initializes every `.select2` element on the page. Do not call `initializeSelect2()` manually in module scripts unless extra options are needed.
- **Select2 in modals:** When a Select2 element lives inside a Bootstrap modal, call `initializeSelect2('#selectId', { dropdownParent: $('#modalId') })` explicitly. `ComponentUtils.initAll()` cannot apply `dropdownParent` globally, so modal selects need targeted initialization. Without `dropdownParent`, Bootstrap's focus trap closes the dropdown immediately. Populate options from PHP `<option>` elements (server-side) rather than AJAX to avoid re-initialization conflicts.
- **AdminLTE cards:** `card-outline card-{color}` classes go on the outer `div.card`, **never** on `div.card-header`. Adding them to the header instead of the card silently breaks the colored left border style.
- **Commits:** Follow Conventional Commits (`feat:`, `fix:`, `docs:`, etc.).
