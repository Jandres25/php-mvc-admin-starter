# Database Seeding Guide

This project includes a predefined dataset in `database/seeder.sql` for faster local setup and manual QA.

## Import order

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeder.sql
```

## Rerun behavior

The seeder truncates `role_permissions`, `user_permissions`, `users`, `permissions`, and `roles` before inserting data again.
This keeps seed imports deterministic and avoids duplicate records in local environments.

## Seeded roles

| Role          | `is_system` | Description                                           |
| ------------- | ----------- | ----------------------------------------------------- |
| Administrator | 1           | Full system access (cannot be deleted or deactivated) |
| Editor        | 0           | Manage users                                          |
| Viewer        | 0           | Read-only access to own profile                       |

## Seeded test users

All users below are seeded with password: `admin123`

| Name                 | Email                    | Role          | Status   | Suggested scenario                     |
| -------------------- | ------------------------ | ------------- | -------- | -------------------------------------- |
| Administrator System | admin@sistema.com        | Administrator | Active   | Full admin flows and global access     |
| Ana Paredes Molina   | ana.paredes@sistema.com  | Editor        | Active   | User management operations             |
| Carlos Rojas Vega    | carlos.rojas@sistema.com | Viewer        | Active   | Basic profile-only navigation          |
| Lucia Quispe Salas   | lucia.quispe@sistema.com | —             | Inactive | Account activation/deactivation flows  |
| Diego Torres Luna    | diego.torres@sistema.com | Viewer        | Active   | Non-admin read/navigation smoke checks |

## Permission matrix

Permissions are the **union** of direct assignments and role-inherited permissions.

| User                     | profile | users | permissions | admin | roles.manage |
| ------------------------ | ------- | ----- | ----------- | ----- | ------------ |
| admin@sistema.com        | ✅      | ✅    | ✅          | ✅    | ✅           |
| ana.paredes@sistema.com  | ✅      | ✅    | ❌          | ❌    | ❌           |
| carlos.rojas@sistema.com | ✅      | ❌    | ❌          | ❌    | ❌           |
| lucia.quispe@sistema.com | ✅      | ❌    | ✅          | ❌    | ❌           |
| diego.torres@sistema.com | ✅      | ❌    | ❌          | ❌    | ❌           |

## Notes

- `admin@sistema.com` has `is_system = 1` on its role → `Auth::isAdmin()` returns `true` and the session cache is always `['*']`.
- Permission names are intentionally aligned with the application checks (`profile`, `users`, `permissions`, `admin`, `roles.manage`).
- If you modify permissions or role assignments manually, remember that the user permission cache depends on `permissions_updated_at`. Run `UPDATE users SET permissions_updated_at = NOW() WHERE id = X` to force a refresh on next page load.
