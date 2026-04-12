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
  email varchar(255) DEFAULT NULL CHECK (email IS NULL OR email LIKE '%@%.%'),
  position varchar(255) DEFAULT NULL,
  password varchar(255) NOT NULL,
  image varchar(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  status tinyint(1) DEFAULT 1,
  permissions_updated_at DATETIME NULL DEFAULT NULL,
  reset_token varchar(255) DEFAULT NULL,
  reset_token_expiry DATETIME DEFAULT NULL,
  UNIQUE KEY (email),
  UNIQUE KEY (document_type, document_number)
);

-- User permissions pivot table
CREATE TABLE user_permissions (
  id int PRIMARY KEY AUTO_INCREMENT,
  permission_id int DEFAULT NULL,
  user_id int DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_up_permission FOREIGN KEY (permission_id) REFERENCES permissions(id),
  CONSTRAINT fk_up_user       FOREIGN KEY (user_id)       REFERENCES users(id)
);
