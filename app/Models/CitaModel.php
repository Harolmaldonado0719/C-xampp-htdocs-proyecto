<?php

class CitaModel {
    private $conn; // Conexión PDO
    private $table_name = "citas";
    private $table_usuarios = "usuarios"; 
    private $table_servicios = "servicios"; 

    public function __construct($db_connection) {
        if ($db_connection instanceof \PDO) {
            $this->conn = $db_connection;
        } else {
            error_log("CitaModel: Se esperaba una conexión PDO, se recibió: " . gettype($db_connection));
            $this->conn = null;
        }
    }

    public function obtenerCitasPorEmpleado($empleado_id, $fecha_inicio = null, $fecha_fin = null) {
        if ($this->conn === null) {
            error_log(get_class($this)."::obtenerCitasPorEmpleado - Sin conexión PDO a BD.");
            return [];
        }

        // CORRECCIÓN: Cambiado alias de id_cita a id
        $sql = "SELECT c.id as id, c.cliente_id, c.empleado_id, c.servicio_id, 
                       c.fecha_hora_cita, c.estado_cita, c.notas_cliente, c.notas_empleado,
                       c.fecha_creacion, c.fecha_actualizacion,
                       u_cliente.nombre as cliente_nombre, 
                       u_cliente.apellido as cliente_apellido, /* Añadido para nombre completo */
                       u_cliente.telefono as cliente_telefono, /* Añadido para modal */
                       s.nombre as servicio_nombre,
                       s.duracion_minutos as servicio_duracion 
                FROM " . $this->table_name . " c
                JOIN " . $this->table_usuarios . " u_cliente ON c.cliente_id = u_cliente.id
                LEFT JOIN " . $this->table_servicios . " s ON c.servicio_id = s.id
                WHERE c.empleado_id = :empleado_id";

        $params_to_bind = [':empleado_id' => $empleado_id];

        if ($fecha_inicio) {
            $sql .= " AND DATE(c.fecha_hora_cita) >= :fecha_inicio";
            $params_to_bind[':fecha_inicio'] = $fecha_inicio;
        }
        if ($fecha_fin) {
            $sql .= " AND DATE(c.fecha_hora_cita) <= :fecha_fin";
            $params_to_bind[':fecha_fin'] = $fecha_fin;
        }
        $sql .= " ORDER BY c.fecha_hora_cita ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerCitasPorEmpleado PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return [];
            }

            $stmt->execute($params_to_bind);
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($citas as &$cita) {
                if (!empty($cita['fecha_hora_cita'])) {
                    $datetime = new DateTime($cita['fecha_hora_cita']);
                    $cita['fecha_formateada'] = $datetime->format('d/m/Y');
                    $cita['hora_formateada'] = $datetime->format('h:i A'); // o H:i para formato 24h
                } else {
                    $cita['fecha_formateada'] = 'N/A';
                    $cita['hora_formateada'] = 'N/A';
                }
                if (!empty($cita['fecha_creacion'])) {
                    $datetime_creacion = new DateTime($cita['fecha_creacion']);
                    $cita['fecha_creacion_formateada'] = $datetime_creacion->format('d/m/Y H:i');
                } else {
                    $cita['fecha_creacion_formateada'] = 'N/A';
                }
                if (!empty($cita['fecha_actualizacion'])) {
                    $datetime_actualizacion = new DateTime($cita['fecha_actualizacion']);
                    $cita['fecha_actualizacion_formateada'] = $datetime_actualizacion->format('d/m/Y H:i');
                } else {
                    $cita['fecha_actualizacion_formateada'] = 'N/A';
                }
                $cita['duracion_estimada_min'] = $cita['servicio_duracion'] ?? 'N/A'; 
            }
            unset($cita); 

