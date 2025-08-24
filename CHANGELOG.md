# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/Jandres25/php-mvc-admin-starter/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/Jandres25/php-mvc-admin-starter/releases/tag/v1.0.0