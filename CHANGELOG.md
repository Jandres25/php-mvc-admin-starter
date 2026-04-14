# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- New app-layer base components: `app/core/BaseController.php`, `app/core/ViewRenderer.php`, and `app/core/AssetRegistry.php` to support incremental MVC migration without breaking legacy routes
- New page controllers for view-model preparation: `app/controllers/users/UserPageController.php` and `app/controllers/permissions/PermissionPageController.php`

### Changed
- `config/autoload.php` now resolves `App\...` classes explicitly, enabling the new `app/` layer while preserving existing namespace loading
- Layout plugin assets were centralized through `AssetRegistry` (`views/layouts/header.php`, `views/layouts/footer.php`) to remove duplicated inline plugin maps
- Users and Permissions views (`index`, `show`/`update`, `detail`) now consume page-controller view models instead of embedding request validation and flow control logic directly in views
- Documentation updated to describe `app/` structure and compatibility-wrapper strategy during migration

## [2.3.1] - 2026-04-13

### Added
- jQuery Validate support in auth views (`views/auth/login.php`, `views/auth/forgot_password.php`, `views/auth/reset_password.php`) by loading `jquery.validate`, `additional-methods`, and `public/js/core/common-validate.js` for consistent client-side validation

### Changed
- Auth form scripts (`public/js/modules/auth/login.js`, `forgot_password.js`, `reset_password.js`) now use `$('#form').validate({ ... submitHandler })` instead of manual validation checks, while preserving loading toasts/spinners on submit
- Auth form markup now wraps each input as `form-group > input-group`, aligning Bootstrap validation feedback with the same pattern used in the rest of the app
- `public/css/modules/login/login.css` now relies on global validation styles (removed local `.is-valid`/`.is-invalid` overrides) and adjusts validation spacing for cleaner rendering

### Fixed
- Removed redundant Select2 initialization from `public/js/modules/permissions/detail-permission.js`; modal Select2 now depends on centralized initialization to avoid duplicate setup

## [2.3.0] - 2026-04-12

### Added
- **Password Reset System**: New workflow to recover accounts via email (`forgot_password.php`, `reset_password.php`)
- `Services\MailService` — handles SMTP email delivery using PHPMailer
- `libs/PHPMailer-master` — integrated PHPMailer as the primary mailing engine
- SMTP configuration support in `.env` (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, etc.)

### Changed
- **Directory Restructuring**: Renamed `views/login/` to `views/auth/` for better semantic organization
- **Modular JavaScript**: Extracted inline scripts from auth views to `public/js/modules/auth/` (`login.js`, `forgot_password.js`, `reset_password.js`)
- **UX Improvements**: Login and password reset buttons now disable and show a spinner icon (`fa-spinner fa-spin`) during form submission to prevent duplicate requests
- **Enhanced Debugging**: `PasswordResetController` now provides a specific error message if an email is not found when `DEBUG=true` is set in `.env`

## [2.2.0] - 2026-04-11

### Added
- `public/js/core/ui-components.js` — `ComponentUtils` module: centralizes Select2 and tooltip initialization with a single `ComponentUtils.initAll()` call on `DOMContentLoaded`; `initSelect2(options, selector)` and `initTooltips()` are also exposed individually for modules that need targeted initialization
- `public/css/core/ui-components.css` — companion stylesheet loaded globally via `header.php`
- **Permissions index**: new "Description" column in the permissions table showing the permission description or "N/A"
- `data-toggle="tooltip"` added to action buttons in `users/index.php`, `permissions/index.php`, and `permissions/detail.php` — tooltips are now initialized automatically by `ComponentUtils`

### Changed
- `views/permissions/_modal_permission.php` now includes the "Assign User" modal (previously inlined in `detail.php`), keeping `detail.php` free of duplicate markup
- `public/js/core/common-utils.js` — `initializeSelect2()` now delegates to `ComponentUtils.initSelect2()` for backwards compatibility; `refreshSelect2()` and `initializeTooltips()` removed (functionality covered by `ComponentUtils`)
- `create-user.js` / `update-user.js` — removed manual `initializeSelect2()` and `$('[data-toggle="tooltip"]').tooltip()` calls; auto-initialization handled by `ComponentUtils.initAll()`

### Fixed
- **Users — `second_surname` stored as empty string**: `UserController::prepareUserData()` now returns `null` for an empty `second_surname`; `User::create()` and `User::update()` bind it with `PDO::PARAM_NULL` when empty, consistent with the existing pattern for `address`, `phone`, and other nullable fields
- **Permissions — `description` stored as empty string**: `Permission::create()` and `Permission::update()` now use `!empty()` (instead of `?? null`, which does not catch `''`) and bind `null` with `PDO::PARAM_NULL` when the description is absent or blank

## [2.1.0] - 2026-04-01

### Added
- `docs/AI_SETUP.md` documentation for configuring AI assistants like Claude Code.
- `.claude/settings.example.json` to share recommended bash permissions for Claude Code.
- `.mcp.example.json` to share recommended Model Context Protocol (MCP) servers (GitHub, MySQL, ClickUp).
- Added `.claude/*` to `.gitignore` while explicitly allowing `!.claude/skills/` and `!.claude/settings.example.json`.

