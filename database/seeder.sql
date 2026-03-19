USE auth_base;

-- Insert basic permissions
INSERT INTO permissions (name) VALUES
('profile'),      -- Access to user profile
('admin'),        -- General administration (superuser)
('users'),        -- User management
('permissions');  -- Permission management

-- Create default admin user
-- Email: admin@sistema.com / Password: admin123
-- Password hashed with password_hash() using PASSWORD_DEFAULT
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
) VALUES (
  'Administrator',
  'System',
  NULL,
  'DNI',
  '12345678',
  NULL,
  NULL,
  'admin@sistema.com',
  'Administrator',
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni', -- admin123
  1
);

-- Get the admin user ID
SET @admin_id = LAST_INSERT_ID();

-- Assign all permissions to admin
INSERT INTO user_permissions (permission_id, user_id)
SELECT id, @admin_id FROM permissions;
