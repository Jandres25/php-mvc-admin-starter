# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.5.0] - 2026-03-19

### Added
- `descripcion` field on the `permiso` table (TEXT NULL) — editable via the create/edit modal in the permissions index and detail views
- User assignment and revocation directly from the permission detail view: new AJAX endpoints `asignar_usuario_permiso_ajax.php` and `revocar_usuario_permiso_ajax.php` with CSRF validation and permission guard
- `refreshPermisosIfStale()` in `session.php` — called automatically from `requireLogin()` on every page load; compares `$_SESSION['permisos_ts']` against the `permisos_updated_at` column in `usuarios` and regenerates the session permission cache only when stale (1 extra DB query per load vs. always querying)
- `permisos_updated_at DATETIME NULL` column on `usuarios` table — updated by `actualizarPermisosTimestamp()` whenever a user's permissions change (assign, revoke, or bulk update)
- `actualizarPermisosTimestamp()` and `getPermisosTimestamp()` on `Usuario` model — used by the cache invalidation mechanism

### Changed
- Permission detail view (`views/permisos/detalle.php`): redesigned with an "Asignar Usuario" modal (Select2 populated from PHP server-side) and per-row revoke buttons with SweetAlert2 confirmation
- Users without a permission now loaded from PHP `<option>` elements instead of AJAX, avoiding the Bootstrap modal + Select2 focus-trap conflict that caused the dropdown to close immediately
- `AuthorizationService::esAdministrador()` now memoizes its result per-request via `private static array $adminCache` — eliminates repeated DB queries when the method is called multiple times for the same user in a single request
- `views/layouts/header.php`: `require_once config.php` moved before `requireLogin()` so the PSR-4 autoloader is registered before `refreshPermisosIfStale()` instantiates `\Models\Usuario`

### Fixed
- Fatal 500 on dashboard (`index.php`): `requireLogin()` → `refreshPermisosIfStale()` tried to instantiate `\Models\Usuario` before `config.php` registered the autoloader
- `Uncaught SyntaxError: redeclaration of const csrfToken` in `views/permisos/detalle.php`: removed redundant `const csrfToken` declaration (already defined globally in `header.php`)

## [1.4.0] - 2026-03-16

### Added
- Conditional plugin loading via `$plugins` array in views — DataTables, Select2, jQuery Validate and export libraries are only loaded on pages that declare them, eliminating ~20 unnecessary files from every page load
- `public/js/core/common-validate.js` — global jQuery Validate configuration for Bootstrap 4: Bootstrap-compatible `errorPlacement`, `highlight`/`unhighlight`, `success` callback and `onkeyup: false`; reusable by any future module without extra config
- Remote validation endpoints `controllers/usuarios/check_correo.php` and `controllers/usuarios/check_documento.php` — lightweight AJAX uniqueness checks against the DB for email and document number on create/update forms; exclude the current user on edit via `$id_excluir`

### Changed
- `header.php` and `footer.php` now conditionally load plugin CSS/JS based on the `$plugins` array declared at the top of each view; `common-datatable.js` is now part of the `datatables` plugin group
- User create/update forms migrated to jQuery Validate with inline Bootstrap `is-invalid`/`is-valid` feedback, replacing manual `Swal.fire()` validation popups
- Remote validation fires only on field blur and form submit (`onkeyup: false`) — no AJAX calls while the user is still typing

### Fixed
- Password minimum length mismatch between frontend (was 6) and backend `validarDatos()` (requires 8) — both now enforce 8 characters
- Browser autofill in `update.php`: password fields changed to `autocomplete="new-password"` (Chrome ignores `autocomplete="off"` on `type="password"`), correo field set to `autocomplete="off"`

## [1.3.0] - 2026-03-15

### Added
- Permission cache in `$_SESSION['usuario_permisos']` populated at login — navigation checks now require zero DB queries per page load; cache is refreshed automatically when a user edits their own permissions
- Self-deactivation protection: users cannot deactivate their own account from the index, nor change their own status from the edit form
- Status field removed from the user edit form — account activation/deactivation is managed exclusively from the user list

### Changed
- `sanitizarDatos()` in `Usuario.php` and `Permiso.php` now only applies `trim()`; `htmlspecialchars()` moved exclusively to the view layer, eliminating double-encoding of stored data
- Login no longer reveals whether an email or document number exists — all credential failures return a generic "Credenciales incorrectas" message
- `loginPorCorreo()` and `loginPorNumDocumento()` no longer filter by `estado = 1`; account status is checked after credential verification so disabled-account users get an explicit message
- `logout.php` now includes `session.php` (instead of raw `session_start()`) so `$URL` is always defined and the redirect lands on the correct login page

### Security
- CSRF token regenerated after every successful POST validation across all 6 endpoints (crear/actualizar/cambiar_estado permisos, crear/actualizar usuario, desactivar usuario)
- Session cookie hardened: `httponly`, `SameSite=Lax`, `use_strict_mode` flags set in `session.php`
- AJAX endpoints for permisos and usuarios now require `requirePermiso()` equivalent inline check returning JSON 403 instead of an HTML error page
- `AuthController::verificarSesion()` removed — was dead code with an inconsistent 3600 s timeout vs the 86400 s used by `checkSessionTimeout()` in `session.php`
- `desactivar_usuario.php` rewritten as POST + CSRF + JSON AJAX endpoint (was an unprotected GET redirect)
- `AuthController` CSRF helpers now delegate to the global `generateCSRFToken()` / `verifyCSRFToken()` functions, eliminating duplicate logic

