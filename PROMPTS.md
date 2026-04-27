# PROMPTS.md — php-mvc-admin-starter

> Plantillas de prompts para el equipo. Úsalas como base — adapta los bloques
> `[Tarea]` y `[Contexto]` a lo que necesites en cada sesión.
> El `CLAUDE.md` siempre debe estar disponible para el agente como contexto base.

---

## Cómo usar este archivo

Cada plantilla sigue la estructura de 5 ejes del prompt profesional:

| Eje                   | Pregunta          | Para qué sirve                                    |
| --------------------- | ----------------- | ------------------------------------------------- |
| **Rol**               | ¿Quién eres?      | Define el nivel y especialidad que asume la IA    |
| **Contexto**          | ¿Dónde estamos?   | El proyecto, stack y módulo activo                |
| **Tarea exacta**      | ¿Qué necesitas?   | Concreto y específico — nunca genérico            |
| **Restricciones**     | ¿Qué límites hay? | Convenciones del proyecto que NO se pueden romper |
| **Formato de salida** | ¿Cómo lo quieres? | Estructura del output esperado                    |

> **Regla de oro:** Cuanto más específico sea el bloque `[Tarea]`,
> menos correcciones necesitarás después.

**Reglas de uso del equipo:**

- **Siempre carga el CLAUDE.md** al inicio de la sesión si la herramienta no lo carga automáticamente.
- **Un prompt por subtarea.** Pedir "el módulo completo" en un solo prompt produce resultados genéricos.
- **Si el output no encaja**, no corrijas manualmente primero — ajusta `[Restricciones]` y repite.
- **El spec antes que el código.** Define qué debe hacer antes de pedir que lo implemente.
- **Guarda los prompts que funcionen bien** en este archivo como nuevas plantillas para el equipo.

---

## Plantilla base (copia esto y rellena)

```
[Rol]
Actúa como desarrollador PHP Senior especializado en arquitectura MVC
y patrones de diseño.

[Contexto]
Proyecto: php-mvc-admin-starter — PHP MVC custom (sin framework, sin Composer).
Stack: AdminLTE 3, Bootstrap 4, jQuery, DataTables, SweetAlert2, Select2, PDO/MySQL.
Módulo activo: _______________

[Tarea]
_______________

[Restricciones]
- Seguir el patrón MVC existente (referencia: módulo de users)
- Todos los controllers extienden App\Core\Controller; todos los models extienden App\Core\Model
- Las rutas se declaran en routes/web.php con middleware 'auth', 'guest' o 'perm:NAME'
- CSRF obligatorio: generateCSRFToken() en vistas, $this->csrfCheck() en endpoints POST, regenerateCSRFToken() tras cada POST exitoso
- Input sanitization con trim() solo en el modelo (sanitizeData()); htmlspecialchars() solo en vistas
- Passwords: password_hash(PASSWORD_DEFAULT) al guardar, password_verify() al validar
- Uploads: siempre a través de ImageService
- AJAX: devolver JSON con $this->jsonResponse(); sesión-feedback con $_SESSION['message'] + $_SESSION['icon'] antes del JSON si hay location.reload()
- DataTables + SweetAlert2 para listas y confirmaciones — nunca alert() o confirm() nativo
- Select2 en modales: inicializar con dropdownParent explícito
- No introducir librerías nuevas sin aprobación del líder técnico

[Formato de salida]
_______________
```

---

## Plantilla 1 — Generar código nuevo (feature)

Usar cuando: implementar un requerimiento nuevo.

