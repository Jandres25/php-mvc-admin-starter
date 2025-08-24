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
        global $URL;
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

        // Guardar imagen actual
        $imagen_antigua = $usuario_actual['imagen'];

        // CAMPOS PERMITIDOS: Solo recoger campos que el usuario puede modificar desde su perfil
        $datos = [
            'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
            'apellidopaterno' => isset($_POST['apellidopaterno']) ? trim($_POST['apellidopaterno']) : '',
            'apellidomaterno' => isset($_POST['apellidomaterno']) ? trim($_POST['apellidomaterno']) : null,
            'direccion' => isset($_POST['direccion']) ? trim($_POST['direccion']) : null,
            'telefono' => isset($_POST['telefono']) ? trim($_POST['telefono']) : null,
            'correo' => isset($_POST['correo']) ? trim($_POST['correo']) : '',
            'imagen' => $imagen_antigua
        ];

        // Sanitizar los datos
        $datos = $this->modelo->sanitizarDatos($datos);

        // Validación específica para el perfil
        $errores = $this->validarDatosPerfil($datos, $id, $usuario_actual);

        if (!empty($errores)) {
            return ['success' => false, 'message' => $errores[0], 'icon' => 'error', 'redirect' => 'views/perfil/mi-perfil.php'];
        }

        // Procesar nueva imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $nueva_imagen_path = $this->imagenService->procesarImagen($_FILES['imagen']);
            if ($nueva_imagen_path) {
                $datos['imagen'] = $nueva_imagen_path;

                // Eliminar imagen anterior si no es la predeterminada
                if ($imagen_antigua && $imagen_antigua !== 'user_default.jpg' && $imagen_antigua !== $nueva_imagen_path) {
                    $this->imagenService->eliminarImagen($imagen_antigua);
                }
            } else {
                return ['success' => false, 'message' => 'Error al procesar la nueva imagen. Verifique el formato y tamaño.', 'icon' => 'error', 'redirect' => 'views/perfil/mi-perfil.php'];
            }
        }

        // Usar el método específico para actualizar el perfil
        if ($this->modelo->actualizarPerfil($id, $datos)) {
            // Actualizar datos de sesión
            $_SESSION['usuario_nombre'] = $datos['nombre'];
            $_SESSION['usuario_correo'] = $datos['correo'];
            if (isset($datos['imagen']) && $datos['imagen'] !== $imagen_antigua) {
                $_SESSION['usuario_imagen'] = $datos['imagen'];
            }
            return ['success' => true, 'message' => 'Perfil actualizado correctamente', 'icon' => 'success', 'redirect' => 'views/perfil/mi-perfil.php'];
        } else {
            $db_error = $this->modelo->getLastError();
            return ['success' => false, 'message' => 'Error al actualizar el perfil: ' . ($db_error ?: 'Error desconocido.'), 'icon' => 'error', 'redirect' => 'views/perfil/mi-perfil.php'];
        }
    }

    /**
     * Valida los datos del perfil antes de actualizar
     * 
     * @param array $datos Datos del perfil a validar
     * @param int $id ID del usuario
     * @param array $usuario_actual Datos actuales del usuario
     * @return array Lista de errores encontrados
     */
    private function validarDatosPerfil($datos, $id, $usuario_actual)
    {
        $errores = [];

        // Validar campos obligatorios
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre no puede estar vacío.';
        }

        if (empty($datos['apellidopaterno'])) {
            $errores[] = 'El apellido paterno no puede estar vacío.';
        }

        // Validar correo
        if (empty($datos['correo'])) {
            $errores[] = 'El correo electrónico no puede estar vacío.';
        } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico no es válido.';
        } elseif ($datos['correo'] !== $usuario_actual['correo'] && $this->modelo->existeCorreo($datos['correo'], $id)) {
            $errores[] = 'El correo electrónico ya está registrado para otro usuario.';
        }

        // Validar longitud de los campos
        if (strlen($datos['nombre']) > 255) {
            $errores[] = 'El nombre no debe exceder los 255 caracteres.';
        }

        if (strlen($datos['apellidopaterno']) > 255) {
            $errores[] = 'El apellido paterno no debe exceder los 255 caracteres.';
        }

        if (!empty($datos['apellidomaterno']) && strlen($datos['apellidomaterno']) > 255) {
            $errores[] = 'El apellido materno no debe exceder los 255 caracteres.';
        }

        if (!empty($datos['telefono']) && strlen($datos['telefono']) > 15) {
            $errores[] = 'El número de teléfono no debe exceder los 15 caracteres.';
        }

        if (!empty($datos['direccion']) && strlen($datos['direccion']) > 255) {
            $errores[] = 'La dirección no debe exceder los 255 caracteres.';
        }

        return $errores;
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