## [2.0.1] - 2026-03-29

### Fixed
- **Permissions — duplicate name**: Added `UNIQUE KEY (name)` to the `permissions` table in `schema.sql`; `Permission::create()` and `Permission::update()` now catch SQLSTATE `23000` and return a user-friendly message instead of exposing the raw PDO exception
- **Permissions — inactive guard**: The "Assign User" button is now hidden and a contextual warning banner is displayed when viewing the detail of an inactive permission, preventing assignments that would silently fail
- **Permissions — revoke double-submit**: The revoke button in `detail-permission.js` is now disabled immediately after the SweetAlert2 confirmation, preventing concurrent AJAX calls on rapid clicks; re-enabled on server error
- **Permissions — session self-assign**: `assign_user_permission_ajax.php` no longer re-fetches all user permissions on self-assign; it appends the single new permission via `Permission::getById()`, saving a full JOIN query
- **JS error messages**: Normalized both network-error strings in `detail-permission.js` to match the rest of the modules: `'A communication error occurred with the server.'`

### Security
- **XSS in `get_users_without_permission_ajax.php`**: Both `htmlspecialchars()` calls now use `ENT_QUOTES, 'UTF-8'` flags, preventing attribute-context injection for user names and positions

## [2.0.0] - 2026-03-19

### Changed
- Full codebase translation from Spanish to English: DB schema/columns, model/service/controller class and method names, session keys, file and directory names (`usuarios/` → `users/`, `permisos/` → `permissions/`), all UI strings, JS modules, and documentation
- `config/conexion.php` renamed to `config/connection.php`; class `Conexion` renamed to `Connection`; property `$conexion` renamed to `$connection` in all models and services
- Dashboard (`index.php`): replaced placeholder cards with 4 AdminLTE stat widgets (total/active users and permissions pulled from the DB) and a recent users table; table hides Email on xs and Registered on xs/sm for better mobile responsiveness

### Added
- `User::getStatistics()` — returns total, active and inactive user counts in a single query
- `User::getRecent($limit)` — returns the most recently registered users

## [1.5.0] - 2026-03-19

### Added
- `description` field on the `permissions` table (TEXT NULL) — editable via the create/edit modal in the permissions index and detail views
- User assignment and revocation directly from the permission detail view: new AJAX endpoints `assign_user_permission_ajax.php` and `revoke_user_permission_ajax.php` with CSRF validation and permission guard
- `refreshPermissionsIfStale()` in `session.php` — called automatically from `requireLogin()` on every page load; compares `$_SESSION['permissions_ts']` against the `permissions_updated_at` column in `users` and regenerates the session permission cache only when stale (1 extra DB query per load vs. always querying)
- `permissions_updated_at DATETIME NULL` column on `users` table — updated by `updatePermissionsTimestamp()` whenever a user's permissions change (assign, revoke, or bulk update)
- `updatePermissionsTimestamp()` and `getPermissionsTimestamp()` on `User` model — used by the cache invalidation mechanism

### Changed
- Permission detail view (`views/permissions/detail.php`): redesigned with an "Assign User" modal (Select2 populated from PHP server-side) and per-row revoke buttons with SweetAlert2 confirmation
- Users without a permission now loaded from PHP `<option>` elements instead of AJAX, avoiding the Bootstrap modal + Select2 focus-trap conflict that caused the dropdown to close immediately
- `AuthorizationService::isAdmin()` now memoizes its result per-request via `private static array $adminCache` — eliminates repeated DB queries when the method is called multiple times for the same user in a single request
- `views/layouts/header.php`: `require_once config.php` moved before `requireLogin()` so the PSR-4 autoloader is registered before `refreshPermissionsIfStale()` instantiates `\Models\User`

### Fixed
- Fatal 500 on dashboard (`index.php`): `requireLogin()` → `refreshPermissionsIfStale()` tried to instantiate `\Models\User` before `config.php` registered the autoloader
- `Uncaught SyntaxError: redeclaration of const csrfToken` in `views/permissions/detail.php`: removed redundant `const csrfToken` declaration (already defined globally in `header.php`)

## [1.4.0] - 2026-03-16

### Added
- Conditional plugin loading via `$plugins` array in views — DataTables, Select2, jQuery Validate and export libraries are only loaded on pages that declare them, eliminating ~20 unnecessary files from every page load
- `public/js/core/common-validate.js` — global jQuery Validate configuration for Bootstrap 4: Bootstrap-compatible `errorPlacement`, `highlight`/`unhighlight`, `success` callback and `onkeyup: false`; reusable by any future module without extra config
- Remote validation endpoints `controllers/users/check_email.php` and `controllers/users/check_document.php` — lightweight AJAX uniqueness checks against the DB for email and document number on create/update forms; exclude the current user on edit via `$id_excluir`

### Changed
- `header.php` and `footer.php` now conditionally load plugin CSS/JS based on the `$plugins` array declared at the top of each view; `common-datatable.js` is now part of the `datatables` plugin group
- User create/update forms migrated to jQuery Validate with inline Bootstrap `is-invalid`/`is-valid` feedback, replacing manual `Swal.fire()` validation popups
- Remote validation fires only on field blur and form submit (`onkeyup: false`) — no AJAX calls while the user is still typing

