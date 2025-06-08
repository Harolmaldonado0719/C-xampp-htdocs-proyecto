<?php

class User {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades de la clase
    public $id;
    public $nombre;
    public $email;
    public $password_hash; // O simplemente password si no lo hasheas aquí directamente
    public $fotografia;
    public $rol;
    public $fecha_registro;

    /**
     * Constructor de la clase User.
     * @param mixed $db_connection La conexión a la base de datos.
     */
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param string $nombre
     * @param string $email
     * @param string $password La contraseña en texto plano
     * @param string $rol El rol del usuario (ej. 'cliente', 'admin')
     * @param string|null $fotografia Nombre del archivo de la fotografía (opcional)
     * @return int|false El ID del usuario insertado o false en error.
     */
    public function create($nombre, $email, $password, $rol = 'cliente', $fotografia = null) {
        $query = "INSERT INTO " . $this->table_name . " (nombre, email, password, fotografia, rol, fecha_registro) VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            error_log("Error al preparar la consulta para crear usuario: " . mysqli_error($this->conn));
            return false;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        mysqli_stmt_bind_param($stmt, "sssss", $nombre, $email, $hashed_password, $fotografia, $rol);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        } else {
            error_log("Error al ejecutar la consulta para crear usuario: " . mysqli_stmt_error($stmt));
            return false;
        }
    }

    /**
     * Busca un usuario por su email.
     * @param string $email
     * @return array|null Los datos del usuario (incluyendo rol) o null.
     */
    public function findByEmail($email) {
        $query = "SELECT id, nombre, email, password, fotografia, rol, fecha_registro FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        
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

    /**
     * Busca un usuario por su ID.
     * @param int $id
     * @return array|null Los datos del usuario (incluyendo rol) o null.
     */
    public function findById($id) {
        $query = "SELECT id, nombre, email, fotografia, rol, fecha_registro FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
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
    
    /**
     * Actualiza los datos de un usuario.
     * @param int $id
     * @param string $nombre
     * @param string $email
     * @param string|null $fotografia
     * @return bool True en éxito, false en error.
     */
    public function update($id, $nombre, $email, $fotografia = null) {
        if ($fotografia !== null) { 
            $query = "UPDATE " . $this->table_name . " SET nombre = ?, email = ?, fotografia = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                error_log("Error al preparar la consulta update (con foto): " . mysqli_error($this->conn));
                return false;
            }
            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $fotografia, $id);
        } else {
            $query = "UPDATE " . $this->table_name . " SET nombre = ?, email = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                error_log("Error al preparar la consulta update (sin foto): " . mysqli_error($this->conn));
                return false;
            }
            mysqli_stmt_bind_param($stmt, "ssi", $nombre, $email, $id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            return true; 
        } else {
            error_log("Error al ejecutar la consulta update: " . mysqli_stmt_error($stmt));
            return false;
        }
    }

    /**
     * Obtiene todos los usuarios (incluyendo rol).
     * @return array
     */
    public function getAllUsers() {
        $usuarios = [];
        $query = "SELECT id, nombre, email, fecha_registro, fotografia, rol FROM " . $this->table_name . " ORDER BY fecha_registro DESC";
        
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

    /**
     * Verifica si un email ya existe en la base de datos.
     * @param string $email
     * @return bool True si el email existe, false si no.
     */
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
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            return true; 
        }
        
        mysqli_stmt_close($stmt);
        return false; 
    }

    /**
     * Obtiene el nombre del archivo de fotografía de un usuario por su ID.
     * @param int $id
     * @return string|null El nombre del archivo o null si no tiene o no se encuentra.
     */
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

} // <-- Esta llave cierra la clase User
?>