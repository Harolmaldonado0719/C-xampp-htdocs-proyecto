<?php

class SolicitudModel {
    private $conn; // Conexión PDO
    private $table_name = "solicitudes_atencion";
    private $table_usuarios = "usuarios"; // Para el JOIN

    public function __construct($db_connection) {
        if ($db_connection instanceof \PDO) {
            $this->conn = $db_connection;
        } else {
            error_log("SolicitudModel: Se esperaba una conexión PDO, se recibió: " . gettype($db_connection));
            $this->conn = null;
        }
    }

    public function crear($datos) {
        if ($this->conn === null) {
            error_log("SolicitudModel::crear: Conexión PDO no establecida.");
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  (usuario_id, tipo_solicitud, asunto, descripcion, email_contacto, estado, fecha_creacion, fecha_actualizacion)
                  VALUES (:usuario_id, :tipo_solicitud, :asunto, :descripcion, :email_contacto, :estado, NOW(), NOW())";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en SolicitudModel::crear al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return false;
            }
            
            $stmt->bindParam(':usuario_id', $datos['usuario_id'], PDO::PARAM_INT); 
            $stmt->bindParam(':tipo_solicitud', $datos['tipo_solicitud']); 
            $stmt->bindParam(':asunto', $datos['asunto']); 
            $stmt->bindParam(':descripcion', $datos['descripcion']); 
            $stmt->bindParam(':email_contacto', $datos['email_contacto']); 
            $stmt->bindParam(':estado', $datos['estado']);
            
            if ($stmt->execute()) {
                $lastId = $this->conn->lastInsertId();
                // $stmt->closeCursor(); // No es estrictamente necesario para lastInsertId y puede causar problemas en algunas configuraciones/drivers si se llama antes de tiempo.
                return $lastId;
            }
            error_log("Error en SolicitudModel::crear al ejecutar la consulta PDO: " . implode(" ", $stmt->errorInfo()));
            // $stmt->closeCursor(); // Si hubo error en execute, closeCursor es buena práctica si el statement sigue activo.
            return false;
        } catch (PDOException $e) {
            error_log("PDOException en SolicitudModel::crear: " . $e->getMessage() . " SQL: " . $query . " Datos: " . json_encode($datos));
            return false;
        }
    }

    public function obtenerPorUsuarioId($usuario_id) {
        if ($this->conn === null) {
            error_log("SolicitudModel::obtenerPorUsuarioId: Conexión PDO no establecida.");
            return [];
        }

        // CORRECCIÓN: Añadir respuesta_admin a la consulta SELECT
        $query = "SELECT id, tipo_solicitud, asunto, estado, fecha_creacion, fecha_actualizacion, respuesta_admin
                  FROM " . $this->table_name . "
                  WHERE usuario_id = :usuario_id
                  ORDER BY fecha_creacion DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en SolicitudModel::obtenerPorUsuarioId al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return [];
            }
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // $stmt->closeCursor(); // Buena práctica después de fetchAll
            return $solicitudes;
        } catch (PDOException $e) {
            error_log("PDOException en SolicitudModel::obtenerPorUsuarioId: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerPorId($id) {
        if ($this->conn === null) {
            error_log("SolicitudModel::obtenerPorId: Conexión PDO no establecida.");
            return null;
        }
        // Asegurarse de que todos los campos necesarios, incluyendo respuesta_admin, se seleccionen
        $query = "SELECT s.*, u.nombre as nombre_usuario, u.apellido as apellido_usuario 
                  FROM " . $this->table_name . " s
                  LEFT JOIN " . $this->table_usuarios . " u ON s.usuario_id = u.id 
                  WHERE s.id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en SolicitudModel::obtenerPorId al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return null;
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            $stmt->execute();
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            // $stmt->closeCursor(); // Buena práctica después de fetch
            return $solicitud ?: null; // Devolver null si no se encuentra
        } catch (PDOException $e) {
            error_log("PDOException en SolicitudModel::obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerTodas($limit = 25, $offset = 0) {
        if ($this->conn === null) {
            error_log("SolicitudModel::obtenerTodas: Conexión PDO no establecida.");
            return [];
        }

        // Asegurarse de que todos los campos necesarios, incluyendo respuesta_admin, se seleccionen
        $query = "SELECT s.id, s.usuario_id, u.nombre as nombre_cliente, u.apellido as apellido_cliente, 
                         s.tipo_solicitud, s.asunto, s.estado, s.fecha_creacion, s.fecha_actualizacion, s.respuesta_admin
                  FROM " . $this->table_name . " s
                  LEFT JOIN " . $this->table_usuarios . " u ON s.usuario_id = u.id 
                  ORDER BY s.fecha_creacion DESC
                  LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en SolicitudModel::obtenerTodas al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return [];
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // $stmt->closeCursor();
            return $solicitudes;
        } catch (PDOException $e) {
            error_log("PDOException en SolicitudModel::obtenerTodas: " . $e->getMessage());
            return [];
        }
    }

    public function actualizarRespuestaAdmin($id_solicitud, $respuesta_admin, $estado, $admin_id) {
        if ($this->conn === null) {
            error_log("SolicitudModel::actualizarRespuestaAdmin: Conexión PDO no establecida.");
            return false;
        }
    
        $query = "UPDATE " . $this->table_name . "
                  SET estado = :estado, respuesta_admin = :respuesta_admin, admin_id_respuesta = :admin_id, fecha_actualizacion = NOW()
                  WHERE id = :id_solicitud";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en SolicitudModel::actualizarRespuestaAdmin al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return false;
            }
            
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':respuesta_admin', $respuesta_admin);
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            
            $stmt->execute();
            $affected_rows = $stmt->rowCount();
            // $stmt->closeCursor();
            return $affected_rows > 0;
        } catch (PDOException $e) {
            error_log("PDOException en SolicitudModel::actualizarRespuestaAdmin: " . $e->getMessage());
            return false;
        }
    }
}
?>