```
[Rol]
Actúa como desarrollador PHP Senior especializado en arquitectura MVC
y patrones de diseño.

[Contexto]
Proyecto: php-mvc-admin-starter — PHP MVC custom (sin framework, sin Composer).
Stack: AdminLTE 3, Bootstrap 4, jQuery, DataTables, SweetAlert2, Select2, PDO/MySQL.
Módulo activo: [nombre del módulo — ej: users, permissions, dashboard]

Estructura de archivos relevante:
- app/controllers/[modulo]/[Modulo]Controller.php
- app/models/[Modulo].php
- views/[modulo]/[vista].php
- public/js/modules/[modulo]/[script].js
- routes/web.php (registrar rutas nuevas aquí)

[Tarea]
Implementar [nombre exacto del requerimiento].

Descripción: [criterios de aceptación]

[Restricciones]
- Seguir el patrón MVC del módulo de users como referencia
- Rutas declaradas en routes/web.php con los middlewares correspondientes
- CSRF en todos los formularios POST y endpoints AJAX
- Sanitización en el modelo, escape en la vista
- AJAX endpoints devuelven JSON via $this->jsonResponse()
- Feedback post-reload: $_SESSION['message'] + $_SESSION['icon'] antes del JSON
- DataTables para listados; SweetAlert2 para confirmaciones destructivas
- Select2 para dropdowns; con dropdownParent si está dentro de un modal
- Permisos gateados con AuthorizationService::hasPermissionByName() o middleware perm:NAME
- No inventar métodos de core que no existan en app/core/

[Formato de salida]
Devuelve en este orden:
1. Lista de archivos que se crean o modifican
2. Rutas a agregar en routes/web.php
3. Código de cada archivo (con comentario solo donde la lógica no sea obvia)
4. Queries SQL si hay cambios en BD
5. Checklist de testing manual (casos exitosos + edge cases)
```

---

## Plantilla 2 — Debuggear un error

Usar cuando: algo no funciona y no está claro por qué.

```
[Rol]
Actúa como desarrollador PHP Senior especializado en debugging
de aplicaciones MVC y MySQL.

[Contexto]
Proyecto: php-mvc-admin-starter — PHP MVC custom (sin framework, sin Composer).
Stack: PHP 8.2+, PDO/MySQL, jQuery, AdminLTE 3.
Archivo donde ocurre el error: [ruta completa]
Método/función afectada: [nombre]

[Tarea]
Tengo este error:
[pega el mensaje de error exacto o el comportamiento inesperado]

Código actual:
[pega el bloque de código relevante — no todo el archivo]

Lo que debería hacer:
[describe el comportamiento esperado]

Lo que intenté que no funciona:
[describe lo que ya probaste]

[Restricciones]
- No cambiar la arquitectura del archivo — solo corregir el problema específico
- Mantener las convenciones de naming del proyecto
- Si el fix requiere cambiar más de un archivo, indicarlo antes de proponer código

[Formato de salida]
1. Diagnóstico: causa raíz del error en 2-3 líneas
2. Fix: código corregido con comentario explicando el cambio
3. Por qué pasó: explicación breve para no repetirlo
```

---

## Plantilla 3 — Code review antes del merge

Usar cuando: antes de hacer merge de una rama, o cuando el código funciona
pero algo "huele mal".

```
[Rol]
Actúa como Tech Lead PHP con experiencia en code review de sistemas MVC,
seguridad web y patrones de diseño.

[Contexto]
Proyecto: php-mvc-admin-starter — PHP MVC custom.
Rama revisada: feature/[nombre]
Cambio implementado: [descripción breve]

[Tarea]
Revisa el siguiente código antes del merge.

[pega el código o el diff del PR]

[Restricciones]
Evalúa específicamente:
- Seguridad: SQL injection (prepared statements), XSS (htmlspecialchars en vistas), CSRF (csrfCheck + regenerateCSRFToken), sesiones mal validadas
- Convenciones: namespaces App\*, naming, estructura MVC, rutas en routes/web.php
- Lógica: sanitización en modelo, escape en vista, ImageService para uploads
- AJAX: jsonResponse() usado correctamente, feedback de sesión si hay reload
- Permisos: AuthorizationService o middleware perm:NAME para todo endpoint sensible
- Casos edge que podrían fallar en producción

[Formato de salida]
Responde con esta estructura:
OK  - Lo que está bien (menciona al menos 2 cosas)
OBS - Observaciones (mejoras no críticas, con sugerencia)
FIX - Problemas a corregir antes del merge (con código corregido)
```

