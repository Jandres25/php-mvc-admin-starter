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

**Local URL:** `http://localhost/php-mvc-admin-starter/`

## No Build Process

This is a pure PHP application. There are no npm, Composer, Makefile, or test suite commands. Frontend assets (CSS/JS) are static files in `public/`. There is no formal test suite — testing is done manually via browser.

## Architecture

### Request Flow

There is no router. Controllers are accessed directly via URL paths:
- `/index.php` — dashboard
- `/controllers/auth/login.php` — login page
- `/controllers/users/create_user.php` — create user action

`views/layouts/session.php` is included at the top of every protected page. It loads the `.env`, validates the session, and defines `requireLogin()` and `getCurrentUser()` global functions.

### MVC Structure

```
config/         # Bootstrap: autoloader, env loader, DB singleton, config array
database/       # Schema (schema.sql) and seed data (seeder.sql)
models/         # Data access + sanitization (User.php, Permission.php)
services/       # Business logic (AuthorizationService.php, ImageService.php)
controllers/    # One file per action (e.g., create_user.php, login.php)
views/          # PHP templates; layouts/header.php pulls in all CSS/JS
public/         # Static assets organized as lib/, core/, plugins/, modules/
libs/           # Vendored libraries (TCPDF)
```

### Custom Autoloader

`config/autoload.php` implements PSR-4: namespace separators map to lowercase directory paths relative to the project root. Register once in `config/config.php`. Example: `Controllers\Users\UserController` → `controllers/users/UserController.php`.

### Database Connection

`config/connection.php` exposes a singleton `Connection::getInstance()` returning a configured PDO object. All queries use prepared statements.

### Permissions

`services/AuthorizationService.php` checks whether a user has a named permission (e.g., `'users'`, `'permissions'`, `'admin'`). Use `$authService->hasPermissionByName($userId, 'permission_name')` to gate access. Navigation items and action buttons are conditionally rendered based on these checks.

Permission names are checked against `$_SESSION['user_permissions']` (cached at login). The cache is automatically refreshed by `refreshPermissionsIfStale()` in `session.php` — called on every page load — which compares `$_SESSION['permissions_ts']` against the `permissions_updated_at` column in `users`. Call `$userModel->updatePermissionsTimestamp($userId)` after any permission change so the affected user's cache is regenerated on their next page load.

`AuthorizationService::isAdmin()` is memoized per-request via `static array $adminCache` — safe to call multiple times in the same request without extra DB queries.

### AJAX Pattern

Action controllers (e.g., `create_user.php`, `ajax_change_password.php`) return JSON responses consumed by JS modules in `public/js/modules/`. CSRF tokens are generated via `generateCSRFToken()` (global in `session.php`) and validated with `verifyCSRFToken()`; call `regenerateCSRFToken()` after every successful POST.

When an AJAX action is followed by `location.reload()` in JS, set `$_SESSION['message']` and `$_SESSION['icon']` in the PHP endpoint before echoing the JSON — `messages.php` will pick them up and fire the SweetAlert2 toast on the reloaded page.

### Frontend Modules

Feature-specific JS lives in `public/js/modules/{feature}/`. Core utilities (AJAX helpers, DataTables setup, jQuery Validate config) are in `public/js/core/`. All third-party libraries are already bundled in `public/js/lib/` and `public/css/lib/`.

**Loading order:** `footer.php` loads Bootstrap and AdminLTE, then conditional plugins (`$plugins`), then `public/js/core/ui-components.js` (auto-initializes Select2 and tooltips via `ComponentUtils.initAll()`), then `$module_scripts`. Always register page-specific JS via `$module_scripts = ['feature/file']` at the top of the view — never use inline `<script>` blocks before `footer.php`, as Bootstrap plugins (e.g. `.tab()`) will not yet be available.

`public/css/core/ui-components.css` is loaded globally in `header.php` and requires no per-page declaration.

Similarly, register page-specific CSS via `$module_styles = ['feature/file']` at the top of the view.

**Conditional plugin loading:** Third-party plugins (DataTables, Select2, jQuery Validate) are loaded on demand via `$plugins` declared before `header.php`. Available plugins: `datatables`, `datatables-export`, `select2`, `validate`, `chart`. Example:

```php
$plugins = ['datatables', 'datatables-export'];
$module_scripts = ['users/index-users'];
require_once '../layouts/header.php';
```

**Form validation:** Use `$plugins = ['select2', 'validate']` on forms. `public/js/core/common-validate.js` (loaded automatically with `validate`) configures jQuery Validate globally for Bootstrap 4 — `errorPlacement` inside `.form-group`, `highlight`/`unhighlight`, `onkeyup: false`. Each module calls `$('#form').validate({ rules, messages, submitHandler })` with its own rules. For uniqueness checks against the DB, use `remote` rules pointing to `controllers/users/check_email.php` or `check_document.php`.

## Coding Conventions

- **Namespaces:** `Controllers\Auth`, `Controllers\Users`, `Controllers\Permissions`, `Models`, `Services` — match directory structure in lowercase.
- **CSRF:** Generate token with `generateCSRFToken()`, validate with `verifyCSRFToken()` (global functions in `session.php`). Call `regenerateCSRFToken()` after every successful POST validation to prevent replay attacks.
- **Input sanitization:** Use `trim()` at the model layer (`sanitizeData()`). Apply `htmlspecialchars()` exclusively at the view layer on all output — never in the model or before storing in the DB.
- **Passwords:** Always `password_hash($pass, PASSWORD_DEFAULT)` / `password_verify()`.
- **Images:** Route all upload/resize/delete through `ImageService`.
- **JS:** ES6+ with JSDoc comments. Use `SweetAlert2` for confirmations, `DataTables` for lists, `Select2` for dropdowns.
- **Select2 auto-init:** `ComponentUtils.initAll()` (called automatically on `DOMContentLoaded` by `ui-components.js`) initializes every `.select2` element on the page. Do not call `initializeSelect2()` manually in module scripts unless extra options are needed.
- **Select2 in modals:** When a Select2 element lives inside a Bootstrap modal, call `initializeSelect2('#selectId', { dropdownParent: $('#modalId') })` explicitly. `ComponentUtils.initAll()` cannot apply `dropdownParent` globally, so modal selects need targeted initialization. Without `dropdownParent`, Bootstrap's focus trap closes the dropdown immediately. Populate options from PHP `<option>` elements (server-side) rather than AJAX to avoid re-initialization conflicts.
- **AdminLTE cards:** `card-outline card-{color}` classes go on the outer `div.card`, **never** on `div.card-header`. Adding them to the header instead of the card silently breaks the colored left border style.
- **Commits:** Follow Conventional Commits (`feat:`, `fix:`, `docs:`, etc.).
