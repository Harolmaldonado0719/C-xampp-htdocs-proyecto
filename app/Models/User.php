<?php

class User {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades de la clase
    public $id;
    public $nombre;
    public $email;
    public $password_hash; // La columna en BD se llama 'password', pero almacena el hash
    public $fotografia;
    public $rol_id;       // Para almacenar el ID del rol del usuario
    public $nombre_rol;   // Para almacenar el nombre del rol (obtenido del JOIN)
    public $fecha_registro;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    public function create($nombre, $email, $password, $rol_id, $fotografia = null) {
        $query = "INSERT INTO " . $this->table_name . " (nombre, email, password, fotografia, rol_id, fecha_registro) VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            error_log("Error al preparar la consulta para crear usuario: " . mysqli_error($this->conn));
            return false;
        }

        $hashed_password_db = password_hash($password, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $email, $hashed_password_db, $fotografia, $rol_id);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        } else {
            error_log("Error al ejecutar la consulta para crear usuario: " . mysqli_stmt_error($stmt));
            return false;
        }
    }

    public function findByEmail($email) {
        $query = "SELECT u.id, u.nombre, u.email, u.password AS password_hash, u.fotografia, u.fecha_registro, 
                         u.rol_id, r.nombre_rol 
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.email = ? LIMIT 1";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            error_log("Error al preparar la consulta findByEmail: " . mysqli_error($this->conn));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user_data = mysqli_fetch_assoc($result)) {
            return $user_data;
        }
        return null;
    }

    public function findById($id) {
        // CAMBIO: Seleccionar también la contraseña (hash)
        $query = "SELECT u.id, u.nombre, u.email, u.password AS password_hash, u.fotografia, u.fecha_registro,
                         u.rol_id, r.nombre_rol
                  FROM " . $this->table_name . " u 
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.id = ? LIMIT 1";
        
        $stmt = mysqli_prepare($this->conn, $query);
         if (!$stmt) {
            error_log("Error al preparar la consulta findById: " . mysqli_error($this->conn));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user_data = mysqli_fetch_assoc($result)) {
            return $user_data;
        }
        return null;
    }
    
    // El método update simple se puede eliminar si UserController::updateProfile maneja toda la lógica.
    // Si se quiere mantener, debería ser más robusto o renombrarse para evitar confusión.
    // Por ahora, lo comentaré para evitar conflictos con la lógica del controlador.
    /*
    public function update($id, $nombre, $email, $fotografia = null) {
        // ... (código anterior) ...
    }
    */

    public function getAllUsers() {
        $usuarios = [];
        $query = "SELECT u.id, u.nombre, u.email, u.fecha_registro, u.fotografia, 
                         u.rol_id, r.nombre_rol 
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  ORDER BY u.fecha_registro DESC";
        
        $result = mysqli_query($this->conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $usuarios[] = $row;
            }
            mysqli_free_result($result);
        } else {
            error_log("Error al ejecutar la consulta getAllUsers: " . mysqli_error($this->conn));
        }
        return $usuarios;
    }

    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            error_log("Error al preparar la consulta emailExists: " . mysqli_error($this->conn));
            return false; 
        }
        
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt); 
        
        $num_rows = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $num_rows > 0; 
    }

    public function getFotografiaFilenameById($id) {
        $query = "SELECT fotografia FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->conn, $query);

        if (!$stmt) {
            error_log("Error al preparar la consulta getFotografiaFilenameById: " . mysqli_error($this->conn));
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['fotografia'];
        }
        return null;
    }
}
?>