---

## Plantilla 4 — Consulta de arquitectura

Usar cuando: hay una decisión técnica importante antes de implementar,
o cuando no está claro cómo integrar algo nuevo.

```
[Rol]
Actúa como arquitecto de software PHP con experiencia en sistemas MVC
custom, diseño de base de datos y patrones de diseño (Repository, Service Layer).

[Contexto]
Proyecto: php-mvc-admin-starter — PHP MVC custom (sin framework, sin Composer).
Stack actual: App\Core\Router, middleware por ruta, PSR-4 custom autoloader,
              PDO singleton (Connection::getInstance()), AuthorizationService.
Módulos existentes: auth, users, permissions, dashboard.

[Tarea]
Necesito decidir: [describe la decisión técnica]

Opciones que estoy considerando:
- Opción A: [describe]
- Opción B: [describe]

[Restricciones]
- No introducir frameworks (ni Laravel, ni Symfony)
- No introducir Composer ni gestores de dependencias externos
- Mantener compatibilidad con Router, Connection singleton y el autoloader actual
- La solución debe poder implementarla un dev junior sin romper lo que existe
- Cualquier nuevo Service debe vivir en app/services/ y seguir el patrón de AuthorizationService

[Formato de salida]
1. Recomendación directa (cuál opción y por qué en 3 líneas)
2. Trade-offs de cada opción (tabla si aplica)
3. Impacto en el resto del sistema
4. Primeros pasos concretos para implementar la opción recomendada
```

---

## Plantilla 5 — Nuevo módulo completo (spec-first)

Usar cuando: se va a implementar un módulo nuevo de principio a fin.
Completar el spec antes de pedir código.

```
[Rol]
Actúa como desarrollador PHP Senior especializado en arquitectura MVC,
diseño de base de datos y seguridad web.

[Contexto]
Proyecto: php-mvc-admin-starter — PHP MVC custom (sin framework, sin Composer).
Stack: AdminLTE 3, Bootstrap 4, jQuery, DataTables, SweetAlert2, Select2, PDO/MySQL.
Módulo nuevo: [nombre]

BD existente relevante:
- users (user_id, name, last_name, email, password, document, role, is_active, ...)
- permissions (permission_id, name, description, is_active)
- user_permissions (user_id, permission_id)

Módulo de referencia para patrones: users.

[Tarea]
Implementar el módulo [nombre] con las siguientes funcionalidades:
[lista de operaciones: CRUD, toggles, AJAX, etc.]

Criterios de aceptación:
[pega los criterios]

[Restricciones]
- Seguir el patrón MVC del módulo users como referencia exacta
- Rutas declaradas en routes/web.php con middlewares auth + perm:[nombre] donde aplique
- CSRF en todos los formularios POST y endpoints AJAX
- DataTables para listados, SweetAlert2 para confirmaciones, Select2 para dropdowns
- Borrado lógico con is_active — nunca DELETE físico en tablas de negocio
- AJAX: $this->jsonResponse(); feedback de sesión si el JS hace location.reload()
- Permisos gateados con AuthorizationService o middleware perm:NAME
- Registrar el permiso nuevo en database/seeder.sql
- PHPDoc en clases y métodos; JSDoc en funciones JS
- No introducir librerías nuevas

[Formato de salida]
Devuelve en este orden:
1. SQL: ALTER/CREATE TABLE + INSERT en seeder para el permiso nuevo
2. Rutas a agregar en routes/web.php
3. app/models/[Modulo].php
4. app/controllers/[modulo]/[Modulo]Controller.php
5. views/[modulo]/index.php
6. views/[modulo]/create.php (si aplica)
7. views/[modulo]/update.php (si aplica)
8. public/js/modules/[modulo]/index-[modulo].js
9. public/js/modules/[modulo]/modal-[modulo].js (si hay modales AJAX)
10. Checklist de testing manual
```

---

_Última actualización: v3.1.0_
_Mantener sincronizado con CLAUDE.md al iniciar cada sprint._
