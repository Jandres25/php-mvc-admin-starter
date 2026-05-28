# Database Seeding Guide

This project includes a predefined dataset in `database/seeder.sql` for faster local setup and manual QA.

## Import order

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeder.sql
```

## Rerun behavior

The seeder truncates `activity_logs`, `password_resets`, `user_permissions`, `role_permissions`, `users`, `permissions`, and `roles` before inserting data again.
This keeps seed imports deterministic and avoids duplicate records in local environments.

## Seeded roles

| Role          | `is_system` | Status   | Description                                           |
| ------------- | ----------- | -------- | ----------------------------------------------------- |
| Administrator | 1           | Active   | Full system access (cannot be deleted or deactivated) |
| Editor        | 0           | Active   | Manage users                                          |
| Auditor       | 0           | Active   | Read audit log and own profile                        |
| Viewer        | 0           | Active   | Read-only access to own profile                       |
| Inactive Role | 0           | Inactive | For testing role deactivation flows                   |

## Role permissions (inherited by users of each role)

| Role          | profile | users | permissions | admin | roles | audit_log |
| ------------- | ------- | ----- | ----------- | ----- | ----- | --------- |
| Administrator | ✅ `*`  | ✅ `*`| ✅ `*`      | ✅ `*`| ✅ `*`| ✅ `*`    |
| Editor        | ✅      | ✅    | ❌          | ❌    | ❌    | ❌        |
| Auditor       | ✅      | ❌    | ❌          | ❌    | ❌    | ✅        |
| Viewer        | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |

## Seeded test users

All users below are seeded with password: `admin123`

`created_at` is spread across the **last 6 months** so the dashboard line chart always shows meaningful data.

| Name                    | Email                        | Role          | Status   | Suggested scenario                        |
| ----------------------- | ---------------------------- | ------------- | -------- | ----------------------------------------- |
| Administrator System    | admin@sistema.com            | Administrator | Active   | Full admin flows and global access        |
| Ana Paredes Molina      | ana.paredes@sistema.com      | Editor        | Active   | User management, extra roles perm         |
| Carlos Rojas Vega       | carlos.rojas@sistema.com     | Viewer        | Active   | Profile-only navigation                   |
| Lucia Quispe Salas      | lucia.quispe@sistema.com     | Viewer        | Inactive | Account deactivation flows (status=0)     |
| Diego Torres Luna       | diego.torres@sistema.com     | Viewer        | Active   | Non-admin read/navigation smoke checks    |
| Sofia Mendoza Riva      | sofia.mendoza@sistema.com    | Auditor       | Active   | Audit log access, extra permissions perm  |
| Miguel Flores Campos    | miguel.flores@sistema.com    | Editor        | Active   | User management operations                |
| Valeria Castro Ruiz     | valeria.castro@sistema.com   | Auditor       | Active   | Audit log access                          |
| Roberto Herrera Pinto   | roberto.herrera@sistema.com  | Viewer        | Active   | Minimal-permissions user                  |
| Elena Vargas Solano     | elena.vargas@sistema.com     | Editor        | Active   | User management operations                |
| Jorge Aguirre Blanco    | jorge.aguirre@sistema.com    | Viewer        | Active   | Minimal-permissions user                  |
| Patricia Lara Medina    | patricia.lara@sistema.com    | Auditor       | Active   | Audit log access                          |
| Fernando Rios Zapata    | fernando.rios@sistema.com    | Viewer        | Active   | Minimal-permissions user                  |
| Isabel Navarro Cano     | isabel.navarro@sistema.com   | Viewer        | Inactive | Account deactivation flows                |

## Permission matrix

Effective permissions are the **union** of direct `user_permissions` assignments and permissions inherited from the user's role via `role_permissions`.

| User                         | profile | users | permissions | admin | roles | audit_log |
| ---------------------------- | ------- | ----- | ----------- | ----- | ----- | --------- |
| admin@sistema.com            | ✅ `*`  | ✅ `*`| ✅ `*`      | ✅ `*`| ✅ `*`| ✅ `*`    |
| ana.paredes@sistema.com      | ✅      | ✅    | ❌          | ❌    | ✅    | ❌        |
| carlos.rojas@sistema.com     | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |
| lucia.quispe@sistema.com     | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |
| diego.torres@sistema.com     | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |
| sofia.mendoza@sistema.com    | ✅      | ❌    | ✅          | ❌    | ❌    | ✅        |
| miguel.flores@sistema.com    | ✅      | ✅    | ❌          | ❌    | ❌    | ❌        |
| valeria.castro@sistema.com   | ✅      | ❌    | ❌          | ❌    | ❌    | ✅        |
| roberto.herrera@sistema.com  | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |
| elena.vargas@sistema.com     | ✅      | ✅    | ❌          | ❌    | ❌    | ❌        |
| jorge.aguirre@sistema.com    | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |
| patricia.lara@sistema.com    | ✅      | ❌    | ❌          | ❌    | ❌    | ✅        |
| fernando.rios@sistema.com    | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |
| isabel.navarro@sistema.com   | ✅      | ❌    | ❌          | ❌    | ❌    | ❌        |

> **Note:** `roberto.herrera`, `jorge.aguirre`, `fernando.rios`, and `isabel.navarro` have no direct `user_permissions` rows — their only effective permission comes from the Viewer role (`profile`).

## Notes

- `admin@sistema.com` has `is_system = 1` on its role → `Auth::isAdmin()` returns `true` and the session cache is always `['*']`.
- Permission names are intentionally aligned with the application checks (`profile`, `users`, `permissions`, `admin`, `roles`, `audit_log`).
- `activity_logs` contains ~42 entries spread across the last 6 months for realistic dashboard "Events Today" and audit log QA.
- `password_resets` is truncated on every seeder run. No pre-seeded tokens — use the invite flow or the forgot-password form to generate them.
- If you modify permissions or role assignments manually, remember that the user permission cache depends on `permissions_updated_at`. Run `UPDATE users SET permissions_updated_at = NOW() WHERE id = X` to force a refresh on next page load.
