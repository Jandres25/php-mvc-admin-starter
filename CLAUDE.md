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

# 3. Create upload directory
mkdir -p public/uploads/usuarios
cp public/img/user_default.jpg public/uploads/usuarios/
```

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

`services/AuthorizationService.php` checks whether a user has a named permission (e.g., `'usuarios'`, `'permisos'`, `'admin'`). Use `$authService->tienePermiso($idUsuario, 'permiso_name')` to gate access. Navigation items and action buttons are conditionally rendered based on these checks.

### AJAX Pattern

Action controllers (e.g., `crear_usuario.php`, `ajax_cambiar_clave.php`) return JSON responses consumed by JS modules in `public/js/modules/`. CSRF tokens are generated in `AuthController` and validated on form submission.

### Frontend Modules

Feature-specific JS lives in `public/js/modules/{feature}/`. Core utilities (AJAX helpers, DataTables setup) are in `public/js/core/`. All third-party libraries are already bundled in `public/js/lib/` and `public/css/lib/`.

## Coding Conventions

- **Namespaces:** `Controllers\Auth`, `Controllers\Usuarios`, `Models`, `Services` — match directory structure in lowercase.
- **CSRF:** Generate token with `AuthController::generarTokenCSRF()`, validate with `AuthController::validarTokenCSRF()`.
- **Input sanitization:** Use `htmlspecialchars()` + `trim()` at the model layer before inserting/updating.
- **Passwords:** Always `password_hash($pass, PASSWORD_DEFAULT)` / `password_verify()`.
- **Images:** Route all upload/resize/delete through `ImagenService`.
- **JS:** ES6+ with JSDoc comments. Use `SweetAlert2` for confirmations, `DataTables` for lists, `Select2` for dropdowns.
- **Commits:** Follow Conventional Commits (`feat:`, `fix:`, `docs:`, etc.).
