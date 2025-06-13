<?php

class NotificacionModel {
    private $conn; // Conexión PDO
    private $table_name = "notificaciones";

    public function __construct($db_connection) {
        if ($db_connection instanceof \PDO) {
            $this->conn = $db_connection;
        } else {
            error_log("NotificacionModel: Se esperaba una conexión PDO, se recibió: " . gettype($db_connection));
            $this->conn = null;
        }
    }

    /**
     * Crea una nueva notificación.
     *
     * @param int $usuario_id_destino ID del usuario que recibirá la notificación.
     * @param string $mensaje El contenido de la notificación.
     * @param string $tipo Tipo de notificación (ej. 'cita_confirmada', 'info').
     * @param string|null $enlace URL opcional a la que dirige la notificación.
     * @return int|false El ID de la notificación creada o false en caso de error.
     */
    public function crear($usuario_id_destino, $mensaje, $tipo = 'info', $enlace = null) {
        if ($this->conn === null) {
            error_log("NotificacionModel::crear: Conexión PDO no establecida.");
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  (usuario_id_destino, mensaje, tipo, enlace, fecha_creacion)
                  VALUES (:usuario_id_destino, :mensaje, :tipo, :enlace, NOW())";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en NotificacionModel::crear al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return false;
            }
            
            $stmt->bindParam(':usuario_id_destino', $usuario_id_destino, PDO::PARAM_INT);
            $stmt->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
            $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindParam(':enlace', $enlace, $enlace === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $lastId = $this->conn->lastInsertId();
                $stmt->closeCursor();
                return $lastId;
            }
            error_log("Error en NotificacionModel::crear al ejecutar la consulta PDO: " . implode(" ", $stmt->errorInfo()));
            $stmt->closeCursor();
            return false;
        } catch (PDOException $e) {
            error_log("PDOException en NotificacionModel::crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las notificaciones de un usuario (leídas y no leídas).
     *
     * @param int $usuario_id_destino ID del usuario.
     * @return array Arreglo de notificaciones.
     */
    public function obtenerPorUsuarioId($usuario_id_destino) {
        if ($this->conn === null) {
            error_log("NotificacionModel::obtenerPorUsuarioId: Conexión PDO no establecida.");
            return [];
        }

        $query = "SELECT id, usuario_id_destino, mensaje, tipo, enlace, fecha_creacion, fecha_lectura
                  FROM " . $this->table_name . "
                  WHERE usuario_id_destino = :usuario_id_destino
                  ORDER BY fecha_creacion DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en NotificacionModel::obtenerPorUsuarioId al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return [];
            }
            
            $stmt->bindParam(':usuario_id_destino', $usuario_id_destino, PDO::PARAM_INT);
            
            $stmt->execute();
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $notificaciones;
            
        } catch (PDOException $e) { 
            error_log("PDOException en NotificacionModel::obtenerPorUsuarioId: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene las notificaciones no leídas de un usuario (donde fecha_lectura es NULL).
     *
     * @param int $usuario_id_destino ID del usuario.
     * @param int|null $limite Número máximo de notificaciones a devolver.
     * @return array Arreglo de notificaciones no leídas.
     */
    public function obtenerNoLeidasPorUsuario($usuario_id_destino, $limite = null) {
        if ($this->conn === null) {
            error_log("NotificacionModel::obtenerNoLeidasPorUsuario: Conexión PDO no establecida.");
            return [];
        }

        $query_string = "SELECT id, usuario_id_destino, mensaje, tipo, enlace, fecha_creacion, fecha_lectura
                         FROM " . $this->table_name . "
                         WHERE usuario_id_destino = :usuario_id_destino AND fecha_lectura IS NULL
                         ORDER BY fecha_creacion DESC";
        
        if ($limite !== null && is_int($limite) && $limite > 0) {
            $query_string .= " LIMIT :limite";
        }
        
        try {
            $stmt = $this->conn->prepare($query_string);
            if (!$stmt) {
                error_log("Error en NotificacionModel::obtenerNoLeidasPorUsuario al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return [];
            }
            
            $stmt->bindParam(':usuario_id_destino', $usuario_id_destino, PDO::PARAM_INT);
            if ($limite !== null && is_int($limite) && $limite > 0) {
                 $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $notificaciones;
        } catch (PDOException $e) { 
            error_log("PDOException en NotificacionModel::obtenerNoLeidasPorUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una notificación específica por su ID.
     *
     * @param int $id ID de la notificación.
     * @return array|null La notificación como array asociativo o null si no se encuentra.
     */
    public function obtenerPorId($id) {
        if ($this->conn === null) {
            error_log("NotificacionModel::obtenerPorId: Conexión PDO no establecida.");
            return null;
        }

        $query = "SELECT id, usuario_id_destino, mensaje, tipo, enlace, fecha_creacion, fecha_lectura
                  FROM " . $this->table_name . "
                  WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en NotificacionModel::obtenerPorId al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return null;
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            $stmt->execute();
            $notificacion = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $notificacion ?: null;
        } catch (PDOException $e) {
            error_log("PDOException en NotificacionModel::obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Marca una notificación específica como leída, estableciendo fecha_lectura a la hora actual.
     * Solo la marca si pertenece al usuario_id_destino especificado.
     *
     * @param int $id ID de la notificación.
     * @param int $usuario_id_destino ID del usuario al que pertenece la notificación.
     * @return bool True si se marcó como leída, false en caso contrario.
     */
    public function marcarComoLeida($id, $usuario_id_destino) {
        if ($this->conn === null) {
            error_log("NotificacionModel::marcarComoLeida: Conexión PDO no establecida.");
            return false;
        }

        $query = "UPDATE " . $this->table_name . "
                  SET fecha_lectura = NOW()
                  WHERE id = :id AND usuario_id_destino = :usuario_id_destino AND fecha_lectura IS NULL";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en NotificacionModel::marcarComoLeida al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return false;
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id_destino', $usuario_id_destino, PDO::PARAM_INT);
            
            $stmt->execute();
            $affected_rows = $stmt->rowCount();
            $stmt->closeCursor();
            return $affected_rows > 0;
        } catch (PDOException $e) {
            error_log("PDOException en NotificacionModel::marcarComoLeida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca todas las notificaciones no leídas de un usuario como leídas.
     *
     * @param int $usuario_id_destino ID del usuario.
     * @return bool True si se marcaron como leídas (al menos una), false en caso contrario o si no había no leídas.
     */
    public function marcarTodasComoLeidasPorUsuario($usuario_id_destino) {
        if ($this->conn === null) {
            error_log("NotificacionModel::marcarTodasComoLeidasPorUsuario: Conexión PDO no establecida.");
            return false;
        }
        $query = "UPDATE " . $this->table_name . " SET fecha_lectura = NOW() 
                  WHERE usuario_id_destino = :usuario_id_destino AND fecha_lectura IS NULL";
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en NotificacionModel::marcarTodasComoLeidasPorUsuario al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return false;
            }
            $stmt->bindParam(':usuario_id_destino', $usuario_id_destino, PDO::PARAM_INT);
            $stmt->execute();
            $affected_rows = $stmt->rowCount();
            $stmt->closeCursor();
            return $affected_rows > 0;
        } catch (PDOException $e) {
            error_log("PDOException en NotificacionModel::marcarTodasComoLeidasPorUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cuenta las notificaciones no leídas de un usuario (donde fecha_lectura es NULL).
     *
     * @param int $usuario_id_destino ID del usuario.
     * @return int Cantidad de notificaciones no leídas.
     */
    public function contarNoLeidasPorUsuarioId($usuario_id_destino) {
        if ($this->conn === null) {
            error_log("NotificacionModel::contarNoLeidasPorUsuarioId: Conexión PDO no establecida.");
            return 0;
        }

        $query = "SELECT COUNT(id) as total_no_leidas
                  FROM " . $this->table_name . "
                  WHERE usuario_id_destino = :usuario_id_destino AND fecha_lectura IS NULL";
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error en NotificacionModel::contarNoLeidasPorUsuarioId al preparar la consulta PDO: " . implode(" ", $this->conn->errorInfo()));
                return 0;
            }
            $stmt->bindParam(':usuario_id_destino', $usuario_id_destino, PDO::PARAM_INT);
            
            $stmt->execute();
            $count = (int)$stmt->fetchColumn(); // fetchColumn() es adecuado aquí
            $stmt->closeCursor(); // Aunque para fetchColumn no es estrictamente necesario, es buena práctica ser consistente
            return $count;
        } catch (PDOException $e) {
            error_log("PDOException en NotificacionModel::contarNoLeidasPorUsuarioId: " . $e->getMessage());
            return 0;
        }
    }
}
?>