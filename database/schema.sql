-- Database
CREATE DATABASE IF NOT EXISTS auth_base;
USE auth_base;

-- Permissions table
CREATE TABLE permissions (
  id int PRIMARY KEY AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  description TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  status tinyint(1) DEFAULT 1,
  UNIQUE KEY (name)
);

-- Roles table (must be created before users due to FK)
CREATE TABLE roles (
  id int PRIMARY KEY AUTO_INCREMENT,
  name varchar(60) NOT NULL,
  description varchar(255) DEFAULT NULL,
  status tinyint(1) DEFAULT 1,
  is_system tinyint(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_roles_name (name)
);

-- Users table
CREATE TABLE users (
  id int PRIMARY KEY AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  first_surname varchar(255) NOT NULL,
  second_surname varchar(255) DEFAULT NULL,
  document_type varchar(20) NOT NULL,
  document_number varchar(20) NOT NULL,
  address varchar(255) DEFAULT NULL,
  phone varchar(15) DEFAULT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  image varchar(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  status tinyint(1) DEFAULT 1,
  login_attempts  int          NOT NULL DEFAULT 0,
  locked_until    DATETIME     NULL     DEFAULT NULL,
  last_attempt_at DATETIME     NULL     DEFAULT NULL,
  permissions_updated_at DATETIME NULL DEFAULT NULL,
  reset_token varchar(255) DEFAULT NULL,
  reset_token_expiry DATETIME DEFAULT NULL,
  remember_token CHAR(64) NULL DEFAULT NULL,
  remember_token_expires DATETIME NULL DEFAULT NULL,
  role_id int NOT NULL,
  UNIQUE KEY (email),
  UNIQUE KEY (document_type, document_number),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Role permissions pivot table
CREATE TABLE role_permissions (
  id int PRIMARY KEY AUTO_INCREMENT,
  role_id int NOT NULL,
  permission_id int NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_role_perm (role_id, permission_id),
  CONSTRAINT fk_rp_role       FOREIGN KEY (role_id)       REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- User permissions pivot table
CREATE TABLE user_permissions (
  id int PRIMARY KEY AUTO_INCREMENT,
  permission_id int NOT NULL,
  user_id int NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_perm (user_id, permission_id),
  CONSTRAINT fk_up_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
  CONSTRAINT fk_up_user       FOREIGN KEY (user_id)       REFERENCES users(id) ON DELETE CASCADE
);

-- Password resets / invitations table
CREATE TABLE password_resets (
  id          BIGINT       PRIMARY KEY AUTO_INCREMENT,
  user_id     INT          NOT NULL,
  token_hash  CHAR(64)     NOT NULL,
  type        ENUM('reset','invitation') NOT NULL DEFAULT 'reset',
  expires_at  DATETIME     NOT NULL,
  used_at     DATETIME     NULL DEFAULT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY  uq_token_hash (token_hash),
  KEY         idx_user_type (user_id, type),
  CONSTRAINT  fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity / audit log table (append-only)
CREATE TABLE activity_logs (
  id            bigint       PRIMARY KEY AUTO_INCREMENT,
  actor_id      int          NULL DEFAULT NULL,
  actor_label   varchar(255) NULL DEFAULT NULL,
  module        varchar(50)  NOT NULL,
  action        varchar(50)  NOT NULL,
  description   varchar(255) NULL DEFAULT NULL,
  details       JSON         NULL DEFAULT NULL,
  ip_address    varchar(45)  NULL DEFAULT NULL,
  user_agent    varchar(255) NULL DEFAULT NULL,
  created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
  KEY idx_al_module  (module),
  KEY idx_al_actor   (actor_id),
  KEY idx_al_created (created_at),
  CONSTRAINT fk_al_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
);
