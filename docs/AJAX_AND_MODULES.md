# AJAX Endpoints and Frontend Module Conventions

This guide documents the project conventions for AJAX controller methods and module scripts.

## Endpoint pattern (PHP)

AJAX actions are regular controller methods registered in `routes/web.php` with the appropriate HTTP method and middleware. A typical AJAX method follows this sequence:

1. Call `$this->csrfCheck()` — validates the CSRF token; automatically returns JSON 403 for AJAX requests or redirects for regular POSTs.
2. Call `regenerateCSRFToken()` after the action succeeds to prevent replay attacks.
3. Perform the action (model call, service call, etc.).
4. Return JSON via `$this->jsonResponse($data)`.

Example:

```php
public function toggleStatusAjax(): void
{
    $this->csrfCheck();
    // ... business logic ...
    regenerateCSRFToken();
    $this->jsonResponse(['success' => true]);
}
```

Route declaration in `routes/web.php`:

```php
['method' => 'POST', 'path' => '/users/toggle-status', 'controller' => 'Users\User@toggleStatusAjax', 'middleware' => ['auth', 'perm:users']],
```

When the JS success flow ends with `location.reload()`, set the flash message before calling `jsonResponse()`:

```php
$_SESSION['message'] = 'User status updated.';
$_SESSION['icon']    = 'success';
$this->jsonResponse(['success' => true]);
```

The toast is displayed after reload by `views/layouts/messages.php`.

## Frontend module structure

Feature scripts live in:

- `public/js/modules/{feature}/`

Pass them to `Controller::render()` as the fourth argument:

```php
$this->render('users/index', $data, ['datatables', 'datatables-export'], ['users/index-users']);
```

## Plugin loading

Pass only required plugins as the third argument to `render()`:

```php
$this->render('permissions/index', $data, ['datatables', 'select2'], ['permissions/index-permissions']);
```

Asset resolution is handled by `App\Core\AssetRegistry` and rendered in `layouts/header.php` / `layouts/footer.php`.

## Load order (important)

`footer.php` loads:

1. Bootstrap + AdminLTE
2. Conditional plugin JS
3. `public/js/core/ui-components.js`
4. Module scripts

Do not place page inline scripts that depend on Bootstrap plugins before `footer.php`.

## AJAX URL targets

JS modules should call the clean routes registered in `routes/web.php`:

| Action                    | Old path (removed)                                       | New route                         |
|---------------------------|----------------------------------------------------------|-----------------------------------|
| Check email uniqueness    | `app/controllers/users/check_email.php`                  | `POST /users/check-email`         |
| Check document uniqueness | `app/controllers/users/check_document.php`               | `POST /users/check-document`      |
| Toggle user status        | `app/controllers/users/toggle_user_status.php`           | `POST /users/toggle-status`       |
| Change password (AJAX)    | `app/controllers/users/ajax_change_password.php`         | `POST /users/change-password`     |
| Create permission         | `app/controllers/permissions/create_permission_ajax.php` | `POST /permissions/create`        |
| Update permission         | `app/controllers/permissions/update_permission_ajax.php` | `POST /permissions/update`        |
| Toggle permission status  | `app/controllers/permissions/toggle_permission_status_ajax.php` | `POST /permissions/toggle-status` |
| Assign user to permission | `app/controllers/permissions/assign_user_permission_ajax.php` | `POST /permissions/assign-user`  |
| Revoke user permission    | `app/controllers/permissions/revoke_user_permission_ajax.php` | `POST /permissions/revoke-user`  |
| Get users without perm    | `app/controllers/permissions/get_users_without_permission_ajax.php` | `GET /permissions/get-users-without` |

## Select2 and validation conventions

- `ComponentUtils.initAll()` auto-initializes `.select2` and tooltips.
- For Select2 inside Bootstrap modals, initialize explicitly with `dropdownParent`.
- Forms that use jQuery Validate should load plugin `validate`.
- Global validate config lives in `public/js/core/common-validate.js`.
- For remote uniqueness validation, point `remote` rules at the clean routes above (e.g., `/users/check-email`).
