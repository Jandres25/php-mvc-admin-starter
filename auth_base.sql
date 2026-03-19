-- Base de datos
CREATE DATABASE IF NOT EXISTS auth_base;
USE auth_base;

-- Tabla de permisos
CREATE TABLE permiso (
  idpermiso int PRIMARY KEY AUTO_INCREMENT,
  nombre varchar(255) NOT NULL,
  descripcion TEXT NULL,
  fechacreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fechaactualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP,
  estado tinyint(1) DEFAULT 1
);

-- Tabla de usuarios
CREATE TABLE usuarios (
  idusuario int PRIMARY KEY AUTO_INCREMENT,
  nombre varchar(255) NOT NULL,
  apellidopaterno varchar(255) NOT NULL,
  apellidomaterno varchar(255) DEFAULT NULL,
  tipodocumento varchar(20) NOT NULL,
  numdocumento varchar(20) NOT NULL,
  direccion varchar(255) DEFAULT NULL,
  telefono varchar(15) DEFAULT NULL,
  correo varchar(255) DEFAULT NULL CHECK (correo IS NULL OR correo LIKE '%@%.%'),
  cargo varchar(255) DEFAULT NULL,
  clave varchar(255) NOT NULL,
  imagen varchar(255),
  fechacreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fechaactualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP,
  estado tinyint(1) DEFAULT 1,
  permisos_updated_at DATETIME NULL DEFAULT NULL,
  UNIQUE KEY (correo),
  UNIQUE KEY (tipodocumento, numdocumento)
);

-- Tabla relacional de permisos de usuarios
CREATE TABLE permisousuario (
  idrol int PRIMARY KEY AUTO_INCREMENT,
  idpermiso int DEFAULT NULL,
  idusuario int DEFAULT NULL,
  fechacreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fechaactualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (idpermiso) REFERENCES permiso(idpermiso),
  FOREIGN KEY (idusuario) REFERENCES usuarios(idusuario)
);

-- Insertar permisos básicos
INSERT INTO permiso (nombre) VALUES 
('perfil'),       -- Acceso al perfil del usuario
('admin'),        -- Administración general (superusuario)
('usuarios'),     -- Gestión de usuarios
('permisos');     -- Gestión de permisos

-- Crear usuario administrador por defecto
-- Usuario: admin, Contraseña: admin123
-- La contraseña está hasheada con password_hash() y el algoritmo PASSWORD_DEFAULT
INSERT INTO usuarios (
  nombre, 
  apellidopaterno, 
  apellidomaterno, 
  tipodocumento, 
  numdocumento, 
  direccion, 
  telefono, 
  correo, 
  cargo, 
  clave, 
  estado
) VALUES (
  'Administrador', 
  'Sistema', 
  NULL, 
  'DNI', 
  '12345678', 
  NULL, 
  NULL, 
  'admin@sistema.com', 
  'Administrador', 
  '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni', -- admin123
  1
);

-- Obtener el ID del usuario administrador
SET @admin_id = LAST_INSERT_ID();

-- Asignar todos los permisos al administrador
INSERT INTO permisousuario (idpermiso, idusuario)
SELECT idpermiso, @admin_id FROM permiso;