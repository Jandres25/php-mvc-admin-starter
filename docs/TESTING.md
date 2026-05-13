# Testing

This project uses **PHPUnit 11** with two independent suites.

---

## Quick start

```bash
# All suites
vendor/bin/phpunit

# Unit only (no DB, ~2 s)
vendor/bin/phpunit --testsuite=Unit

# Integration only (requires test DB)
vendor/bin/phpunit --testsuite=Integration
```

Or via Composer scripts:

```bash
composer test
composer test:unit
composer test:integration
```

---

## Test DB setup (one time)

```bash
# 1. Create the database
mysql -u root -p -e "CREATE DATABASE php_mvc_admin_starter_test CHARACTER SET utf8;"

# 2. Configure credentials
cp .env.testing.example .env.testing
# Edit .env.testing: set DB_HOST, DB_USER, DB_PASS
```

The `IntegrationTestCase` loads `database/schema.sql` and `tests/fixtures/sql/minimal_seed.sql`
automatically on the first run of the Integration suite. No manual schema import needed.

---

## Suites

### Unit (`tests/Unit/`)

No DB, no HTTP context. Each test class maps to a production class:

| Test class                                 | Production class                |
| ------------------------------------------ | ------------------------------- |
| `tests/Unit/Core/HelpersTest.php`          | `app/Core/helpers.php`          |
| `tests/Unit/Core/AuthTest.php`             | `app/Core/Auth.php`             |
| `tests/Unit/Core/RouterTest.php`           | `app/Core/Router.php`           |
| `tests/Unit/Services/ImageServiceTest.php` | `app/Services/ImageService.php` |

### Integration (`tests/Integration/`)

Requires a live MySQL connection. Each test runs inside a transaction that is rolled back on
teardown, so the DB is always clean.

| Test class                                       | What it covers                                        |
| ------------------------------------------------ | ----------------------------------------------------- |
| `tests/Integration/Models/UserTest.php`          | `User` CRUD, remember-me token, permissions timestamp |
| `tests/Integration/Models/PermissionTest.php`    | `Permission` read, assign, revoke, sync               |
| `tests/Integration/Core/AuthIntegrationTest.php` | `refreshPermissionsIfStale`, `attemptRememberLogin`   |

---

## Infrastructure

### Base classes

**`tests/TestCase.php`** — used by Unit tests:

- Resets `$_SESSION`, `$_POST`, `$_GET`, `$_COOKIE` before each test.
- Snapshots and restores `$_SERVER` in teardown.
- Provides `invokePrivate($object, $method, $args)` for testing protected/private methods via reflection.

**`tests/IntegrationTestCase.php`** — used by Integration tests:

- Loads `.env.testing` with phpdotenv before the first test.
- Resets the `Connection` singleton via reflection so it picks up the test DB credentials.
- Loads schema + seed once per suite run (`setUpBeforeClass`).
- Wraps each test in `beginTransaction` / `rollBack` by default.
- Set `protected bool $useTransactions = false` in a subclass when the SUT manages its own
  transaction (e.g. `Permission::syncForUser`); call `self::reloadSeed()` in `setUp/tearDown` instead.

### Fixtures

- **`tests/fixtures/images/`** — `sample.jpg` (50×50 JPEG), `sample.png` (80×40 PNG with alpha),
  `corrupt.txt` (non-image file). Used by `ImageServiceTest`.
- **`tests/fixtures/sql/minimal_seed.sql`** — 1 admin user, 1 editor user, 2 permissions, 1
  assignment. Loaded once per Integration suite run.

---

## Conventions

- **Test method names:** `test_[method]_[condition]_[expected result]`
  — e.g. `test_check_timeout_returns_false_and_destroys_when_exceeded`
- **No mocking PDO** — Integration tests use a real DB connection.
- **No mocking `move_uploaded_file()`** — `ImageService::processImage()` is tested via its
  validation path (MIME, size, error code); `resizeImage()` and `deleteImage()` use real files in `/tmp`.
- **`$_SESSION` in Unit tests** — `Auth` reads/writes `$_SESSION` as a plain array in CLI without
  `session_start()`. Start a real session only when the SUT calls `session_destroy()` (e.g. `AuthTest`).
- **`set_error_handler()`** — always paired with `restore_error_handler()` immediately after to avoid
  PHPUnit risky-test warnings.
- **Do not test controllers** — they are covered by manual browser testing.

---

## CI

Both suites run automatically on every push and pull request via
`.github/workflows/tests.yml` (two independent jobs: `unit` and `integration`).

The `integration` job spins up a MySQL 8.0 service container and generates `.env.testing` at runtime
— no secrets are committed.
