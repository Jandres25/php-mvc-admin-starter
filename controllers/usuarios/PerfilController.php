<?php

/**
 * Controlador de Perfil
 * 
 * Gestiona las operaciones relacionadas con el perfil del usuario autenticado
 * 
 * @package ProyectoBase
 * @subpackage Controllers\Usuarios
 * @author Jandres25
 * @version 1.0
 */

namespace Controllers\Usuarios;

use Models\Usuario;
use Services\ImagenService;

class PerfilController
{
    /**
     * Modelo de Usuario
     * @var Usuario
     */
    private $modelo;

    /**
     * Servicio de imágenes
     * @var ImagenService
     */
    private $imagenService;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->modelo = new Usuario();

        // Inicializar el servicio de imágenes
        $this->imagenService = new ImagenService(__DIR__ . '/../../public/uploads/usuarios/');

        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Muestra el perfil del usuario autenticado
     * 
     * @return array|null Datos del usuario o redirige en caso de error
     */
    public function mostrarPerfil()
    {
        global $URL;

        // Verificar si el usuario está logueado
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['mensaje'] = 'Debe iniciar sesión para acceder a su perfil.';
            $_SESSION['icono'] = 'warning';
            header('Location: ' . $URL . 'views/login/login.php');
            exit;
        }

        $id = $_SESSION['usuario_id'];

        // Obtener datos del usuario
        $usuario = $this->modelo->getById($id);

        if (!$usuario) {
            $_SESSION['mensaje'] = 'Usuario no encontrado.';
            $_SESSION['icono'] = 'error';
            header('Location: ' . $URL . 'index.php');
            exit;
        }

        // Devolver los datos del usuario
        return $usuario;
    }

    /**
     * Procesa el formulario para actualizar los datos del perfil del usuario logueado
     */
    public function actualizarPerfil()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Acceso no permitido.', 'icon' => 'warning', 'redirect' => 'views/perfil/mi-perfil.php'];
        }

        if (!isset($_SESSION['usuario_id'])) {
            return ['success' => false, 'message' => 'Sesión no iniciada.', 'icon' => 'error', 'redirect' => 'views/login/login.php'];
        }

        $id = $_SESSION['usuario_id'];

        // Obtener datos actuales del usuario
        $usuario_actual = $this->modelo->getById($id);
        if (!$usuario_actual) {
            return ['success' => false, 'message' => 'Usuario no encontrado para actualizar', 'icon' => 'error', 'redirect' => 'views/perfil/mi-perfil.php'];
        }

        $imagen_antigua = $usuario_actual['imagen'];

        $datos = [
            'telefono'  => !empty($_POST['telefono'])  ? htmlspecialchars(trim($_POST['telefono']),  ENT_QUOTES, 'UTF-8') : null,
            'direccion' => !empty($_POST['direccion']) ? htmlspecialchars(trim($_POST['direccion']), ENT_QUOTES, 'UTF-8') : null,
            'imagen'    => $imagen_antigua,
        ];

        // Validar campos del perfil
        $errores = $this->modelo->validarDatosPerfil($datos);
        if (!empty($errores)) {
            return ['success' => false, 'message' => implode(' ', $errores), 'icon' => 'warning', 'redirect' => 'views/usuarios/perfil.php'];
        }

        // Procesar nueva imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $nueva_imagen_path = $this->imagenService->procesarImagen($_FILES['imagen']);
            if ($nueva_imagen_path) {
                $datos['imagen'] = $nueva_imagen_path;

                if ($imagen_antigua && $imagen_antigua !== 'user_default.jpg') {
                    $this->imagenService->eliminarImagen($imagen_antigua);
                }
            } else {
                return ['success' => false, 'message' => 'Error al procesar la imagen. Verifique el formato (JPG, PNG, WEBP) y que no supere 5 MB.', 'icon' => 'error', 'redirect' => 'views/usuarios/perfil.php'];
            }
        }

        if ($this->modelo->actualizarPerfil($id, $datos)) {
            if ($datos['imagen'] !== $imagen_antigua) {
                $_SESSION['usuario_imagen'] = $datos['imagen'];
            }
            return ['success' => true, 'message' => 'Perfil actualizado correctamente.', 'icon' => 'success', 'redirect' => 'views/usuarios/perfil.php'];
        }

        return ['success' => false, 'message' => 'Error al actualizar el perfil: ' . ($this->modelo->getLastError() ?: 'Error desconocido.'), 'icon' => 'error', 'redirect' => 'views/usuarios/perfil.php'];
    }

    /**
     * Procesa el formulario para actualizar la contraseña del perfil con AJAX
     * 
     * @return array Respuesta con el resultado de la operación
     */
    public function actualizarClavePerfilAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Acceso no permitido.'];
        }

        if (!isset($_SESSION['usuario_id'])) {
            return ['success' => false, 'message' => 'Sesión no iniciada.'];
        }

        $id = $_SESSION['usuario_id'];

        // Validar datos
        $clave_actual = isset($_POST['clave_actual']) ? trim($_POST['clave_actual']) : '';
        $nueva_clave = isset($_POST['nueva_clave']) ? trim($_POST['nueva_clave']) : '';
        $confirmar_nueva_clave = isset($_POST['confirmar_nueva_clave']) ? trim($_POST['confirmar_nueva_clave']) : '';

        if (empty($clave_actual) || empty($nueva_clave) || empty($confirmar_nueva_clave)) {
            return ['success' => false, 'message' => 'Todos los campos de contraseña son obligatorios.'];
        }

        // Verificar contraseña actual
        if (!$this->modelo->verificarContrasenaActual($id, $clave_actual)) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
        }

        if ($nueva_clave !== $confirmar_nueva_clave) {
            return ['success' => false, 'message' => 'Las nuevas contraseñas no coinciden.'];
        }

        if (strlen($nueva_clave) < 6) {
            return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.'];
        }

        if ($this->modelo->actualizarClave($id, $nueva_clave)) {
            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente.'
            ];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar la contraseña: ' . $this->modelo->getLastError()];
        }
    }
}
