<?php


class ServicioModel {
    private $conn; // Esta será una conexión PDO
    private $table_name = 'servicios';

    public $id;
    public $nombre;
    public $descripcion;
    public $duracion_minutos;
    public $precio;
    public $activo;
    public $fecha_creacion;
    public $fecha_actualizacion;

    public function __construct($db) { // $db debe ser una instancia de PDO
        if ($db instanceof \PDO) {
            $this->conn = $db;
        } else {
            // Manejar el error apropiadamente si no es una conexión PDO
            // Esto es una salvaguarda, CitaController ya debería pasar PDO.
            error_log("ServicioModel: Se esperaba una conexión PDO, se recibió: " . gettype($db));
            throw new \InvalidArgumentException("Se esperaba una conexión PDO para ServicioModel.");
        }
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion, duracion_minutos, precio, activo) 
                  VALUES (:nombre, :descripcion, :duracion_minutos, :precio, :activo)";

        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("ServicioModel::crear - Error al preparar la consulta: " . implode(", ", $this->conn->errorInfo()) . " SQL: " . $query);
            return false;
        }

        // Limpiar datos (aunque la validación principal debería estar en el controlador)
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->duracion_minutos = intval($this->duracion_minutos);
        $this->precio = floatval($this->precio);
        $this->activo = intval($this->activo);

        // Vincular datos
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":duracion_minutos", $this->duracion_minutos, \PDO::PARAM_INT);
        $stmt->bindParam(":precio", $this->precio); // PDO puede manejar float/double sin tipo específico si el driver lo soporta bien
        $stmt->bindParam(":activo", $this->activo, \PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId(); // Obtener el ID del nuevo servicio con PDO
                return true;
            }
            error_log("ServicioModel::crear - Error al ejecutar: " . implode(", ", $stmt->errorInfo()));
            return false;
        } catch (\PDOException $e) { 
            error_log("ServicioModel::crear - PDOException: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
            return false;
        }
    }

    public function obtenerTodos() {
        $query = "SELECT id, nombre, descripcion, duracion_minutos, precio, activo, fecha_creacion, fecha_actualizacion 
                  FROM " . $this->table_name . " ORDER BY id ASC"; 
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("ServicioModel::obtenerTodos - Error al preparar la consulta: " . implode(", ", $this->conn->errorInfo()));
                return [];
            }
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC); // Usar fetchAll con PDO
        } catch (\PDOException $e) {
            error_log("ServicioModel::obtenerTodos - PDOException: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTodosActivos() {
        $query = "SELECT id, nombre, descripcion, duracion_minutos, precio, activo, fecha_creacion, fecha_actualizacion 
                  FROM " . $this->table_name . " WHERE activo = 1 ORDER BY id ASC"; 
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("ServicioModel::obtenerTodosActivos - Error al preparar la consulta: " . implode(", ", $this->conn->errorInfo()));
                return [];
            }
            $stmt->execute();
            // Esta es la línea 107 que causaba el error. Ahora usa fetchAll.
            return $stmt->fetchAll(\PDO::FETCH_ASSOC); 
        } catch (\PDOException $e) {
            error_log("ServicioModel::obtenerTodosActivos - PDOException: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId($id) {
        $query = "SELECT id, nombre, descripcion, duracion_minutos, precio, activo, fecha_creacion, fecha_actualizacion 
                  FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("ServicioModel::obtenerPorId - Error al preparar la consulta: " . implode(", ", $this->conn->errorInfo()));
                return null;
            }

            $id_filtrado = filter_var($id, FILTER_VALIDATE_INT);
            if ($id_filtrado === false) {
                error_log("ServicioModel::obtenerPorId - ID inválido: " . $id);
                return null;
            }
            
            $stmt->bindParam(":id", $id_filtrado, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC); // Usar fetch con PDO
            return $row ?: null;
        } catch (\PDOException $e) {
            error_log("ServicioModel::obtenerPorId - PDOException: " . $e->getMessage());
            return null;
        }
    }

    public function actualizar() {
        $query = "UPDATE " . $this->table_name . "
                  SET nombre = :nombre,
                      descripcion = :descripcion,
                      duracion_minutos = :duracion_minutos, 
                      precio = :precio,
                      activo = :activo 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("ServicioModel::actualizar - Error al preparar la consulta: " . implode(", ", $this->conn->errorInfo()));
            return false;
        }

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->duracion_minutos = intval($this->duracion_minutos);
        $this->precio = floatval($this->precio);
        $this->activo = intval($this->activo);
        $this->id = intval($this->id);
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":duracion_minutos", $this->duracion_minutos, \PDO::PARAM_INT);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":activo", $this->activo, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                // $stmt->rowCount() devuelve el número de filas afectadas en PDO para UPDATE/DELETE
                return $stmt->rowCount() >= 0; 
            }
            error_log("ServicioModel::actualizar - Error al ejecutar: " . implode(", ", $stmt->errorInfo()));
            return false;
        } catch (\PDOException $e) {
            error_log("ServicioModel::actualizar - PDOException: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id) {
        // Esta función cambia el estado 'activo' a 0 (eliminación suave)
        $query = "UPDATE " . $this->table_name . " SET activo = 0 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("ServicioModel::eliminar (desactivar) - Error al preparar la consulta: " . implode(", ", $this->conn->errorInfo()));
            return false;
        }

        $id_filtrado = filter_var($id, FILTER_VALIDATE_INT);
        if ($id_filtrado === false) {
            error_log("ServicioModel::eliminar (desactivar) - ID inválido: " . $id);
            return false;
        }
        
        $stmt->bindParam(':id', $id_filtrado, \PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() >= 0;
            }
            error_log("ServicioModel::eliminar (desactivar) - Error al ejecutar: " . implode(", ", $stmt->errorInfo()));
            return false;
        } catch (\PDOException $e) {
            error_log("ServicioModel::eliminar (desactivar) - PDOException: " . $e->getMessage());
            return false;
        }
    }
}
?>