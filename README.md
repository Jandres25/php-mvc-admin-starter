<div align="center">

# PHP MVC Admin Starter

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-3.4.0-green)](CHANGELOG.md)

A PHP starter template with authentication, user management, and role-based permission control. Built on a pure MVC architecture with a custom PSR-4 autoloader and Composer for dependency management.

> A solid starting point for PHP web applications that need a secure admin panel out of the box.

</div>

## Features

- **Secure authentication** — Login by email or document number, CSRF protection, anti-session hijacking, inactivity logout, persistent remember-me cookie
- **User management** — Full CRUD, profile images, account activation/deactivation
- **Permission control** — Granular per-user permissions, adaptive navigation menu
- **Custom error pages** — Styled 403, 404, and 500 error pages via Apache `ErrorDocument`
- **Composer-managed** — Custom PSR-4 autoloader for `App\*`; Composer handles third-party packages
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

# 2. Install dependencies
composer install

# 3. Import the database
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeder.sql

# 4. Set up the environment
cp .env.example .env
# Edit .env with your credentials (see Configuration section)

# 5. Create the uploads directory
mkdir -p public/uploads/users
cp public/img/user_default.jpg public/uploads/users/
```

Open `http://localhost/php-mvc-admin-starter/` in your browser.

**Default credentials:** `admin@sistema.com` / `admin123`
⚠️ Change the default credentials immediately after installation.
For all seeded test users and their permissions, see [docs/SEEDING.md](docs/SEEDING.md).

## Configuration `.env`

```env
DB_HOST=localhost
DB_NAME=auth_base
DB_USER=root
DB_PASS=yourpassword
DB_CHARSET=utf8mb4

APP_URL=http://localhost/php-mvc-admin-starter/public
TIMEZONE=America/La_Paz
DEBUG=true
APP_VERSION=3.4.0

# Session & Remember Me
SESSION_LIFETIME=1800
REMEMBER_ME_LIFETIME=2592000
REMEMBER_ME_COOKIE_NAME=remember_me

# SMTP Configuration (Optional)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_app_password
MAIL_FROM=your_email@gmail.com
MAIL_FROM_NAME="Admin Starter"
```

## Adding a New Module

This project is designed to be extended. To add a module (e.g. `Products`):

1. **Controller** — Create `app/controllers/products/ProductController.php` with namespace `App\Controllers\Products`, extending `App\Core\Controller`
2. **Model** — Create `app/models/Product.php` with namespace `App\Models`, extending `App\Core\Model`
3. **Views** — Create `views/products/index.php`, `create.php`, etc.
4. **Routes** — Add entries to `routes/web.php`:
   ```php
   ['method' => 'GET',  'path' => '/products',        'controller' => 'Products\Product@index',  'middleware' => ['auth', 'perm:products']],
   ['method' => 'POST', 'path' => '/products',        'controller' => 'Products\Product@store',  'middleware' => ['auth', 'perm:products']],
   ['method' => 'GET',  'path' => '/products/create', 'controller' => 'Products\Product@create', 'middleware' => ['auth', 'perm:products']],
   ```
5. **Assets** — Add CSS to `public/css/modules/products/` and JS to `public/js/modules/products/`
6. **Permission** — Insert the permission into the `permissions` table in the database
7. **Menu** — Add the link in `views/layouts/header.php` with a permission check:

```php
<?php if (\App\Core\Auth::hasPermission('products')): ?>
    <li class="nav-item">
        <a href="<?= URL ?>products" class="nav-link">Products</a>
    </li>
<?php endif; ?>
```

The autoloader automatically resolves any class whose namespace matches the directory structure.

## Architecture

```
app/
├── config/       # Bootstrap: PSR-4 autoloader, PDO singleton, phpdotenv bootstrap
├── controllers/  # Feature controllers (auth/, users/, permissions/, dashboard/)
├── core/         # Controller.php, Model.php, Router.php, Auth.php, AssetRegistry.php, ErrorHandler.php, helpers.php
├── middleware/   # AuthMiddleware, GuestMiddleware, PermissionMiddleware
├── models/       # App\Models
└── services/     # App\Services (ImageService, MailService)
routes/           # web.php — all route definitions
views/            # PHP templates; layouts/header.php and footer.php wrap content
public/           # Static assets (lib/, core/, plugins/, modules/) + index.php (Front Controller)
vendor/           # Composer dependencies (not committed — run composer install)
```

**Request flow:** All HTTP requests are routed through `public/index.php` via Apache rewriting. `App\Core\Router` matches the URI and method against `routes/web.php`, runs declared middleware (auth, guest, perm:NAME), and dispatches to the controller method. Clean URLs like `/users`, `/users/5/edit`, `/permissions/3` replace the old direct-file access pattern.

## Tech Stack

| Layer        | Technology                                                    |
| ------------ | ------------------------------------------------------------- |
| Backend      | PHP 8.2, PDO, custom PSR-4 autoloader, Composer               |
| Dependencies | PHPMailer, TCPDF, phpdotenv (via Composer)                    |
| UI framework | AdminLTE 3, Bootstrap 4, FontAwesome                          |
| JavaScript   | jQuery, DataTables, Select2, SweetAlert2, Chart.js, Moment.js |
| Database     | MySQL / MariaDB                                               |

## Security

- Passwords hashed with `password_hash()` (PASSWORD_DEFAULT), minimum 8 characters
- CSRF tokens on all forms and AJAX endpoints; token regenerated after every successful POST
- Prepared statements for all SQL queries
- XSS prevention via `htmlspecialchars()` at the view layer on all output
- Session cookie flags: `httponly`, `SameSite=Lax`, `use_strict_mode`
- Session hijacking protection (IP and User-Agent validation)
- Session ID regeneration on every login and on every remember-me auto-login
- Remember-me cookie: `HttpOnly`, `SameSite=Lax`, `Secure` (HTTPS); token stored as SHA-256 hash, rotated on every use
- Configurable session lifetime via `SESSION_LIFETIME` in `.env`
- Permission cache in session — navigation checks require zero DB queries per page load
- Users cannot deactivate or change the status of their own account

## Developer Docs

- [docs/SEEDING.md](docs/SEEDING.md) — seeded users, rerun behavior, and permission matrix
- [docs/ACCESS_CONTROL.md](docs/ACCESS_CONTROL.md) — session guards and permission cache flow
- [docs/AJAX_AND_MODULES.md](docs/AJAX_AND_MODULES.md) — AJAX endpoint and frontend module conventions

## AI Integration

This project is configured for AI coding assistants like Claude Code. It includes custom skills, MCP server templates, and auto-approval settings.
See [docs/AI_SETUP.md](docs/AI_SETUP.md) for full details on how to use and extend the AI configuration.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full guide.
We follow [Conventional Commits](https://conventionalcommits.org/) and [Semantic Versioning](https://semver.org/).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full change history.

## License

<div align="center">

MIT — see [LICENSE](LICENSE) for details.

</div>
