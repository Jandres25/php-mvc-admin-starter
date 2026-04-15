USE auth_base;

-- Insert base permissions used by the application
INSERT INTO permissions (name, description, status) VALUES
('profile', 'Access to own profile and password changes', 1),
('admin', 'General administration (superuser)', 1),
('users', 'User management', 1),
('permissions', 'Permission management', 1);

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
  position,
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
  'Administrator',
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
  'Operations Manager',
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
  'Support Analyst',
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
  'HR Specialist',
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
  'Data Analyst',
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
  1
);

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
