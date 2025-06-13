<?php

class FacturaModel {
    private $conn;
    private $table_name = "facturas";
    private $table_citas = "citas"; 
    private $table_usuarios = "usuarios";

    public function __construct($db_connection) {
        if ($db_connection instanceof \PDO) {
            $this->conn = $db_connection;
        } else {
            error_log("FacturaModel: Se esperaba una conexión PDO. Se recibió: " . gettype($db_connection));
            $this->conn = null; 
        }
    }

    public function crear($datosFactura) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::crear - Sin conexión PDO a BD.");
            return false;
        }

        // Asumiendo que 'id' es AUTO_INCREMENT y no se incluye en la inserción explícita
        $sql = "INSERT INTO " . $this->table_name . " 
                (cita_id, numero_factura, cliente_id, empleado_id, servicio_nombre_en_factura, monto_total, estado_factura, fecha_emision, fecha_creacion, fecha_actualizacion) 
                VALUES 
                (:cita_id, :numero_factura, :cliente_id, :empleado_id, :servicio_nombre_en_factura, :monto_total, :estado_factura, NOW(), NOW(), NOW())";

        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (crear factura PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return false;
            }

            $stmt->bindParam(":cita_id", $datosFactura['cita_id'], PDO::PARAM_INT);
            $stmt->bindParam(":numero_factura", $datosFactura['numero_factura']);
            $stmt->bindParam(":cliente_id", $datosFactura['cliente_id'], PDO::PARAM_INT);
            $stmt->bindParam(":empleado_id", $datosFactura['empleado_id'], PDO::PARAM_INT);
            $stmt->bindParam(":servicio_nombre_en_factura", $datosFactura['servicio_nombre_en_factura']);
            $stmt->bindParam(":monto_total", $datosFactura['monto_total']);
            $stmt->bindParam(":estado_factura", $datosFactura['estado_factura']);
            
            if ($stmt->execute()) {
                $id_insertado = $this->conn->lastInsertId();
                // $stmt->closeCursor(); // No es necesario con lastInsertId y puede causar problemas si se llama antes de fetch/fetchAll en otras consultas
                return $id_insertado;
            } else {
                error_log("Error al ejecutar consulta (crear factura PDO): " . implode(" ", $stmt->errorInfo()) . " Datos: " . json_encode($datosFactura));
                // $stmt->closeCursor();
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDOException en FacturaModel::crear: " . $e->getMessage() . " Datos: " . json_encode($datosFactura));
            if (isset($datosFactura['numero_factura']) && $e->getCode() == '23000' && strpos($e->getMessage(), 'numero_factura_unique') !== false) {
                error_log("Error de duplicado de numero_factura: " . $datosFactura['numero_factura']);
            }
            return false;
        }
    }

    public function obtenerFacturasPorEmpleado($empleado_id, $limit = 20, $offset = 0) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::obtenerFacturasPorEmpleado - Sin conexión PDO a BD.");
            return [];
        }

        // f.* seleccionará la columna 'id' si así se llama en la BD
        $sql = "SELECT f.*, c.fecha_hora_cita, u_cliente.nombre as cliente_nombre, u_cliente.apellido as cliente_apellido
                FROM " . $this->table_name . " f
                JOIN " . $this->table_citas . " c ON f.cita_id = c.id
                JOIN " . $this->table_usuarios . " u_cliente ON f.cliente_id = u_cliente.id
                WHERE f.empleado_id = :empleado_id
                ORDER BY f.fecha_emision DESC
                LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":empleado_id", $empleado_id, PDO::PARAM_INT);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // $stmt->closeCursor(); // No es necesario después de fetchAll
            return $facturas;
        } catch (PDOException $e) {
            error_log("PDOException en FacturaModel::obtenerFacturasPorEmpleado: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerFacturaPorIdYEmpleado($id_factura_param, $empleado_id) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::obtenerFacturaPorIdYEmpleado - Sin conexión PDO a BD.");
            return null;
        }
        // Cambio aquí: f.id_factura a f.id
        $sql = "SELECT f.*, 
                       c.fecha_hora_cita, c.notas_cliente,
                       u_cliente.nombre as cliente_nombre, u_cliente.apellido as cliente_apellido, u_cliente.email as cliente_email, u_cliente.telefono as cliente_telefono,
                       u_empleado.nombre as empleado_nombre, u_empleado.apellido as empleado_apellido
                FROM " . $this->table_name . " f
                LEFT JOIN " . $this->table_citas . " c ON f.cita_id = c.id
                LEFT JOIN " . $this->table_usuarios . " u_cliente ON f.cliente_id = u_cliente.id
                LEFT JOIN " . $this->table_usuarios . " u_empleado ON f.empleado_id = u_empleado.id
                WHERE f.id = :factura_id_placeholder AND f.empleado_id = :empleado_id";
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerFacturaPorIdYEmpleado PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return null;
            }
            // Cambio aquí: el placeholder coincide con el de la consulta
            $stmt->bindParam(":factura_id_placeholder", $id_factura_param, PDO::PARAM_INT); 
            $stmt->bindParam(":empleado_id", $empleado_id, PDO::PARAM_INT);
            $stmt->execute();
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            // $stmt->closeCursor(); // No es necesario después de fetch
            return $factura ? $factura : null;
        } catch (PDOException $e) {
            error_log("PDOException en FacturaModel::obtenerFacturaPorIdYEmpleado: " . $e->getMessage());
            return null;
        }
    }

    public function actualizarEstadoFactura($id_factura_param, $nuevo_estado, $empleado_id_sesion) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::actualizarEstadoFactura - Sin conexión PDO a BD.");
            return false;
        }
        
        $estados_permitidos = ['Pendiente', 'Pagada', 'Anulada'];
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            error_log("FacturaModel::actualizarEstadoFactura - Estado '{$nuevo_estado}' no permitido.");
            return false;
        }

        // Cambio aquí: id_factura a id
        $sql = "UPDATE " . $this->table_name . " 
                SET estado_factura = :nuevo_estado, fecha_actualizacion = NOW()
                WHERE id = :factura_id_placeholder AND empleado_id = :empleado_id_sesion";
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (actualizarEstadoFactura PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return false;
            }
            $stmt->bindParam(':nuevo_estado', $nuevo_estado, PDO::PARAM_STR);
            // Cambio aquí: el placeholder coincide con el de la consulta
            $stmt->bindParam(':factura_id_placeholder', $id_factura_param, PDO::PARAM_INT); 
            $stmt->bindParam(':empleado_id_sesion', $empleado_id_sesion, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $rowCount = $stmt->rowCount();
                // $stmt->closeCursor(); // No es necesario después de rowCount para UPDATE
                if ($rowCount > 0) {
                    error_log("FacturaModel::actualizarEstadoFactura - Estado de factura ID {$id_factura_param} actualizado a '{$nuevo_estado}' por empleado ID {$empleado_id_sesion}. Filas afectadas: {$rowCount}");
                    return true;
                } else {
                    error_log("FacturaModel::actualizarEstadoFactura - No se actualizó la factura ID {$id_factura_param} para empleado ID {$empleado_id_sesion} (quizás no pertenece, no existe o estado sin cambios).");
                    return false; 
                }
            } else {
                error_log("FacturaModel::actualizarEstadoFactura - Error al ejecutar la actualización: " . implode(", ", $stmt->errorInfo()));
                // $stmt->closeCursor();
                return false;
            }
        } catch (PDOException $e) {
            error_log("FacturaModel::actualizarEstadoFactura - Excepción PDO: " . $e->getMessage());
            return false;
        }
    }
    
    public function generarNumeroFactura() {
        // Genera un número de factura único. Ejemplo: FACT-20231027-A1B2
        return "FACT-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -4));
    }

    // Si tienes métodos para Admin, también deberías revisarlos para usar 'id'
    // Por ejemplo, un hipotético obtenerFacturaPorIdAdmin:
    public function obtenerFacturaPorIdAdmin($id_factura_param) {
        if ($this->conn === null) {
            error_log(get_class($this) . "::obtenerFacturaPorIdAdmin - Sin conexión PDO a BD.");
            return null;
        }
        // Cambio aquí: f.id_factura a f.id
        $sql = "SELECT f.*, 
                       c.fecha_hora_cita, c.notas_cliente,
                       u_cliente.nombre as cliente_nombre, u_cliente.apellido as cliente_apellido, u_cliente.email as cliente_email, u_cliente.telefono as cliente_telefono,
                       u_empleado.nombre as empleado_nombre, u_empleado.apellido as empleado_apellido
                FROM " . $this->table_name . " f
                LEFT JOIN " . $this->table_citas . " c ON f.cita_id = c.id
                LEFT JOIN " . $this->table_usuarios . " u_cliente ON f.cliente_id = u_cliente.id
                LEFT JOIN " . $this->table_usuarios . " u_empleado ON f.empleado_id = u_empleado.id
                WHERE f.id = :factura_id_placeholder"; // Asume que el admin puede ver cualquier factura por ID
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar consulta (obtenerFacturaPorIdAdmin PDO): " . implode(" ", $this->conn->errorInfo()) . " SQL: " . $sql);
                return null;
            }
            $stmt->bindParam(":factura_id_placeholder", $id_factura_param, PDO::PARAM_INT);
            $stmt->execute();
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            return $factura ? $factura : null;
        } catch (PDOException $e) {
            error_log("PDOException en FacturaModel::obtenerFacturaPorIdAdmin: " . $e->getMessage());
            return null;
        }
    }
     public function obtenerTodasLasFacturasAdmin($filtros = [], $orderBy = 'f.fecha_emision DESC', $limit = 10, $offset = 0) {
        if ($this->conn === null) return ['data' => [], 'total' => 0];

        $sqlBase = "FROM {$this->table_name} f
                    LEFT JOIN {$this->table_usuarios} uc ON f.cliente_id = uc.id
                    LEFT JOIN {$this->table_usuarios} ue ON f.empleado_id = ue.id";
        
        $sqlWhere = " WHERE 1=1 ";
        $params = [];

        if (!empty($filtros['numero_factura'])) {
            $sqlWhere .= " AND f.numero_factura LIKE :numero_factura ";
            $params[':numero_factura'] = '%' . $filtros['numero_factura'] . '%';
        }
        if (!empty($filtros['cliente_nombre'])) {
            $sqlWhere .= " AND (uc.nombre LIKE :cliente_nombre OR uc.apellido LIKE :cliente_nombre) ";
            $params[':cliente_nombre'] = '%' . $filtros['cliente_nombre'] . '%';
        }
        if (!empty($filtros['empleado_nombre'])) {
            $sqlWhere .= " AND (ue.nombre LIKE :empleado_nombre OR ue.apellido LIKE :empleado_nombre) ";
            $params[':empleado_nombre'] = '%' . $filtros['empleado_nombre'] . '%';
        }
        if (!empty($filtros['estado_factura'])) {
            $sqlWhere .= " AND f.estado_factura = :estado_factura ";
            $params[':estado_factura'] = $filtros['estado_factura'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sqlWhere .= " AND DATE(f.fecha_emision) >= :fecha_desde ";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sqlWhere .= " AND DATE(f.fecha_emision) <= :fecha_hasta ";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }


        $sqlCount = "SELECT COUNT(f.id) as total " . $sqlBase . $sqlWhere; // Usa f.id para el conteo
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->fetchColumn();

        // f.* seleccionará la columna 'id' si así se llama en la BD
        $sqlData = "SELECT f.*, uc.nombre as cliente_nombre, uc.apellido as cliente_apellido, ue.nombre as empleado_nombre, ue.apellido as empleado_apellido "
                   . $sqlBase . $sqlWhere . " ORDER BY " . $orderBy . " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmtData = $this->conn->prepare($sqlData);
        $stmtData->execute($params);
        $facturas = $stmtData->fetchAll(PDO::FETCH_ASSOC);
        
        return ['data' => $facturas, 'total' => $totalRecords];
    }
}
?>