### Fixed
- N+1 queries in `PermisoController::index()` — replaced per-row `contarUsuarios()` loop with a single `getAllWithUserCount()` LEFT JOIN query
- N+1 queries in `update.php` permission checkboxes — replaced per-permission `tienePermisoAsignado()` calls with a single `obtenerPermisosAsignados()` + `in_array()` check
- Headers-after-output warning in `detalle.php` — controller logic moved before `include header.php`
- `AdminLTE` card classes: `card-outline card-primary` moved from `card-header` to `div.card` in `usuarios/index.php`

## [1.2.0] - 2026-03-14

### Added
- `show-usuario.js` module: Bootstrap tab persistence via `localStorage` and profile image lightbox with SweetAlert2
- `show-usuario.css` module: active tab color styling for the user detail page
- CSRF token on `create.php` and `update.php` user forms

### Changed
- `create.php` and `update.php`: input fields now use `input-group` with FontAwesome icons; sidebar cards use `card-outline sticky-top`; submit/cancel buttons moved to `card-footer`
- `show.php`: moved inline `<style>` and `<script>` blocks to `$module_styles` / `$module_scripts` modules; fixed `card-outline` placement on Sistema card
- `perfil.php`: simplified to only editable fields — profile photo, phone, and address; password change moved to a separate AJAX tab
- `perfil-usuario.js`: rewrote with client-side image preview (size and format validation) and AJAX password change that redirects to logout on success
- `PerfilController::actualizarPerfil`: non-editable fields (nombre, apellidos, correo) now sourced from DB instead of POST to prevent premature validation failure
- `procesar_actualizar_perfil.php`: switched from `UsuarioController` to `PerfilController`
- `common-datatable.js`: added `initComplete` callback to reveal the table only after DataTables finishes rendering

### Fixed
- `.tab is not a function` error in `show.php` caused by inline script running before Bootstrap loaded
- Image upload silently failing due to Apache (`daemon`) lacking write permission on `public/uploads/usuarios/`
- Profile update rejecting valid image uploads when nombre/apellidos were absent from POST

## [1.1.2] - 2026-03-13

### Added
- Custom error pages (403, 404, 500) served via Apache `ErrorDocument` directives in `.htaccess`
- `APP_VERSION` environment variable exposed as `$APP_VERSION` global in `session.php`, displayed in the footer
- `requirePermiso()` helper in `session.php` to gate access by named permission, rendering the 403 view directly instead of redirecting

### Fixed
- `$URL` undefined variable in `404.php` and `403.php` when served by Apache; replaced with local `$app_url`
- Font Awesome webfonts not loading due to mismatched relative path; added `public/css/lib/webfonts` symlink pointing to `fontawesome/webfonts`
- Removed external Google Fonts request in `header.php`; font is now loaded from local `public/css/core/webfonts.css`

## [1.1.1] - 2026-02-20

### Fixed
- Autoloader case-sensitive path resolution on Linux: `strtolower()` was applied to the full class path including the class name, causing `Class not found` errors on case-sensitive filesystems (Linux/ext4). Now only the namespace portion (directories) is lowercased; the class name preserves its original casing.

## [1.1.0] - 2025-08-24

### Added
- Comprehensive PHPDoc documentation across all PHP files
- JSDoc documentation for all custom JavaScript modules
- Standardized documentation format with @package ProyectoBase and @author Jandres25
- Version 1.0 consistency across all documented files
- Professional documentation structure following industry standards

### Changed
- Updated project identity from legacy references (Sistema de Ventas, Calzados y Carteras, Alojamiento Flores) to ProyectoBase
- Repository references updated to php-mvc-admin-starter in README and documentation
- Standardized @author tags to Jandres25 across all files
- Enhanced code organization with proper @subpackage annotations

### Fixed
- Inconsistent project naming across different files
- Missing documentation headers in controller and service files
- Outdated repository URLs in installation instructions

### Documentation
- All PHP classes and files now include proper PHPDoc headers
- JavaScript modules documented with JSDoc standards
- Consistent package and authorship information throughout codebase

## [1.0.0] - 2025-08-23

### Added
- Initial project setup with PHP MVC authentication system
- Complete user management with CRUD operations
- Permission-based access control system
- Responsive admin interface using AdminLTE 3
- DataTables integration for data management
- Image upload and management functionality
- PDF generation capabilities with TCPDF
- Multi-language JavaScript components
- Secure session management
- Database configuration with environment variables

### Security
- Password hashing with PHP's password_hash()
- CSRF protection on all forms
- Session hijacking prevention
- SQL injection protection with prepared statements
- XSS prevention with input sanitization

[Unreleased]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.5.0...HEAD
[1.5.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.2...v1.2.0
[1.1.2]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/Jandres25/php-mvc-admin-starter/releases/tag/v1.0.0