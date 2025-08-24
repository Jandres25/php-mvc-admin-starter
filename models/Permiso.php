<?php

/**
 * Modelo Permiso
 * 
 * Gestiona las operaciones relacionadas con los permisos en la base de datos
 * 
 * @package ProyectoBase
 * @subpackage Models
 * @author Jandres25
 * @version 1.0
 */

namespace Models;

use Config\Conexion;
use PDO;
use PDOException;

require_once __DIR__ . '/../config/conexion.php';

class Permiso
{
    /**
     * Conexión a la base de datos
     * @var PDO
     */
    private $conexion;

    /**
     * Tabla de permisos en la base de datos
     * @var string
     */
    private $tabla = 'permiso';

    /**
     * Último error ocurrido
     * @var string
     */
    private $lastError = '';

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->conexion = Conexion::getInstance()->getConnection();
    }

    /**
     * Obtiene el último error ocurrido
     * 
     * @return string Mensaje de error
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Sanitiza los datos de entrada para prevenir inyección SQL y XSS
     * 
     * @param array $datos Datos a sanitizar
     * @return array Datos sanitizados
     */
    public function sanitizarDatos($datos)
    {
        $sanitized = [];
        foreach ($datos as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Obtiene todos los permisos
     * 
     * @param bool $soloActivos Si es true, solo devuelve permisos activos
     * @return array Lista de permisos
     */
    public function getAll($soloActivos = false)
    {
        try {
            $query = "SELECT * FROM {$this->tabla}";

            if ($soloActivos) {
                $query .= " WHERE estado = 1";
            }

            $query .= " ORDER BY nombre";

            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
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
        try {
            $query = "SELECT * FROM {$this->tabla} WHERE idpermiso = :id";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Crea un nuevo permiso
     * 
     * @param array $datos Datos del permiso (solo nombre)
     * @return bool True si se creó correctamente, False en caso contrario
     */
    public function crear($datos)
    {
        try {
            // Verificar si ya existe un permiso con el mismo nombre
            if ($this->existeNombre($datos['nombre'])) {
                $this->lastError = 'Ya existe un permiso con este nombre';
                return false;
            }

            $query = "INSERT INTO {$this->tabla} (nombre) VALUES (:nombre)";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Actualiza un permiso existente
     * 
     * @param int $id ID del permiso
     * @param array $datos Datos del permiso (solo nombre)
     * @return bool True si se actualizó correctamente, False en caso contrario
     */
    public function actualizar($id, $datos)
    {
        try {
            // Verificar si ya existe un permiso con el mismo nombre (excluyendo el actual)
            if ($this->existeNombre($datos['nombre'], $id)) {
                $this->lastError = 'Ya existe un permiso con este nombre';
                return false;
            }

            $query = "UPDATE {$this->tabla} SET nombre = :nombre WHERE idpermiso = :id";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Actualiza el estado de un permiso
     * 
     * @param int $id ID del permiso
     * @param int $estado Nuevo estado (1: activo, 0: inactivo)
     * @return bool True si se actualizó correctamente, False en caso contrario
     */
    public function actualizarEstado($id, $estado)
    {
        try {
            $sql = "UPDATE {$this->tabla} SET estado = :estado WHERE idpermiso = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Verifica si existe un permiso con el mismo nombre
     * 
     * @param string $nombre Nombre del permiso
     * @param int $id_excluir ID del permiso a excluir de la verificación (opcional)
     * @return bool True si existe, False en caso contrario
     */
    public function existeNombre($nombre, $id_excluir = null)
    {
        try {
            if ($id_excluir) {
                $query = "SELECT COUNT(*) FROM {$this->tabla} WHERE nombre = :nombre AND idpermiso != :id";
                $stmt = $this->conexion->prepare($query);
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id_excluir, PDO::PARAM_INT);
            } else {
                $query = "SELECT COUNT(*) FROM {$this->tabla} WHERE nombre = :nombre";
                $stmt = $this->conexion->prepare($query);
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Obtiene el número de usuarios que tienen asignado un permiso
     * 
     * @param int $idPermiso ID del permiso
     * @return int Número de usuarios
     */
    public function contarUsuarios($idPermiso)
    {
        try {
            $query = "SELECT COUNT(*) FROM permisousuario WHERE idpermiso = :id";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':id', $idPermiso, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
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
        try {
            $query = "SELECT u.idusuario, u.nombre, u.apellidopaterno, u.apellidomaterno, u.correo, u.cargo, u.estado
                     FROM usuarios u
                     INNER JOIN permisousuario pu ON u.idusuario = pu.idusuario
                     WHERE pu.idpermiso = :idpermiso
                     ORDER BY u.nombre, u.apellidopaterno";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':idpermiso', $idPermiso, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Obtiene estadísticas de permisos (total, activos, inactivos)
     * 
     * @return array Estadísticas
     */
    public function getEstadisticas()
    {
        try {
            // Total de permisos
            $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM {$this->tabla}");
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Permisos activos
            $stmt = $this->conexion->prepare("SELECT COUNT(*) as activos FROM {$this->tabla} WHERE estado = 1");
            $stmt->execute();
            $activos = $stmt->fetch(PDO::FETCH_ASSOC)['activos'];

            // Permisos inactivos
            $stmt = $this->conexion->prepare("SELECT COUNT(*) as inactivos FROM {$this->tabla} WHERE estado = 0");
            $stmt->execute();
            $inactivos = $stmt->fetch(PDO::FETCH_ASSOC)['inactivos'];

            // Permisos más utilizados
            $query = "SELECT p.idpermiso, p.nombre, COUNT(pu.idusuario) as total_usuarios
                     FROM permiso p
                     LEFT JOIN permisousuario pu ON p.idpermiso = pu.idpermiso
                     GROUP BY p.idpermiso
                     ORDER BY total_usuarios DESC
                     LIMIT 5";

            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            $mas_utilizados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total' => $total,
                'activos' => $activos,
                'inactivos' => $inactivos,
                'mas_utilizados' => $mas_utilizados
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [
                'total' => 0,
                'activos' => 0,
                'inactivos' => 0,
                'mas_utilizados' => []
            ];
        }
    }

    /**
     * Obtiene el ID del último permiso insertado
     * 
     * @return int ID del último permiso insertado
     */
    public function getLastInsertId()
    {
        try {
            return $this->conexion->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }
}
