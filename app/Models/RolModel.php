<?php

class RolModel {
    private $conn; // Conexión PDO
    private $table_name = "roles";

    public $id;
    public $nombre_rol; // Cambiado de 'nombre' a 'nombre_rol' para coincidir con la BD

    public function __construct($db_connection) {
        if ($db_connection instanceof \PDO) {
            $this->conn = $db_connection;
        } else {
            error_log("RolModel: Se esperaba una conexión PDO, se recibió: " . gettype($db_connection));
            $this->conn = null; 
        }
    }

    public function getAll() {
        if ($this->conn === null) {
            error_log("RolModel::getAll(): No hay conexión PDO a la base de datos.");
            return [];
        }

        $query = "SELECT id, nombre_rol FROM " . $this->table_name . " ORDER BY nombre_rol ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("Error al preparar la consulta en RolModel::getAll() PDO: " . implode(" ", $this->conn->errorInfo()));
                return [];
            }
            
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $roles;
        } catch (PDOException $e) {
            error_log("PDOException en RolModel::getAll(): " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        if ($this->conn === null) {
            error_log("RolModel::findById(): No hay conexión PDO a la base de datos.");
            return null;
        }

        $query = "SELECT id, nombre_rol FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("Error al preparar la consulta en RolModel::findById() PDO: " . implode(" ", $this->conn->errorInfo()));
                return null;
            }
            
            $id_f = filter_var($id, FILTER_VALIDATE_INT);
            if ($id_f === false) {
                error_log("RolModel::findById() - ID inválido: $id");
                return null;
            }
            $stmt->bindParam(":id", $id_f, PDO::PARAM_INT); 

            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($row) {
                $this->id = $row['id'];
                $this->nombre_rol = $row['nombre_rol']; 
                return $row; 
            }
            return null; 
        } catch (PDOException $e) {
            error_log("PDOException en RolModel::findById(" . $id . "): " . $e->getMessage());
            return null;
        }
    }

    public function getNombreById($id) {
        if ($this->conn === null) {
            error_log("RolModel::getNombreById(): No hay conexión PDO a la base de datos.");
            return null;
        }

        $query = "SELECT nombre_rol FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("Error al preparar la consulta en RolModel::getNombreById() PDO: " . implode(" ", $this->conn->errorInfo()));
                return null;
            }

            $id_f = filter_var($id, FILTER_VALIDATE_INT);
             if ($id_f === false) {
                error_log("RolModel::getNombreById() - ID inválido: $id");
                return null;
            }
            $stmt->bindParam(":id", $id_f, PDO::PARAM_INT);

            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $row ? $row['nombre_rol'] : null;
        } catch (PDOException $e) {
            error_log("PDOException en RolModel::getNombreById(" . $id . "): " . $e->getMessage());
            return null;
        }
    }
}
?>