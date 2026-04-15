# AJAX Endpoints and Frontend Module Conventions

This guide documents the project conventions for AJAX controllers and module scripts.

## Endpoint pattern (PHP)

Typical AJAX/action controllers under `app/controllers/**` follow this sequence:

1. Include `views/layouts/session.php` and `app/config/config.php`
2. Enforce `requireLogin()` and optionally `requirePermission(...)`
3. Validate request method + CSRF token (`verifyCSRFToken(...)`)
4. Regenerate token after valid POST (`regenerateCSRFToken()`)
5. Return JSON for JS consumers

When JS success flow ends with `location.reload()`, set:

- `$_SESSION['message']`
- `$_SESSION['icon']`

The toast is displayed after reload by `views/layouts/messages.php`.

## Frontend module structure

Feature scripts live in:

- `public/js/modules/{feature}/`

Register them in the view before `header.php`:

```php
$module_scripts = ['users/index-users'];
require_once '../layouts/header.php';
```

## Plugin loading

Load only required plugins with `$plugins`:

```php
$plugins = ['datatables', 'datatables-export'];
```

Asset resolution is handled by `App\Core\AssetRegistry` and rendered in `layouts/header.php` / `layouts/footer.php`.

## Load order (important)

`footer.php` loads:

1. Bootstrap + AdminLTE
2. Conditional plugin JS
3. `public/js/core/ui-components.js`
4. Module scripts

Do not place page inline scripts that depend on Bootstrap plugins before `footer.php`.

## Select2 and validation conventions

- `ComponentUtils.initAll()` auto-initializes `.select2` and tooltips.
- For Select2 inside Bootstrap modals, initialize explicitly with `dropdownParent`.
- Forms that use jQuery Validate should load plugin `validate`.
- Global validate config lives in `public/js/core/common-validate.js`.
