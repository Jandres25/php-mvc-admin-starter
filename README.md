# PHP MVC Admin Starter

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-1.1.1-green)](CHANGELOG.md)

A PHP starter template with authentication, user management, and role-based permission control. Built on a pure MVC architecture, **with no Composer dependencies or external frameworks**.

> A solid starting point for PHP web applications that need a secure admin panel out of the box.

## Features

- **Secure authentication** — Login by email or document number, CSRF protection, anti-session hijacking, inactivity logout
- **User management** — Full CRUD, profile images, account activation/deactivation
- **Permission control** — Granular per-user permissions, adaptive navigation menu
- **No Composer** — Custom PSR-4 autoloader; zero external dependencies to manage
- **AdminLTE 3** — Production-ready responsive dashboard
- **PDF generation** — Built-in report generation with TCPDF
- **Full UI toolkit** — DataTables, Select2, SweetAlert2, Chart.js, jQuery Validate included

## Requirements

- PHP 8.2+ with **PDO** and **GD** extensions
- MySQL 5.7+ / MariaDB 10.4+
- Apache 2.4+ or Nginx

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/Jandres25/php-mvc-admin-starter.git
cd php-mvc-admin-starter

# 2. Import the database
mysql -u root -p < auth_base.sql

# 3. Set up the environment
cp .env.example .env
# Edit .env with your credentials (see Configuration section)

# 4. Create the uploads directory
mkdir -p public/uploads/usuarios
cp public/img/user_default.jpg public/uploads/usuarios/
```

Open `http://localhost/php-mvc-admin-starter/` in your browser.

**Default credentials:** `admin@sistema.com` / `admin123`
⚠️ Change the default credentials immediately after installation.

## Configuration `.env`

```env
DB_HOST=localhost
DB_NAME=auth_base
DB_USER=root
DB_PASS=yourpassword
DB_CHARSET=utf8mb4

APP_URL=http://localhost/php-mvc-admin-starter
TIMEZONE=America/La_Paz
DEBUG=true
```

## Adding a New Module

This project is designed to be extended. To add a module (e.g. `Products`):

1. **Controller** — Create `controllers/products/ProductController.php` with namespace `Controllers\Products`
2. **Model** — Create `models/Product.php` with namespace `Models`
3. **Views** — Create `views/products/index.php`, `create.php`, etc.
4. **Assets** — Add CSS to `public/css/modules/products/` and JS to `public/js/modules/products/`
5. **Permission** — Insert the permission into the `permiso` table in the database
6. **Menu** — Add the link in `views/layouts/header.php` with a permission check:

```php
<?php if ($authService->tienePermiso($usuario['idusuario'], 'products')): ?>
    <li class="nav-item">
        <a href="<?= $URL ?>/views/products/index.php" class="nav-link">Products</a>
    </li>
<?php endif; ?>
```

The autoloader automatically resolves any class whose namespace matches the directory structure.

## Architecture

```
config/         # PSR-4 autoloader, PDO singleton, .env helpers
controllers/    # One file per action (no central router)
models/         # Data access + input sanitization
services/       # Reusable business logic (AuthorizationService, ImagenService)
views/          # PHP templates; layouts/session.php validates the session
public/         # Static assets organized into lib/, core/, plugins/, modules/
libs/           # Vendored libraries (TCPDF)
```

**Request flow:** URLs point directly to controller files (e.g. `/controllers/auth/login.php`). There is no central router. Every protected page includes `views/layouts/session.php` as its first step, which validates the session and exposes the `requireLogin()` and `getCurrentUser()` helpers.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, PDO, custom PSR-4 autoloader |
| UI framework | AdminLTE 3, Bootstrap 4, FontAwesome |
| JavaScript | jQuery, DataTables, Select2, SweetAlert2, Chart.js, Moment.js |
| PDF | TCPDF |
| Database | MySQL / MariaDB |

## Security

- Passwords hashed with `password_hash()` (PASSWORD_DEFAULT)
- CSRF tokens on all forms
- Prepared statements for all SQL queries
- XSS prevention via `htmlspecialchars()` input sanitization
- Session hijacking protection (IP and User-Agent validation)
- Session ID regeneration on every login

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full guide.
We follow [Conventional Commits](https://conventionalcommits.org/) and [Semantic Versioning](https://semver.org/).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full change history.

## License

MIT — see [LICENSE](LICENSE) for details.
