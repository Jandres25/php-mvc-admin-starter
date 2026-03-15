<?php

/**
 * Controlador de Autenticación
 * 
 * Gestiona las operaciones relacionadas con la autenticación de usuarios
 * 
 * @package ProyectoBase
 * @subpackage Controllers\Auth
 * @author Jandres25
 * @version 1.0
 */

namespace Controllers\Auth;

use Models\Usuario;

class AuthController
{
    /**
     * Modelo de Usuario
     * @var Usuario
     */
    private $modelo;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->modelo = new Usuario();

        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Muestra la página de login
     */
    public function showLoginForm()
    {
        // Incluir la vista del formulario de login
        require_once __DIR__ . '/../../views/login/login.php';
    }

    /**
     * Genera un token CSRF para proteger formularios
     * 
     * @return string Token CSRF
     */
    public function generarCSRFToken()
    {
        return generateCSRFToken();
    }

    /**
     * Verifica si el token CSRF es válido
     *
     * @param string $token Token CSRF a verificar
     * @return bool True si es válido, False en caso contrario
     */
    public function verificarCSRFToken($token)
    {
        return verifyCSRFToken($token);
    }

    /**
     * Procesa el formulario de login
     */
    public function login()
    {
        // Verificar si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Verificar token CSRF
            if (!$this->verificarCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['mensaje'] = 'Error de seguridad. Por favor, intente nuevamente.';
                $_SESSION['icono'] = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Validar datos
            $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
            $clave = isset($_POST['clave']) ? trim($_POST['clave']) : '';
            $errors = [];

            // Validaciones básicas
            if (empty($identifier)) {
                $errors[] = 'Debe ingresar un correo o número de documento';
            }

            if (empty($clave)) {
                $errors[] = 'Debe ingresar una contraseña';
            }

            // Si hay errores básicos, mostrar el primero y redirigir
            if (!empty($errors)) {
                $_SESSION['mensaje'] = $errors[0];
                $_SESSION['icono'] = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Determinar si el identificador es un correo o un número de documento
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

            // Verificar credenciales (no revelar si el usuario existe o no)
            if ($isEmail) {
                $usuario = $this->modelo->loginPorCorreo($identifier, $clave);
            } else {
                $usuario = $this->modelo->loginPorNumDocumento($identifier, $clave);
            }

            if (!$usuario) {
                $_SESSION['mensaje'] = 'Credenciales incorrectas';
                $_SESSION['icono'] = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Verificar si el usuario está activo
            if ((int)$usuario['estado'] === 0) {
                $_SESSION['mensaje'] = 'Su cuenta está desactivada. Contacte al administrador.';
                $_SESSION['icono'] = 'warning';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Iniciar sesión
            $this->iniciarSesion($usuario);

            $_SESSION['mensaje'] = 'Bienvenido al sistema ' . $_SESSION['usuario_nombre'];
            $_SESSION['icono'] = 'success';
            header('Location: ' . $GLOBALS['URL']);
            exit;
        }

        // Si no se envió el formulario, redirigir al login
        header('Location: ' . $GLOBALS['URL'] . 'views/login/login.php');
    }

    /**
     * Inicia la sesión del usuario
     *
     * @param array $usuario Datos del usuario
     */
    private function iniciarSesion($usuario)
    {
        // Regenerar ID de sesión para evitar session fixation
        session_regenerate_id(true);

        // Guardar datos del usuario en la sesión
        $_SESSION['usuario_id'] = $usuario['idusuario'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_correo'] = $usuario['correo'];
        $_SESSION['usuario_cargo'] = $usuario['cargo'];
        $_SESSION['usuario_imagen'] = $usuario['imagen'] ?? 'user_default.jpg';
        $_SESSION['autenticado'] = true;

        // Registrar último acceso
        $_SESSION['ultimo_acceso'] = time();

        // Registrar IP y User Agent para seguridad adicional
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        // Cachear permisos en sesión para evitar N+1 queries en cada carga de página
        if (strtolower($usuario['cargo']) === 'administrador') {
            $_SESSION['usuario_permisos'] = ['*'];
        } else {
            $authService = new \Services\AuthorizationService();
            $permisos = $authService->obtenerPermisosUsuario($usuario['idusuario']);
            $_SESSION['usuario_permisos'] = array_column($permisos, 'nombre');
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout()
    {
        // Eliminar todas las variables de sesión
        $_SESSION = array();

        // Destruir la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destruir la sesión
        session_destroy();

        // Redirigir al login
        header('Location: ' . $GLOBALS['URL'] . 'views/login/login.php');
        exit;
    }

}
