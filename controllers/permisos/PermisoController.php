<?php

/**
 * Controlador de Permisos
 * 
 * Gestiona las operaciones relacionadas con los permisos
 * 
 * @package ProyectoBase
 * @subpackage Controllers\Permisos
 * @author Jandres25
 * @version 1.0
 */

namespace Controllers\Permisos;

use Models\Permiso;

class PermisoController
{
    /**
     * Modelo de Permiso
     * @var Permiso
     */
    private $modelo;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->modelo = new Permiso();
    }

    /**
     * Muestra la lista de permisos
     * 
     * @param bool $soloActivos Si es true, solo muestra permisos activos
     * @return array Lista de permisos
     */
    public function index($soloActivos = false)
    {
        $permisos = $this->modelo->getAll($soloActivos);

        // Agregar conteo de usuarios a cada permiso
        foreach ($permisos as &$permiso) {
            $permiso['total_usuarios'] = $this->modelo->contarUsuarios($permiso['idpermiso']);
        }

        return $permisos;
    }

    /**
     * Crea un permiso vía AJAX
     * 
     * @return array Respuesta JSON con el resultado de la operación
     */
    public function crearAjax()
    {
        // Verificar si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        // Preparar datos del permiso
        $datos = [
            'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : ''
        ];

        // Sanitizar los datos
        $datos = $this->modelo->sanitizarDatos($datos);

        // Validar datos básicos
        if (empty($datos['nombre'])) {
            return ['success' => false, 'message' => 'El nombre del permiso es obligatorio'];
        }

        // Guardar permiso usando el modelo
        if ($this->modelo->crear($datos)) {
            return [
                'success' => true,
                'message' => 'Permiso creado correctamente',
                'permiso' => [
                    'idpermiso' => $this->modelo->getLastInsertId(),
                    'nombre' => $datos['nombre'],
                    'estado' => 1,
                    'total_usuarios' => 0 // Nuevos permisos no tienen usuarios
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Error al crear el permiso: ' . $this->modelo->getLastError()];
        }
    }

    /**
     * Obtiene un permiso por su ID
     * 
     * @param int $id ID del permiso
     * @return array|bool Datos del permiso o false si no existe
     */
    public function getById($id)
    {
        $permiso = $this->modelo->getById($id);
        if ($permiso) {
            $permiso['total_usuarios'] = $this->modelo->contarUsuarios($id);
            $permiso['usuarios'] = $this->modelo->getUsuariosPorPermiso($id);
        }
        return $permiso;
    }

    /**
     * Actualiza un permiso vía AJAX
     * 
     * @return array Respuesta JSON con el resultado de la operación
     */
    public function actualizarAjax()
    {
        // Verificar si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        // Obtener ID del permiso
        $id = isset($_POST['idpermiso']) ? (int)$_POST['idpermiso'] : 0;

        if (!$id) {
            return ['success' => false, 'message' => 'ID de permiso no válido'];
        }

        // Obtener datos actuales del permiso
        $permiso_actual = $this->modelo->getById($id);
        if (!$permiso_actual) {
            return ['success' => false, 'message' => 'Permiso no encontrado para actualizar'];
        }

        // Preparar datos del permiso
        $datos = [
            'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : $permiso_actual['nombre']
        ];

        // Sanitizar los datos
        $datos = $this->modelo->sanitizarDatos($datos);

        // Validar datos básicos
        if (empty($datos['nombre'])) {
            return ['success' => false, 'message' => 'El nombre del permiso es obligatorio'];
        }

        // Actualizar permiso
        if ($this->modelo->actualizar($id, $datos)) {
            return [
                'success' => true,
                'message' => 'Permiso actualizado correctamente',
                'permiso' => [
                    'idpermiso' => $id,
                    'nombre' => $datos['nombre'],
                    'estado' => $permiso_actual['estado'],
                    'total_usuarios' => $this->modelo->contarUsuarios($id)
                ]
            ];
        } else {
            $error_message = 'Error al actualizar el permiso: ' . $this->modelo->getLastError();
            return ['success' => false, 'message' => $error_message];
        }
    }

    /**
     * Cambia el estado de un permiso vía AJAX
     * 
     * @return array Respuesta JSON con el resultado de la operación
     */
    public function cambiarEstadoAjax()
    {
        // Verificar si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $estado_actual = isset($_POST['estado_actual']) ? (int)$_POST['estado_actual'] : null;

        if (!$id || $estado_actual === null) {
            return ['success' => false, 'message' => 'Datos inválidos para cambiar el estado del permiso'];
        }

        // Verificar si el permiso tiene usuarios antes de desactivar
        if ($estado_actual == 1 && $this->modelo->contarUsuarios($id) > 0) {
            return [
                'success' => false,
                'message' => 'No se puede desactivar el permiso porque hay usuarios que lo tienen asignado'
            ];
        }

        // El nuevo estado es el opuesto al actual
        $nuevo_estado = $estado_actual == 1 ? 0 : 1;

        if ($this->modelo->actualizarEstado($id, $nuevo_estado)) {
            $mensaje = $nuevo_estado == 1 ? 'activado' : 'desactivado';
            return [
                'success' => true,
                'message' => "Permiso $mensaje correctamente",
                'nuevo_estado' => $nuevo_estado
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al cambiar el estado del permiso: ' . $this->modelo->getLastError()
            ];
        }
    }

    /**
     * Obtiene los usuarios que tienen asignado un permiso específico
     * 
     * @param int $idPermiso ID del permiso
     * @return array Lista de usuarios
     */
    public function getUsuariosPorPermiso($idPermiso)
    {
        return $this->modelo->getUsuariosPorPermiso($idPermiso);
    }

    /**
     * Obtiene estadísticas de permisos
     * 
     * @return array Estadísticas
     */
    public function getEstadisticas()
    {
        return $this->modelo->getEstadisticas();
    }
}