            $stmt->closeCursor();
            return $citas;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::obtenerCitasPorEmpleado: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerCitaPorId($id_cita) {
        if ($this->conn === null) {
            error_log(get_class($this)."::obtenerCitaPorId - Sin conexión PDO a BD.");
            return null;
        }
        
        $sql = "SELECT c.id as id, c.cliente_id, c.empleado_id, c.servicio_id, 
                       c.fecha_hora_cita, c.estado_cita, c.notas_cliente, c.notas_empleado,
                       c.fecha_creacion, c.fecha_actualizacion,
                       u_cliente.nombre as cliente_nombre, 
                       u_cliente.apellido as cliente_apellido, 
                       u_cliente.telefono as cliente_telefono, 
                       u_empleado.nombre as empleado_nombre, 
                       u_empleado.apellido as empleado_apellido, 
                       s.nombre as servicio_nombre,
                       s.duracion_minutos as servicio_duracion, 
                       s.precio as servicio_precio
                FROM " . $this->table_name . " c
                JOIN " . $this->table_usuarios . " u_cliente ON c.cliente_id = u_cliente.id
                LEFT JOIN " . $this->table_usuarios . " u_empleado ON c.empleado_id = u_empleado.id
                LEFT JOIN " . $this->table_servicios . " s ON c.servicio_id = s.id
                WHERE c.id = :id_cita";
        
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerCitaPorId PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return null;
            }
            $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
            $stmt->execute();
            $cita = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cita) {
                if (!empty($cita['fecha_hora_cita'])) {
                    $datetime = new DateTime($cita['fecha_hora_cita']);
                    $cita['fecha_formateada'] = $datetime->format('d/m/Y');
                    $cita['hora_formateada'] = $datetime->format('h:i A'); // o H:i para formato 24h
                } else {
                    $cita['fecha_formateada'] = 'N/A';
                    $cita['hora_formateada'] = 'N/A';
                }
                if (!empty($cita['fecha_creacion'])) {
                    $datetime_creacion = new DateTime($cita['fecha_creacion']);
                    $cita['fecha_creacion_formateada'] = $datetime_creacion->format('d/m/Y H:i');
                } else {
                    $cita['fecha_creacion_formateada'] = 'N/A';
                }
                if (!empty($cita['fecha_actualizacion'])) {
                    $datetime_actualizacion = new DateTime($cita['fecha_actualizacion']);
                    $cita['fecha_actualizacion_formateada'] = $datetime_actualizacion->format('d/m/Y H:i');
                } else {
                    $cita['fecha_actualizacion_formateada'] = 'N/A';
                }
                $cita['duracion_estimada_min'] = $cita['servicio_duracion'] ?? 'N/A';
            }

            $stmt->closeCursor();
            return $cita ?: null;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::obtenerCitaPorId: " . $e->getMessage());
            return null;
        }
    }

    public function actualizarEstadoCita($id_cita, $nuevo_estado, $notas_empleado = null) {
        if ($this->conn === null) {
            error_log(get_class($this)."::actualizarEstadoCita - Sin conexión PDO a BD.");
            return false;
        }

        $sql_parts = [];
        $params_to_bind = [];

        if ($nuevo_estado !== null) {
            $sql_parts[] = "estado_cita = :nuevo_estado";
            $params_to_bind[':nuevo_estado'] = $nuevo_estado;
        }

        if ($notas_empleado !== null) { 
            $sql_parts[] = "notas_empleado = :notas_empleado";
            $params_to_bind[':notas_empleado'] = $notas_empleado;
        }
        
        if (empty($sql_parts)) {
            error_log(get_class($this)."::actualizarEstadoCita - No se proporcionaron datos para actualizar la cita ID: $id_cita.");
            return true; 
        }
        
        $sql_parts[] = "fecha_actualizacion = NOW()";
        $params_to_bind[':id_cita'] = $id_cita;

        $sql = "UPDATE " . $this->table_name . " SET " . implode(", ", $sql_parts) . " WHERE id = :id_cita";
        
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (actualizarEstadoCita PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return false;
            }

            $stmt->execute($params_to_bind);
            $stmt->closeCursor();
            return true; 
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::actualizarEstadoCita: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerHistorialCitasPorEmpleado($empleado_id, $filtros = []) {
        if ($this->conn === null) {
            error_log(get_class($this)."::obtenerHistorialCitasPorEmpleado - Sin conexión PDO a BD.");
            return [];
        }

        $sql = "SELECT c.id as id, c.cliente_id, c.empleado_id, c.servicio_id, 
                       c.fecha_hora_cita, c.estado_cita, c.notas_cliente, c.notas_empleado,
                       c.fecha_creacion, c.fecha_actualizacion,
                       u_cliente.nombre as cliente_nombre,
                       u_cliente.apellido as cliente_apellido, 
                       s.nombre as servicio_nombre,
                       s.duracion_minutos as servicio_duracion
                FROM " . $this->table_name . " c
                JOIN " . $this->table_usuarios . " u_cliente ON c.cliente_id = u_cliente.id
                LEFT JOIN " . $this->table_servicios . " s ON c.servicio_id = s.id
                WHERE c.empleado_id = :empleado_id";

        $params_to_bind = [':empleado_id' => $empleado_id];

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(c.fecha_hora_cita) >= :fecha_desde";
            $params_to_bind[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(c.fecha_hora_cita) <= :fecha_hasta";
            $params_to_bind[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND c.estado_cita = :estado_cita_filtro";
            $params_to_bind[':estado_cita_filtro'] = $filtros['estado'];
        }
        $sql .= " ORDER BY c.fecha_hora_cita DESC"; 

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerHistorialCitasPorEmpleado PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return [];
            }

            $stmt->execute($params_to_bind);
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear fechas aquí
            foreach ($citas as &$cita_item) { // Cambiado el nombre de la variable para evitar conflicto
                if (!empty($cita_item['fecha_hora_cita'])) {
                    $datetime = new DateTime($cita_item['fecha_hora_cita']);
                    $cita_item['fecha_formateada'] = $datetime->format('d/m/Y');
                    $cita_item['hora_formateada'] = $datetime->format('H:i'); // Formato 24h para consistencia
                } else {
                    $cita_item['fecha_formateada'] = 'N/A';
                    $cita_item['hora_formateada'] = 'N/A';
                }
                if (!empty($cita_item['fecha_creacion'])) {
                    $datetime_creacion = new DateTime($cita_item['fecha_creacion']);
                    $cita_item['fecha_creacion_formateada'] = $datetime_creacion->format('d/m/Y H:i');
                } else {
                    $cita_item['fecha_creacion_formateada'] = 'N/A';
                }
                if (!empty($cita_item['fecha_actualizacion'])) {
                    $datetime_actualizacion = new DateTime($cita_item['fecha_actualizacion']);
                    $cita_item['fecha_actualizacion_formateada'] = $datetime_actualizacion->format('d/m/Y H:i');
                } else {
                    $cita_item['fecha_actualizacion_formateada'] = 'N/A';
                }
                // Asegurarse que cliente_nombre y servicio_nombre están presentes o son N/A
                $cita_item['cliente_nombre'] = ($cita_item['cliente_nombre'] ?? '') . ' ' . ($cita_item['cliente_apellido'] ?? '');
                if (empty(trim($cita_item['cliente_nombre']))) {
                    $cita_item['cliente_nombre'] = 'N/A';
                }
                $cita_item['servicio_nombre'] = $cita_item['servicio_nombre'] ?? 'N/A';

            }
            unset($cita_item); // Romper la referencia del último elemento

            $stmt->closeCursor();
            return $citas;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::obtenerHistorialCitasPorEmpleado: " . $e->getMessage());
            return [];
        }
    }

    public function contarCitasPorEstadoParaEmpleado($empleado_id, $fecha_desde = null, $fecha_hasta = null) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::contarCitasPorEstadoParaEmpleado - Sin conexión PDO a BD.");
            return [];
        }

        $sql = "SELECT estado_cita, COUNT(*) as conteo 
                FROM " . $this->table_name . "
                WHERE empleado_id = :empleado_id";
        
        $params_to_bind = [':empleado_id' => $empleado_id];

        if ($fecha_desde) {
            $sql .= " AND DATE(fecha_hora_cita) >= :fecha_desde";
            $params_to_bind[':fecha_desde'] = $fecha_desde;
        }
        if ($fecha_hasta) {
            $sql .= " AND DATE(fecha_hora_cita) <= :fecha_hasta";
            $params_to_bind[':fecha_hasta'] = $fecha_hasta;
        }
        $sql .= " GROUP BY estado_cita ORDER BY estado_cita";

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (contarCitasPorEstadoParaEmpleado PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return [];
            }

            $stmt->execute($params_to_bind);
            $resultados = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
            $stmt->closeCursor();
            return $resultados;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::contarCitasPorEstadoParaEmpleado: " . $e->getMessage());
            return [];
        }
    }
    
    public function contarCitasPorEstado($estado) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::contarCitasPorEstado - Sin conexión PDO a BD.");
            return 0;
        }
        $sql = "SELECT COUNT(id) FROM " . $this->table_name . " WHERE estado_cita = :estado";
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (contarCitasPorEstado PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return 0;
            }
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
            $count = (int) $stmt->fetchColumn();
            $stmt->closeCursor();
            return $count;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::contarCitasPorEstado: " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerResumenServiciosParaEmpleado($empleado_id, $fecha_desde = null, $fecha_hasta = null) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::obtenerResumenServiciosParaEmpleado - Sin conexión PDO a BD.");
            return [];
        }

        $sql = "SELECT s.nombre as servicio_nombre, COUNT(c.id) as conteo
                FROM " . $this->table_name . " c
                JOIN " . $this->table_servicios . " s ON c.servicio_id = s.id
                WHERE c.empleado_id = :empleado_id 
                AND (c.estado_cita = 'Completada' OR c.estado_cita = 'Confirmada')"; 
        
        $params_to_bind = [':empleado_id' => $empleado_id];

        if ($fecha_desde) {
            $sql .= " AND DATE(c.fecha_hora_cita) >= :fecha_desde";
            $params_to_bind[':fecha_desde'] = $fecha_desde;
        }
        if ($fecha_hasta) {
            $sql .= " AND DATE(c.fecha_hora_cita) <= :fecha_hasta";
            $params_to_bind[':fecha_hasta'] = $fecha_hasta;
        }
        $sql .= " GROUP BY s.id, s.nombre ORDER BY conteo DESC, servicio_nombre ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerResumenServiciosParaEmpleado PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return [];
            }

            $stmt->execute($params_to_bind);
            $resumen_servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $resumen_servicios;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::obtenerResumenServiciosParaEmpleado: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTodasConDetalles() {
        if ($this->conn === null) {
            error_log(get_class($this) . "::obtenerTodasConDetalles - Sin conexión PDO a BD.");
            return false;
        }

        $sql = "SELECT c.id as id, c.cliente_id, c.empleado_id, c.servicio_id, 
                       c.fecha_hora_cita, c.estado_cita, c.notas_cliente, c.notas_empleado,
                       c.fecha_creacion, c.fecha_actualizacion,
                       u_cliente.nombre as cliente_nombre, 
                       u_cliente.apellido as cliente_apellido, 
                       u_empleado.nombre as empleado_nombre, 
                       u_empleado.apellido as empleado_apellido, 
                       s.nombre as servicio_nombre,
                       s.duracion_minutos as servicio_duracion, 
                       s.precio as servicio_precio
                FROM " . $this->table_name . " c
                LEFT JOIN " . $this->table_usuarios . " u_cliente ON c.cliente_id = u_cliente.id
                LEFT JOIN " . $this->table_usuarios . " u_empleado ON c.empleado_id = u_empleado.id
                LEFT JOIN " . $this->table_servicios . " s ON c.servicio_id = s.id
                ORDER BY c.fecha_hora_cita DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerTodasConDetalles PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return false;
            }

            $stmt->execute();
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($citas as &$cita_item) {
                 if (!empty($cita_item['fecha_hora_cita'])) {
                    $datetime = new DateTime($cita_item['fecha_hora_cita']);
                    $cita_item['fecha_formateada'] = $datetime->format('d/m/Y');
                    $cita_item['hora_formateada'] = $datetime->format('H:i');
                } else {
                    $cita_item['fecha_formateada'] = 'N/A';
                    $cita_item['hora_formateada'] = 'N/A';
                }
                if (!empty($cita_item['fecha_creacion'])) {
                    $datetime_creacion = new DateTime($cita_item['fecha_creacion']);
                    $cita_item['fecha_creacion_formateada'] = $datetime_creacion->format('d/m/Y H:i');
                } else {
                    $cita_item['fecha_creacion_formateada'] = 'N/A';
                }
                if (!empty($cita_item['fecha_actualizacion'])) {
                    $datetime_actualizacion = new DateTime($cita_item['fecha_actualizacion']);
                    $cita_item['fecha_actualizacion_formateada'] = $datetime_actualizacion->format('d/m/Y H:i');
                } else {
                    $cita_item['fecha_actualizacion_formateada'] = 'N/A';
                }
            }
            unset($cita_item);

            $stmt->closeCursor();
            return $citas;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::obtenerTodasConDetalles: " . $e->getMessage());
            return false;
        }
    }

    public function crear($datos) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::crear - Sin conexión PDO a BD.");
            return false;
        }

        $sql = "INSERT INTO " . $this->table_name . " 
                (cliente_id, empleado_id, servicio_id, fecha_hora_cita, estado_cita, notas_cliente, fecha_creacion, fecha_actualizacion) 
                VALUES (:cliente_id, :empleado_id, :servicio_id, :fecha_hora_cita, :estado_cita, :notas_cliente, NOW(), NOW())";

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (crear cita PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return false;
            }

            $notas_cliente = $datos['notas_cliente'] ?? null;

            $stmt->bindParam(":cliente_id", $datos['cliente_id'], PDO::PARAM_INT);
            $stmt->bindParam(":empleado_id", $datos['empleado_id'], PDO::PARAM_INT);
            $stmt->bindParam(":servicio_id", $datos['servicio_id'], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_hora_cita", $datos['fecha_hora_cita']);
            $stmt->bindParam(":estado_cita", $datos['estado_cita']);
            $stmt->bindParam(":notas_cliente", $notas_cliente, $notas_cliente === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $id_insertado = $this->conn->lastInsertId();
                $stmt->closeCursor();
                return $id_insertado;
            } else {
                error_log("Error al ejecutar consulta (crear cita PDO): " . implode(" ", $stmt->errorInfo()) . " Datos: " . json_encode($datos));
                $stmt->closeCursor();
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::crear: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorClienteIdConDetalles($cliente_id) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::obtenerPorClienteIdConDetalles - Sin conexión PDO a BD.");
            return false;
        }
        
        $sql = "SELECT c.id as id, c.fecha_hora_cita, c.estado_cita, c.notas_cliente, c.notas_empleado,
                       c.fecha_creacion, c.fecha_actualizacion, /* Añadido para formatear */
                       s.nombre as servicio_nombre, s.precio as servicio_precio, 
                       s.duracion_minutos as servicio_duracion, 
                       u_empleado.nombre as empleado_nombre,
                       u_empleado.apellido as empleado_apellido 
                FROM " . $this->table_name . " c
                LEFT JOIN " . $this->table_servicios . " s ON c.servicio_id = s.id
                LEFT JOIN " . $this->table_usuarios . " u_empleado ON c.empleado_id = u_empleado.id
                WHERE c.cliente_id = :cliente_id
                ORDER BY c.fecha_hora_cita DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerPorClienteIdConDetalles PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return false;
            }
            $stmt->bindParam(":cliente_id", $cliente_id, PDO::PARAM_INT);
            $stmt->execute();
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($citas as &$cita_item) {
                 if (!empty($cita_item['fecha_hora_cita'])) {
                    $datetime = new DateTime($cita_item['fecha_hora_cita']);
                    $cita_item['fecha_formateada'] = $datetime->format('d/m/Y');
                    $cita_item['hora_formateada'] = $datetime->format('H:i');
                } else {
                    $cita_item['fecha_formateada'] = 'N/A';
                    $cita_item['hora_formateada'] = 'N/A';
                }
                if (!empty($cita_item['fecha_creacion'])) {
                    $datetime_creacion = new DateTime($cita_item['fecha_creacion']);
                    $cita_item['fecha_creacion_formateada'] = $datetime_creacion->format('d/m/Y H:i');
                } else {
                    $cita_item['fecha_creacion_formateada'] = 'N/A';
                }
                if (!empty($cita_item['fecha_actualizacion'])) {
                    $datetime_actualizacion = new DateTime($cita_item['fecha_actualizacion']);
                    $cita_item['fecha_actualizacion_formateada'] = $datetime_actualizacion->format('d/m/Y H:i');
                } else {
                    $cita_item['fecha_actualizacion_formateada'] = 'N/A';
                }
            }
            unset($cita_item);

            $stmt->closeCursor();
            return $citas;
        } catch (PDOException $e) {
            error_log("PDOException en CitaModel::obtenerPorClienteIdConDetalles: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstado($id_cita, $nuevo_estado, $modificado_por_id = null) {
        return $this->actualizarEstadoCita($id_cita, $nuevo_estado, null);
    }
}
?>