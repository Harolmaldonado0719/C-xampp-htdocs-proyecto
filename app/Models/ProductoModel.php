
<?php

if (!class_exists('ProductoModel')) {
    class ProductoModel {
        private $conn; // Conexión PDO
        private $table_name = "productos";
        private $table_categorias = "categorias";

        public function __construct($db_connection) {
            if ($db_connection instanceof \PDO) {
                $this->conn = $db_connection;
                // Asegurar que PDO lance excepciones en caso de error
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                error_log("ProductoModel: Se esperaba una conexión PDO, se recibió: " . gettype($db_connection));
                $this->conn = null; 
                // Considera lanzar una excepción aquí si la conexión es vital y no se puede continuar
                // throw new \InvalidArgumentException("Se esperaba una conexión PDO válida.");
            }
        }

        public function crear($datos) {
            if ($this->conn === null) {
                error_log("ProductoModel::crear - Conexión PDO no establecida.");
                return false;
            }
            $sql = "INSERT INTO " . $this->table_name . " (nombre, descripcion, precio, stock, categoria_id, imagen_url, fecha_creacion, fecha_actualizacion, activo) 
                    VALUES (:nombre, :descripcion, :precio, :stock, :categoria_id, :imagen_url, NOW(), NOW(), :activo)";
            
            try {
                $stmt = $this->conn->prepare($sql);

                $nombre = $datos['nombre'];
                $descripcion = $datos['descripcion'];
                $precio = $datos['precio']; 
                $stock = (int)$datos['stock'];
                $categoria_id = (int)$datos['categoria_id'];
                $imagen_url = $datos['imagen_url'] ?? null;
                $activo = isset($datos['activo']) ? (int)$datos['activo'] : 1; 

                $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
                $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
                $stmt->bindParam(":precio", $precio); 
                $stmt->bindParam(":stock", $stock, PDO::PARAM_INT);
                $stmt->bindParam(":categoria_id", $categoria_id, PDO::PARAM_INT);
                $stmt->bindParam(":imagen_url", $imagen_url, $imagen_url === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindParam(":activo", $activo, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $lastId = $this->conn->lastInsertId();
                    $stmt->closeCursor();
                    return $lastId;
                } else {
                    error_log("Error al ejecutar la consulta (crear producto PDO): " . implode(" ", $stmt->errorInfo()));
                    $stmt->closeCursor();
                    return false;
                }
            } catch (PDOException $e) {
                error_log("PDOException en ProductoModel::crear: " . $e->getMessage() . " SQL: " . $sql . " Datos: " . json_encode($datos));
                if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
                return false;
            }
        }

        public function obtenerTodos() {
            if ($this->conn === null) return [];
            $sql = "SELECT p.*, c.nombre as categoria_nombre 
                    FROM " . $this->table_name . " p
                    LEFT JOIN " . $this->table_categorias . " c ON p.categoria_id = c.id
                    ORDER BY p.nombre ASC";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $productos;
            } catch (PDOException $e) {
                error_log("PDOException en ProductoModel::obtenerTodos: " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
                return [];
            }
        }

        public function obtenerPorId($id) {
            if ($this->conn === null) return null;
            $sql = "SELECT p.*, c.nombre as categoria_nombre 
                    FROM " . $this->table_name . " p
                    LEFT JOIN " . $this->table_categorias . " c ON p.categoria_id = c.id
                    WHERE p.id = :id";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
                $producto = $stmt->fetch(PDO::FETCH_ASSOC); 
                $stmt->closeCursor();
                return $producto ?: null;
            } catch (PDOException $e) {
                error_log("PDOException en ProductoModel::obtenerPorId (ID: $id): " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
                return null;
            }
        }

        public function actualizar($id, $datos) {
            if ($this->conn === null) return false;
            
            $setParts = [];
            $params_to_bind_values = []; 

            if (isset($datos['nombre'])) { $setParts[] = "nombre = :nombre"; $params_to_bind_values[':nombre'] = $datos['nombre']; }
            if (isset($datos['descripcion'])) { $setParts[] = "descripcion = :descripcion"; $params_to_bind_values[':descripcion'] = $datos['descripcion']; }
            if (isset($datos['precio'])) { $setParts[] = "precio = :precio"; $params_to_bind_values[':precio'] = $datos['precio']; }
            if (isset($datos['stock'])) { $setParts[] = "stock = :stock"; $params_to_bind_values[':stock'] = (int)$datos['stock']; }
            if (isset($datos['categoria_id'])) { $setParts[] = "categoria_id = :categoria_id"; $params_to_bind_values[':categoria_id'] = (int)$datos['categoria_id']; }
            if (array_key_exists('imagen_url', $datos)) { $setParts[] = "imagen_url = :imagen_url"; $params_to_bind_values[':imagen_url'] = $datos['imagen_url']; }
            if (isset($datos['activo'])) { $setParts[] = "activo = :activo"; $params_to_bind_values[':activo'] = (int)$datos['activo'];}
            
            if (empty($setParts)) {
                return true; 
            }

            $setParts[] = "fecha_actualizacion = NOW()";
            $sql = "UPDATE " . $this->table_name . " SET " . implode(', ', $setParts) . " WHERE id = :id_where";
            
            try {
                $stmt = $this->conn->prepare($sql);

                foreach ($params_to_bind_values as $placeholder => $value) {
                    if ($placeholder === ':stock' || $placeholder === ':categoria_id' || $placeholder === ':activo') {
                        $stmt->bindValue($placeholder, $value, PDO::PARAM_INT);
                    } elseif ($placeholder === ':imagen_url' && $value === null) {
                        $stmt->bindValue($placeholder, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($placeholder, $value, PDO::PARAM_STR);
                    }
                }
                $stmt->bindValue(':id_where', $id, PDO::PARAM_INT); 
                
                $success = $stmt->execute();
                $stmt->closeCursor();
                return $success; 
            } catch (PDOException $e) {
                error_log("PDOException en ProductoModel::actualizar (ID: $id): " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params_to_bind_values));
                if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
                return false;
            }
        }

        public function eliminar($id) { 
            if ($this->conn === null) return false;
            $sql = "UPDATE " . $this->table_name . " SET activo = 0, fecha_actualizacion = NOW() WHERE id = :id";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $success = $stmt->execute();
                $stmt->closeCursor();
                return $success;
            } catch (PDOException $e) {
                error_log("PDOException en ProductoModel::eliminar (marcar inactivo ID: $id): " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
                return false;
            }
        }

        public function obtenerTodosConCategoria() {
            if ($this->conn === null) return [];
            $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_url, p.categoria_id, c.nombre as categoria_nombre, p.activo
                    FROM " . $this->table_name . " p
                    LEFT JOIN " . $this->table_categorias . " c ON p.categoria_id = c.id
                    WHERE p.activo = 1
                    ORDER BY p.nombre ASC";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $productos;
            } catch (PDOException $e) {
                error_log("PDOException en ProductoModel::obtenerTodosConCategoria: " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
                return [];
            }
        }

        public function obtenerPorIdConCategoria($id) {
            if ($this->conn === null) return null;
            $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_url, p.categoria_id, c.nombre as categoria_nombre, p.activo
                    FROM " . $this->table_name . " p
                    LEFT JOIN " . $this->table_categorias . " c ON p.categoria_id = c.id
                    WHERE p.id = :id AND p.activo = 1";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
                $producto = $stmt->fetch(PDO::FETCH_ASSOC); 
                $stmt->closeCursor();
                return $producto ?: null;
            } catch (PDOException $e) {
                error_log("PDOException en ProductoModel::obtenerPorIdConCategoria (ID: $id): " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
                return null;
            }
        }
    }
} // Fin de if (!class_exists('ProductoModel'))
?>
