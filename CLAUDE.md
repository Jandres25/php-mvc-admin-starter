# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP MVC admin starter with authentication, user management, and permission-based access control. Uses AdminLTE 3 UI, PDO/MySQL, and a custom PSR-4 autoloader (no Composer).

## Setup

```bash
# 1. Import database schema
mysql -u root -p < auth_base.sql

# 2. Configure environment
cp .env.example .env
# Edit .env: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, TIMEZONE

# 3. Create upload directory and set write permissions
mkdir -p public/uploads/usuarios
cp public/img/user_default.jpg public/uploads/usuarios/
chmod 777 public/uploads/usuarios/
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
- `/controllers/usuarios/crear_usuario.php` — create user action

`views/layouts/session.php` is included at the top of every protected page. It loads the `.env`, validates the session, and defines `requireLogin()` and `getCurrentUser()` global functions.

### MVC Structure

```
config/         # Bootstrap: autoloader, env loader, DB singleton, config array
models/         # Data access + sanitization (Usuario.php, Permiso.php)
services/       # Business logic (AuthorizationService.php, ImagenService.php)
controllers/    # One file per action (e.g., crear_usuario.php, login.php)
views/          # PHP templates; layouts/header.php pulls in all CSS/JS
public/         # Static assets organized as lib/, core/, plugins/, modules/
libs/           # Vendored libraries (TCPDF)
```

### Custom Autoloader

`config/autoload.php` implements PSR-4: namespace separators map to lowercase directory paths relative to the project root. Register once in `config/config.php`. Example: `Controllers\Usuarios\UsuarioController` → `controllers/usuarios/UsuarioController.php`.

### Database Connection

`config/conexion.php` exposes a singleton `Conexion::getInstance()` returning a configured PDO object. All queries use prepared statements.

### Permissions

`services/AuthorizationService.php` checks whether a user has a named permission (e.g., `'usuarios'`, `'permisos'`, `'admin'`). Use `$authService->tienePermisoNombre($idUsuario, 'permiso_name')` to gate access. Navigation items and action buttons are conditionally rendered based on these checks.

Permission names are checked against `$_SESSION['usuario_permisos']` (cached at login). The cache is automatically refreshed by `refreshPermisosIfStale()` in `session.php` — called on every page load — which compares `$_SESSION['permisos_ts']` against the `permisos_updated_at` column in `usuarios`. Call `$modeloUsuario->actualizarPermisosTimestamp($idusuario)` after any permission change so the affected user's cache is regenerated on their next page load.

`AuthorizationService::esAdministrador()` is memoized per-request via `static array $adminCache` — safe to call multiple times in the same request without extra DB queries.

### AJAX Pattern

Action controllers (e.g., `crear_usuario.php`, `ajax_cambiar_clave.php`) return JSON responses consumed by JS modules in `public/js/modules/`. CSRF tokens are generated via `generateCSRFToken()` (global in `session.php`) and validated with `verifyCSRFToken()`; call `regenerateCSRFToken()` after every successful POST.

When an AJAX action is followed by `location.reload()` in JS, set `$_SESSION['mensaje']` and `$_SESSION['icono']` in the PHP endpoint before echoing the JSON — `mensajes.php` will pick them up and fire the SweetAlert2 toast on the reloaded page.

### Frontend Modules

Feature-specific JS lives in `public/js/modules/{feature}/`. Core utilities (AJAX helpers, DataTables setup, jQuery Validate config) are in `public/js/core/`. All third-party libraries are already bundled in `public/js/lib/` and `public/css/lib/`.

**Loading order:** `footer.php` loads Bootstrap and AdminLTE before `$module_scripts`. Always register page-specific JS via `$module_scripts = ['feature/file']` at the top of the view — never use inline `<script>` blocks before `footer.php`, as Bootstrap plugins (e.g. `.tab()`) will not yet be available.

Similarly, register page-specific CSS via `$module_styles = ['feature/file']` at the top of the view.

**Conditional plugin loading:** Third-party plugins (DataTables, Select2, jQuery Validate) are loaded on demand via `$plugins` declared before `header.php`. Available plugins: `datatables`, `datatables-export`, `select2`, `validate`, `chart`. Example:

```php
$plugins = ['datatables', 'datatables-export'];
$module_scripts = ['usuarios/index-usuarios'];
require_once '../layouts/header.php';
```

**Form validation:** Use `$plugins = ['select2', 'validate']` on forms. `public/js/core/common-validate.js` (loaded automatically with `validate`) configures jQuery Validate globally for Bootstrap 4 — `errorPlacement` inside `.form-group`, `highlight`/`unhighlight`, `onkeyup: false`. Each module calls `$('#form').validate({ rules, messages, submitHandler })` with its own rules. For uniqueness checks against the DB, use `remote` rules pointing to `controllers/usuarios/check_correo.php` or `check_documento.php`.

## Coding Conventions

- **Namespaces:** `Controllers\Auth`, `Controllers\Usuarios`, `Models`, `Services` — match directory structure in lowercase.
- **CSRF:** Generate token with `generateCSRFToken()`, validate with `verifyCSRFToken()` (global functions in `session.php`). Call `regenerateCSRFToken()` after every successful POST validation to prevent replay attacks.
- **Input sanitization:** Use `trim()` at the model layer (`sanitizarDatos()`). Apply `htmlspecialchars()` exclusively at the view layer on all output — never in the model or before storing in the DB.
- **Passwords:** Always `password_hash($pass, PASSWORD_DEFAULT)` / `password_verify()`.
- **Images:** Route all upload/resize/delete through `ImagenService`.
- **JS:** ES6+ with JSDoc comments. Use `SweetAlert2` for confirmations, `DataTables` for lists, `Select2` for dropdowns.
- **Select2 in modals:** When a Select2 element lives inside a Bootstrap modal, initialize it with `initializeSelect2('#selectId', { dropdownParent: $('#modalId') })` instead of the generic `initializeSelect2()`. Without `dropdownParent`, Bootstrap's focus trap closes the dropdown immediately. Populate options from PHP `<option>` elements (server-side) rather than AJAX to avoid re-initialization conflicts.
- **AdminLTE cards:** `card-outline card-{color}` classes go on the outer `div.card`, **never** on `div.card-header`. Adding them to the header instead of the card silently breaks the colored left border style.
- **Commits:** Follow Conventional Commits (`feat:`, `fix:`, `docs:`, etc.).
