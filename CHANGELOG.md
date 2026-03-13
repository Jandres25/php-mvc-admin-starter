# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.2...HEAD
[1.1.2]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/Jandres25/php-mvc-admin-starter/releases/tag/v1.0.0