### Fixed
- Password minimum length mismatch between frontend (was 6) and backend `validarDatos()` (requires 8) — both now enforce 8 characters
- Browser autofill in `update.php`: password fields changed to `autocomplete="new-password"` (Chrome ignores `autocomplete="off"` on `type="password"`), correo field set to `autocomplete="off"`

## [1.3.0] - 2026-03-15

### Added
- Permission cache in `$_SESSION['user_permissions']` populated at login — navigation checks now require zero DB queries per page load; cache is refreshed automatically when a user edits their own permissions
- Self-deactivation protection: users cannot deactivate their own account from the index, nor change their own status from the edit form
- Status field removed from the user edit form — account activation/deactivation is managed exclusively from the user list

### Changed
- `sanitizeData()` in `User.php` and `Permission.php` now only applies `trim()`; `htmlspecialchars()` moved exclusively to the view layer, eliminating double-encoding of stored data
- Login no longer reveals whether an email or document number exists — all credential failures return a generic "Incorrect credentials" message
- `loginByEmail()` and `loginByDocumentNumber()` no longer filter by `status = 1`; account status is checked after credential verification so disabled-account users get an explicit message
- `logout.php` now includes `session.php` (instead of raw `session_start()`) so `$URL` is always defined and the redirect lands on the correct login page

### Security
- CSRF token regenerated after every successful POST validation across all 6 endpoints (create/update/toggle-status permissions, create/update user, toggle user status)
- Session cookie hardened: `httponly`, `SameSite=Lax`, `use_strict_mode` flags set in `session.php`
- AJAX endpoints for permissions and users now require `requirePermission()` inline check returning JSON 403 instead of an HTML error page
- `AuthController::verificarSesion()` removed — was dead code with an inconsistent 3600 s timeout vs the 86400 s used by `checkSessionTimeout()` in `session.php`
- `toggle_user_status.php` rewritten as POST + CSRF + JSON AJAX endpoint (was an unprotected GET redirect)
- `AuthController` CSRF helpers now delegate to the global `generateCSRFToken()` / `verifyCSRFToken()` functions, eliminating duplicate logic

### Fixed
- N+1 queries in `PermissionController::index()` — replaced per-row `countUsers()` loop with a single `getAllWithUserCount()` LEFT JOIN query
- N+1 queries in `update.php` permission checkboxes — replaced per-permission `hasPermissionAssigned()` calls with a single `getAssignedPermissions()` + `in_array()` check
- Headers-after-output warning in `detail.php` — controller logic moved before `include header.php`
- `AdminLTE` card classes: `card-outline card-primary` moved from `card-header` to `div.card` in `users/index.php`

## [1.2.0] - 2026-03-14

### Added
- `show-user.js` module: Bootstrap tab persistence via `localStorage` and profile image lightbox with SweetAlert2
- `show-user.css` module: active tab color styling for the user detail page
- CSRF token on `create.php` and `update.php` user forms

### Changed
- `create.php` and `update.php`: input fields now use `input-group` with FontAwesome icons; sidebar cards use `card-outline sticky-top`; submit/cancel buttons moved to `card-footer`
- `show.php`: moved inline `<style>` and `<script>` blocks to `$module_styles` / `$module_scripts` modules; fixed `card-outline` placement on System card
- `profile.php`: simplified to only editable fields — profile photo, phone, and address; password change moved to a separate AJAX tab
- `profile-user.js`: rewrote with client-side image preview (size and format validation) and AJAX password change that redirects to logout on success
- `ProfileController::updateProfile`: non-editable fields (name, surnames, email) now sourced from DB instead of POST to prevent premature validation failure
- `process_update_profile.php`: switched from `UserController` to `ProfileController`
- `common-datatable.js`: added `initComplete` callback to reveal the table only after DataTables finishes rendering

### Fixed
- `.tab is not a function` error in `show.php` caused by inline script running before Bootstrap loaded
- Image upload silently failing due to Apache (`daemon`) lacking write permission on `public/uploads/users/`
- Profile update rejecting valid image uploads when name/surnames were absent from POST

## [1.1.2] - 2026-03-13

### Added
- Custom error pages (403, 404, 500) served via Apache `ErrorDocument` directives in `.htaccess`
- `APP_VERSION` environment variable exposed as `$APP_VERSION` global in `session.php`, displayed in the footer
- `requirePermission()` helper in `session.php` to gate access by named permission, rendering the 403 view directly instead of redirecting

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

[Unreleased]: https://github.com/Jandres25/php-mvc-admin-starter/compare/2.3.1...HEAD
[2.3.1]: https://github.com/Jandres25/php-mvc-admin-starter/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/2.2.0...2.3.0
[2.2.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/2.1.0...2.2.0
[2.1.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/Jandres25/php-mvc-admin-starter/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.5.0...2.0.0
[1.5.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.2...v1.2.0
[1.1.2]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/Jandres25/php-mvc-admin-starter/releases/tag/v1.0.0
