-- Minimal seed for Integration tests.
-- Loaded once per test run by IntegrationTestCase::setUpBeforeClass().
-- Keep small: 1 admin, 1 normal user, 2 permissions, 1 assignment.

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE role_permissions;
TRUNCATE TABLE user_permissions;
TRUNCATE TABLE users;
TRUNCATE TABLE permissions;
TRUNCATE TABLE roles;
SET FOREIGN_KEY_CHECKS = 1;

-- Permissions
INSERT INTO permissions (id, name, description, status) VALUES
  (1, 'users',       'User management',       1),
  (2, 'permissions', 'Permission management', 1);

-- Admin (position = 'administrator', password = 'admin123')
INSERT INTO users
  (id, name, first_surname, document_type, document_number, email, position, password, status)
VALUES
  (1, 'Admin', 'Test', 'DNI', '00000001', 'admin@test.com', 'administrator',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Normal user (password = 'password')
INSERT INTO users
  (id, name, first_surname, document_type, document_number, email, position, password, status)
VALUES
  (2, 'Normal', 'User', 'DNI', '00000002', 'user@test.com', 'editor',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Assign 'users' permission to normal user
INSERT INTO user_permissions (user_id, permission_id) VALUES (2, 1);

-- Roles
INSERT INTO roles (id, name, description, status, is_system) VALUES
  (1, 'Administrator', 'Full system access',    1, 1),
  (2, 'Editor',        'Content editor role',   1, 0),
  (3, 'Auditor',       'Read-only audit role',  0, 0);

-- Role permissions (Editor → users permission)
INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 1);
