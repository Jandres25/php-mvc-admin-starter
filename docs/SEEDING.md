# Database Seeding Guide

This project includes a predefined dataset in `database/seeder.sql` for faster local setup and manual QA.

## Import order

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeder.sql
```

## Rerun behavior

The seeder truncates `user_permissions`, `users`, and `permissions` before inserting data again.  
This keeps seed imports deterministic and avoids duplicate records in local environments.

## Seeded test users

All users below are seeded with password: `admin123`

| Name                 | Email                    | Status   | Suggested scenario                     |
| -------------------- | ------------------------ | -------- | -------------------------------------- |
| Administrator System | admin@sistema.com        | Active   | Full admin flows and global access     |
| Ana Paredes Molina   | ana.paredes@sistema.com  | Active   | User management operations             |
| Carlos Rojas Vega    | carlos.rojas@sistema.com | Active   | Basic profile-only navigation          |
| Lucia Quispe Salas   | lucia.quispe@sistema.com | Inactive | Account activation/deactivation flows  |
| Diego Torres Luna    | diego.torres@sistema.com | Active   | Non-admin read/navigation smoke checks |

## Permission matrix

| User                     | profile | users | permissions | admin |
| ------------------------ | ------- | ----- | ----------- | ----- |
| admin@sistema.com        | ✅      | ✅    | ✅          | ✅    |
| ana.paredes@sistema.com  | ✅      | ✅    | ❌          | ❌    |
| carlos.rojas@sistema.com | ✅      | ❌    | ❌          | ❌    |
| lucia.quispe@sistema.com | ✅      | ❌    | ✅          | ❌    |
| diego.torres@sistema.com | ✅      | ❌    | ❌          | ❌    |

## Notes

- Permission names are intentionally aligned with the application checks (`profile`, `users`, `permissions`, `admin`).
- If you modify permissions manually, remember that user permission cache refresh depends on `permissions_updated_at`.
