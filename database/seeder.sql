USE auth_base;

-- Base roles
INSERT INTO roles (name, description, status, is_system) VALUES
('Administrator', 'Full system access',              1, 1),
('Editor',        'Manage users',                    1, 0),
('Viewer',        'Read-only access to own profile', 1, 0);

-- Insert base permissions used by the application
INSERT INTO permissions (name, description, status) VALUES
('profile', 'Access to own profile and password changes', 1),
('admin', 'General administration (superuser)', 1),
('users', 'User management', 1),
('permissions', 'Permission management', 1),
('roles', 'Role management', 1);

-- Seed users for manual testing
-- Password for all users below: admin123
INSERT INTO users (
  name,
  first_surname,
  second_surname,
  document_type,
  document_number,
  address,
  phone,
  email,
  password,
  status
) VALUES
(
  'Administrator',
  'System',
  NULL,
  'DNI',
  '12345678',
  'Main office',
  '900111111',
  'admin@sistema.com',
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
  1
),
(
  'Ana',
  'Paredes',
  'Molina',
  'DNI',
  '42345678',
  'Av. Primavera 123',
  '900222222',
  'ana.paredes@sistema.com',
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
  1
),
(
  'Carlos',
  'Rojas',
  'Vega',
  'DNI',
  '52345678',
  'Jr. Lima 456',
  '900333333',
  'carlos.rojas@sistema.com',
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
  1
),
(
  'Lucia',
  'Quispe',
  'Salas',
  'CE',
  'X1234567',
  'Calle Norte 789',
  '900444444',
  'lucia.quispe@sistema.com',
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
  0
),
(
  'Diego',
  'Torres',
  'Luna',
  'PAS',
  'PA998877',
  'Av. Central 1001',
  '900555555',
  'diego.torres@sistema.com',
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
  1
);

-- Assign roles to seeded users
UPDATE users SET role_id = (SELECT id FROM roles WHERE name = 'Administrator') WHERE email = 'admin@sistema.com';
UPDATE users SET role_id = (SELECT id FROM roles WHERE name = 'Editor')        WHERE email = 'ana.paredes@sistema.com';
UPDATE users SET role_id = (SELECT id FROM roles WHERE name = 'Viewer')        WHERE email IN ('carlos.rojas@sistema.com', 'diego.torres@sistema.com');

-- Get user IDs for permission assignment
SET @admin_id = (SELECT id FROM users WHERE email = 'admin@sistema.com' LIMIT 1);
SET @manager_id = (SELECT id FROM users WHERE email = 'ana.paredes@sistema.com' LIMIT 1);
SET @support_id = (SELECT id FROM users WHERE email = 'carlos.rojas@sistema.com' LIMIT 1);
SET @hr_id = (SELECT id FROM users WHERE email = 'lucia.quispe@sistema.com' LIMIT 1);
SET @analyst_id = (SELECT id FROM users WHERE email = 'diego.torres@sistema.com' LIMIT 1);

-- Assign permissions by role profile
INSERT INTO user_permissions (permission_id, user_id)
SELECT id, @admin_id FROM permissions;

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @manager_id
FROM permissions p
WHERE p.name IN ('profile', 'users');

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @support_id
FROM permissions p
WHERE p.name IN ('profile');

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @hr_id
FROM permissions p
WHERE p.name IN ('profile', 'permissions');

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @analyst_id
FROM permissions p
WHERE p.name IN ('profile');

-- Role permissions (Administrator uses is_system=1 so no rows needed)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
  ON r.name = 'Editor' AND p.name IN ('profile', 'users');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
  ON r.name = 'Viewer' AND p.name IN ('profile');

-- Audit log permission (admin with is_system=1 already gets * — this row enables it for non-system roles)
INSERT INTO permissions (name, description, status) VALUES
('audit_log.view', 'View the system audit/activity log', 1);

-- -------------------------------------------------------------------------
-- Audit log — seed entries that reflect the above setup actions
-- All events are attributed to the Administrator (actor_id = @admin_id).
-- Timestamps are staggered by 1 minute to produce a realistic timeline.
-- -------------------------------------------------------------------------

SET @t = '2025-01-01 08:00:00';

-- Roles created
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'roles', 'create', 'Role created: Administrator', JSON_OBJECT('name','Administrator','is_system',1), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'roles', 'create', 'Role created: Editor', JSON_OBJECT('name','Editor','is_system',0), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'roles', 'create', 'Role created: Viewer', JSON_OBJECT('name','Viewer','is_system',0), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

-- Permissions created
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'permissions', 'create', 'Permission created: profile',     JSON_OBJECT('name','profile'),     '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'permissions', 'create', 'Permission created: admin',       JSON_OBJECT('name','admin'),       '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'permissions', 'create', 'Permission created: users',       JSON_OBJECT('name','users'),       '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'permissions', 'create', 'Permission created: permissions', JSON_OBJECT('name','permissions'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'permissions', 'create', 'Permission created: roles',       JSON_OBJECT('name','roles'),       '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'permissions', 'create', 'Permission created: audit_log.view', JSON_OBJECT('name','audit_log.view'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

-- Users created
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'users', 'create', 'User created: Administrator System',
 JSON_OBJECT('email','admin@sistema.com','role','Administrator','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'users', 'create', 'User created: Ana Paredes',
 JSON_OBJECT('email','ana.paredes@sistema.com','role','Editor','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'users', 'create', 'User created: Carlos Rojas',
 JSON_OBJECT('email','carlos.rojas@sistema.com','role','Viewer','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'users', 'create', 'User created: Lucia Quispe',
 JSON_OBJECT('email','lucia.quispe@sistema.com','role',NULL,'status','inactive'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'users', 'create', 'User created: Diego Torres',
 JSON_OBJECT('email','diego.torres@sistema.com','role','Viewer','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

-- Role permissions synced
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'roles', 'sync_permissions', 'Permissions synced for role: Editor',
 JSON_OBJECT('role','Editor','permissions','profile, users'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator', 'roles', 'sync_permissions', 'Permissions synced for role: Viewer',
 JSON_OBJECT('role','Viewer','permissions','profile'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:01:00');

-- First login
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@admin_id, 'Administrator', 'auth', 'login', 'Successful login: Administrator System',
 JSON_OBJECT('email','admin@sistema.com'), '127.0.0.1', 'Seeder/1.0', @t);
