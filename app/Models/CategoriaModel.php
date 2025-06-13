<?php

class CategoriaModel {
    private $conn; // Variable para la conexión PDO
    private $table_name = "categorias";

    public function __construct($db_connection) {
        if ($db_connection instanceof \PDO) {
            $this->conn = $db_connection;
        } else {
            error_log("CategoriaModel: Se esperaba una conexión PDO, se recibió: " . gettype($db_connection));
            // Considera lanzar una excepción si la conexión es indispensable.
            $this->conn = null; 
        }
    }

    /**
     * Obtiene todas las categorías de la base de datos usando PDO.
     * @return array Un array de categorías, o un array vacío si no hay o en caso de error.
     */
    public function obtenerTodasCategorias() {
        if ($this->conn === null) {
            error_log("CategoriaModel::obtenerTodasCategorias - No hay conexión PDO válida.");
            return [];
        }
        
        $sql = "SELECT id, nombre, descripcion FROM " . $this->table_name . " ORDER BY nombre ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Error al preparar la consulta (obtenerTodasCategorias PDO): " . implode(" ", $this->conn->errorInfo()));
                return [];
            }
            
            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $categorias;
            
        } catch (PDOException $e) {
            error_log("PDOException en CategoriaModel::obtenerTodasCategorias: " . $e->getMessage() . " SQL: " . $sql);
            return []; 
        }
    }

    /**
     * Obtiene una categoría específica por su ID usando PDO.
     * @param int $id El ID de la categoría.
     * @return array|null Los datos de la categoría si se encuentra, o null si no.
     */
    public function obtenerCategoriaPorId($id) {
        if ($this->conn === null) {
            error_log("CategoriaModel::obtenerCategoriaPorId - No hay conexión PDO válida.");
            return null;
        }

        $sql = "SELECT id, nombre, descripcion FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log("Error al preparar la consulta (obtenerCategoriaPorId PDO ID: $id): " . implode(" ", $this->conn->errorInfo()));
                return null;
            }
            
            $id_f = filter_var($id, FILTER_VALIDATE_INT);
            if ($id_f === false) {
                error_log("CategoriaModel::obtenerCategoriaPorId - ID inválido: $id");
                return null;
            }
            
            $stmt->bindParam(":id", $id_f, PDO::PARAM_INT);
            
            $stmt->execute();
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC); 
            $stmt->closeCursor();
            return $categoria ?: null;

        } catch (PDOException $e) {
            error_log("PDOException en CategoriaModel::obtenerCategoriaPorId (ID: $id): " . $e->getMessage());
            return null;
        }
    }

    // Puedes añadir más métodos aquí según necesites, por ejemplo:
    // - crearCategoria($datos)
    // - actualizarCategoria($id, $datos)
    // - eliminarCategoria($id)

} // Fin de la clase CategoriaModel
?>