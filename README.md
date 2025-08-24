# ProyectoBase

Un sistema base para proyectos PHP con autenticación de usuarios y control de permisos, siguiendo el patrón de diseño MVC (Modelo-Vista-Controlador).

## Características

- Sistema de autenticación completo
- Gestión de usuarios y permisos
- Protección contra CSRF (Cross-Site Request Forgery)
- Arquitectura MVC
- Manejo de sesiones seguras
- Gestión de imágenes
- Interfaz administrativa con AdminLTE 3
- Responsive Design
- Generación de PDFs con TCPDF
- DataTables para manejo de tablas
- Validaciones frontend con jQuery Validate
- Componentes UI avanzados (Select2, SweetAlert2)
- Gráficos con Chart.js

## Requisitos

- **PHP 8.2.4** o superior
- **MariaDB 10.4.28** o MySQL 5.7 o superior  
- **Apache 2.4.56** (con OpenSSL/1.1.1t) o Nginx
- **phpMyAdmin 5.2.1** (recomendado para administración de BD)
- Extensión PDO de PHP
- Extensión GD de PHP (para el manejo de imágenes)
- Extensión OpenSSL de PHP

## Instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/Jandres25/php-mvc-admin-starter.git
   cd php-mvc-admin-starter
   ```

2. Crear la base de datos:
   - Importar el archivo `auth_base.sql` para crear la estructura de la base de datos

3. Configurar el entorno:
   - Copiar el archivo `.env.example` a `.env`
   - Editar el archivo `.env` con tus configuraciones:

   ```bash
   # Configuración de la Base de Datos
   DB_HOST=localhost
   DB_NAME=auth_base
   DB_USER=root
   DB_PASS=tupassword
   DB_CHARSET=utf8mb4

   # Configuración de la Aplicación
   APP_URL=http://localhost/proyectobase/
   TIMEZONE=America/La_Paz
   DEBUG=true
   ```

4. Crear las carpetas necesarias para uploads:
   ```bash
   mkdir -p public/uploads/usuarios
   chmod 755 public/uploads/usuarios
   ```

5. Copiar la imagen de usuario por defecto:
   ```bash
   cp public/img/user_default.jpg public/uploads/usuarios/
   ```

## Estructura del Proyecto

```
ProyectoBase/
├── config/                 # Configuración
│   ├── conexion.php        # Conexión a la base de datos
│   ├── config.php          # Configuraciones generales
│   └── env.php             # Extracción de contenido de .env
├── controllers/            # Controladores
│   ├── auth/               # Controladores de autenticación
│   │   ├── AuthController.php
│   │   ├── login.php
│   │   └── logout.php
│   ├── permisos/           # Controladores de permisos
│   │   ├── PermisoController.php
│   │   └── ...
│   └── usuarios/           # Controladores de usuarios
│       ├── UsuarioController.php
│       ├── PerfilController.php
│       └── ...
├── models/                 # Modelos
│   ├── Usuario.php         # Modelo de usuario
│   └── Permiso.php         # Modelo de permisos
├── services/               # Servicios
│   ├── AuthorizationService.php  # Servicio de autorización
│   └── ImagenService.php   # Servicio para manejo de imágenes
├── libs/                   # Librerías externas
│   └── TCPDF-main/         # Librería para generación de PDFs
├── public/                 # Archivos públicos
│   ├── css/                # Hojas de estilo organizadas por módulos
│   │   ├── core/           # CSS del sistema
│   │   ├── lib/            # Librerías CSS (AdminLTE, Bootstrap, FontAwesome)
│   │   ├── modules/        # CSS específicos por módulo
│   │   └── plugins/        # CSS de plugins
│   ├── js/                 # JavaScript organizados por módulos
│   │   ├── core/           # JS del sistema
│   │   ├── lib/            # Librerías JS (jQuery, Bootstrap, AdminLTE)
│   │   ├── modules/        # JS específicos por módulo
│   │   └── plugins/        # JS de plugins (DataTables, Select2, etc.)
│   ├── img/                # Imágenes del sistema
│   └── uploads/            # Carpeta para archivos subidos
│       └── usuarios/       # Imágenes de usuarios
├── views/                  # Vistas
│   ├── layouts/            # Plantillas
│   │   ├── header.php      # Cabecera con menú
│   │   ├── footer.php      # Pie de página
│   │   ├── mensajes.php    # Sistema de mensajes
│   │   └── session.php     # Verificación de sesión
│   ├── login/              # Vistas de autenticación
│   ├── usuarios/           # Vistas de usuarios
│   └── permisos/           # Vistas de permisos
├── .env.example            # Plantilla para archivo .env
├── auth_base.sql           # Script SQL para crear la base de datos
├── index.php               # Punto de entrada
└── README.md               # Documentación
```

## Acceso por defecto

- **Usuario:** admin@sistema.com
- **Contraseña:** admin123

## Funcionalidades Principales

### Sistema de Autenticación
- Login seguro con validación
- Gestión de sesiones
- Logout automático por inactividad

### Gestión de Usuarios
- CRUD completo de usuarios
- Subida y gestión de imágenes de perfil
- Cambio de contraseñas
- Activación/desactivación de usuarios
- Perfil de usuario editable

### Sistema de Permisos
- Gestión granular de permisos
- Asignación de permisos por usuario
- Control de acceso a módulos

### Interfaz de Usuario
- Dashboard responsivo
- Tablas con DataTables (paginación, búsqueda, ordenamiento)
- Formularios con validación en tiempo real
- Alertas y confirmaciones con SweetAlert2
- Selectores mejorados con Select2

### Generación de Reportes
- Exportación a PDF con TCPDF
- Posibilidad de generar reportes personalizados

## Uso

1. Navega a la raíz del proyecto en tu navegador
2. Inicia sesión con las credenciales por defecto
3. Explora y personaliza el sistema según tus necesidades

## Librerías y Plugins Incluidos

### Frontend
- **AdminLTE 3**: Framework de administración
- **Bootstrap 4**: Framework CSS responsive
- **FontAwesome**: Iconos vectoriales
- **jQuery**: Librería JavaScript
- **DataTables**: Plugin para tablas avanzadas con paginación, búsqueda y filtros
- **Select2**: Plugin para selectores avanzados
- **SweetAlert2**: Alertas y confirmaciones elegantes
- **jQuery Validate**: Validación de formularios
- **Chart.js**: Librería para gráficos
- **Moment.js**: Manejo de fechas

### Backend
- **TCPDF**: Generación de documentos PDF
- **PHP PDO**: Acceso seguro a base de datos
- **Sistema de sesiones**: Manejo seguro de autenticación

## Personalización

Para agregar nuevos módulos:

1. Crea un nuevo controlador en la carpeta `controllers/`
2. Agrega los modelos correspondientes en `models/`
3. Crea las vistas en `views/`
4. Actualiza el menú en `views/layouts/header.php` para incluir tu nuevo módulo
5. Agrega los archivos CSS y JS correspondientes en las carpetas `public/css/modules/` y `public/js/modules/`
6. Configura los permisos necesarios en la tabla `permiso` para el nuevo módulo

## Documentación

Este proyecto mantiene una documentación completa:

- **PHPDoc**: Todos los archivos PHP incluyen documentación estándar con `@package`, `@author` y descripción de funcionalidades
- **JSDoc**: Los módulos JavaScript personalizados están documentados con JSDoc
- **CHANGELOG**: Ver [CHANGELOG.md](CHANGELOG.md) para el historial completo de cambios
- **Versionado**: Seguimos [Semantic Versioning](https://semver.org/) para las versiones

### Estructura de Documentación

```php
/**
 * Descripción del archivo
 * 
 * @package ProyectoBase
 * @subpackage [Módulo]
 * @author Jandres25
 * @version 1.0
 */
```

## Versionado y Releases

- **Versión actual**: 1.1.0
- **Historial completo**: Ver [CHANGELOG.md](CHANGELOG.md)
- **Releases**: Ver [GitHub Releases](https://github.com/Jandres25/php-mvc-admin-starter/releases)

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Mantén la documentación actualizada (PHPDoc/JSDoc)
4. Commit tus cambios siguiendo [Conventional Commits](https://conventionalcommits.org/)
5. Push a la rama (`git push origin feature/AmazingFeature`)
6. Abre un Pull Request

### Estándares de Código

- Mantener documentación PHPDoc en todos los archivos PHP
- Documentar funciones JavaScript con JSDoc
- Seguir la estructura de packages existente
- Actualizar CHANGELOG.md para cambios significativos

## Seguridad

- Las contraseñas se almacenan usando `password_hash()` con el algoritmo PASSWORD_DEFAULT
- Protección CSRF en todos los formularios
- Verificación de sesiones para prevenir session hijacking
- Sanitización de entradas para prevenir inyección SQL y XSS

## Licencia

Este proyecto está disponible como código abierto bajo la licencia MIT.

## Changelog

Ver [CHANGELOG.md](CHANGELOG.md) para una lista detallada de cambios y versiones.