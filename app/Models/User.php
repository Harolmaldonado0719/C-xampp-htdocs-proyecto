<?php

class User {
    private $conn; 
    private $table_name = "usuarios";

    // Propiedades de la clase
    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $password_hash;
    public $fotografia;
    public $rol_id;
    public $nombre_rol;
    public $telefono;
    public $activo;
    public $fecha_registro;
    public $fecha_modificacion;

    public function __construct($db_connection) {
        if ($db_connection === null) {
            error_log("User Model: La conexión a la base de datos es nula.");
        }
        $this->conn = $db_connection;
    }

    // MÉTODO CREATE (Llamado con PDO desde AuthController para registro)
    public function create($nombre, $apellido, $email, $password, $rol_id, $telefono = null, $fotografia = null, $activo = 1) {
        if ($this->conn === null || !($this->conn instanceof PDO)) {
            error_log("User::create - Conexión PDO no establecida o tipo incorrecto.");
            return false;
        }
        $query = "INSERT INTO " . $this->table_name .
                 " (nombre, apellido, email, password, rol_id, telefono, fotografia, activo, fecha_registro, fecha_modificacion)
                   VALUES (:nombre, :apellido, :email, :password, :rol_id, :telefono, :fotografia, :activo, NOW(), NOW())";

        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error al preparar la consulta para crear usuario (PDO): " . implode(" ", $this->conn->errorInfo()));
                return false;
            }

            $hashed_password_db = password_hash($password, PASSWORD_DEFAULT);
            $apellido_val = $apellido ?: null;

            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido_val, $apellido_val === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashed_password_db);
            $stmt->bindParam(":rol_id", $rol_id, PDO::PARAM_INT);
            $stmt->bindParam(":telefono", $telefono, $telefono === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(":fotografia", $fotografia, $fotografia === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(":activo", $activo, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $new_id = $this->conn->lastInsertId();
                $stmt->closeCursor();
                return $new_id;
            } else {
                error_log("Error al ejecutar la consulta para crear usuario (PDO): " . implode(" ", $stmt->errorInfo()));
                $stmt->closeCursor();
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDOException en User::create: " . $e->getMessage());
            return false;
        }
    }

    // MÉTODO UPDATE (Adaptado para PDO y mysqli)
    // El modelo se encarga de hashear la contraseña si $password_texto_plano no es null y no está vacío
    public function update($id, $nombre, $apellido, $email, $rol_id, $telefono, $fotografia, $activo, $password_texto_plano = null) {
        if ($this->conn === null) {
            error_log("User::update - Conexión no establecida. ID: $id");
            return false;
        }

        $id_f = filter_var($id, FILTER_VALIDATE_INT);
        if ($id_f === false) {
            error_log("User::update - ID inválido proporcionado: " . htmlspecialchars($id));
            return false;
        }

        $query_set_parts = [];
        $params_values_pdo = []; // Para PDO: ['nombre' => $nombre, ...]
        $params_values_mysqli = []; // Para mysqli: [$nombre, ...]
        $types_mysqli = "";       // Para mysqli: "ssisssi"

        if ($nombre !== null) { $query_set_parts[] = "nombre = :nombre_upd"; $params_values_pdo[':nombre_upd'] = $nombre; $types_mysqli .= 's'; $params_values_mysqli[] = $nombre; }
        if ($apellido !== null) { $query_set_parts[] = "apellido = :apellido_upd"; $params_values_pdo[':apellido_upd'] = $apellido; $types_mysqli .= 's'; $params_values_mysqli[] = $apellido; }
        if ($email !== null) { $query_set_parts[] = "email = :email_upd"; $params_values_pdo[':email_upd'] = $email; $types_mysqli .= 's'; $params_values_mysqli[] = $email; }
        if ($rol_id !== null) { $query_set_parts[] = "rol_id = :rol_id_upd"; $params_values_pdo[':rol_id_upd'] = $rol_id; $types_mysqli .= 'i'; $params_values_mysqli[] = $rol_id; }
        if ($telefono !== null) { $query_set_parts[] = "telefono = :telefono_upd"; $params_values_pdo[':telefono_upd'] = $telefono; $types_mysqli .= 's'; $params_values_mysqli[] = $telefono; }
        if ($fotografia !== null) { $query_set_parts[] = "fotografia = :fotografia_upd"; $params_values_pdo[':fotografia_upd'] = ($fotografia === '') ? null : $fotografia; $types_mysqli .= 's'; $params_values_mysqli[] = ($fotografia === '') ? null : $fotografia; }
        if ($activo !== null) { $query_set_parts[] = "activo = :activo_upd"; $params_values_pdo[':activo_upd'] = $activo; $types_mysqli .= 'i'; $params_values_mysqli[] = $activo; }

        if ($password_texto_plano !== null && !empty(trim($password_texto_plano))) {
            error_log("User::update - Actualizando contraseña para ID: $id_f.");
            $hashed_password_to_save = password_hash($password_texto_plano, PASSWORD_DEFAULT);
            $query_set_parts[] = "password = :password_upd";
            $params_values_pdo[':password_upd'] = $hashed_password_to_save;
            $types_mysqli .= 's';
            $params_values_mysqli[] = $hashed_password_to_save;
        } elseif ($password_texto_plano !== null && empty(trim($password_texto_plano))) {
            error_log("User::update - Se intentó actualizar con contraseña vacía para ID: $id_f. No se cambiará la contraseña.");
        }

        if (empty($query_set_parts)) {
            error_log("User::update - No hay campos para actualizar para el ID: $id_f");
            return true; // O false si se considera un error no tener nada que actualizar
        }

        $query_set_parts[] = "fecha_modificacion = NOW()"; // Siempre actualizar fecha_modificacion
        $query = "UPDATE " . $this->table_name . " SET " . implode(", ", $query_set_parts) . " WHERE id = :id_where_upd";

        // --- Manejo para PDO ---
        if ($this->conn instanceof PDO) {
            try {
                $stmt = $this->conn->prepare($query);
                if ($stmt === false) {
                    error_log("User::update - Error al preparar la consulta (PDO): " . implode(" ", $this->conn->errorInfo()) . " Query: " . $query);
                    return false;
                }
                $params_values_pdo[':id_where_upd'] = $id_f; // Añadir el ID para el WHERE

                if ($stmt->execute($params_values_pdo)) {
                    $affected_rows = $stmt->rowCount();
                    $stmt->closeCursor();
                    error_log("User::update (PDO) - Actualización exitosa para ID: $id_f. Filas afectadas: $affected_rows");
                    return true; // Devolver true incluso si affected_rows es 0, si la consulta fue exitosa.
                } else {
                    error_log("User::update (PDO) - Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()) . " Params: " . json_encode($params_values_pdo));
                    $stmt->closeCursor();
                    return false;
                }
            } catch (PDOException $e) {
                error_log("PDOException en User::update: " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof PDOStatement) { $stmt->closeCursor(); }
                return false;
            }
        }
        // --- Manejo para mysqli ---
        elseif ($this->conn instanceof mysqli) {
            // Reconstruir la query para mysqli con '?'
            $query_mysqli_set_parts = [];
            if ($nombre !== null) { $query_mysqli_set_parts[] = "nombre = ?"; }
            if ($apellido !== null) { $query_mysqli_set_parts[] = "apellido = ?"; }
            if ($email !== null) { $query_mysqli_set_parts[] = "email = ?"; }
            if ($rol_id !== null) { $query_mysqli_set_parts[] = "rol_id = ?"; }
            if ($telefono !== null) { $query_mysqli_set_parts[] = "telefono = ?"; }
            if ($fotografia !== null) { $query_mysqli_set_parts[] = "fotografia = ?"; }
            if ($activo !== null) { $query_mysqli_set_parts[] = "activo = ?"; }
            if ($password_texto_plano !== null && !empty(trim($password_texto_plano))) { $query_mysqli_set_parts[] = "password = ?"; }
            
            $query_mysqli_set_parts[] = "fecha_modificacion = NOW()";
            $query_mysqli = "UPDATE " . $this->table_name . " SET " . implode(", ", $query_mysqli_set_parts) . " WHERE id = ?";
            $types_mysqli .= 'i'; // Añadir tipo para el ID en WHERE
            $params_values_mysqli[] = $id_f; // Añadir valor del ID para el WHERE

            try {
                $stmt = $this->conn->prepare($query_mysqli);
                if ($stmt === false) {
                    error_log("User::update - Error al preparar la consulta (mysqli): " . $this->conn->error . " Query: " . $query_mysqli);
                    return false;
                }

                if (!empty($types_mysqli)) {
                    $stmt->bind_param($types_mysqli, ...$params_values_mysqli);
                }

                if ($stmt->execute()) {
                    $affected_rows = $stmt->affected_rows;
                    $stmt->close();
                    error_log("User::update (mysqli) - Actualización exitosa para ID: $id_f. Filas afectadas: $affected_rows");
                    return true;
                } else {
                    error_log("User::update (mysqli) - Error al ejecutar la consulta: " . $stmt->error . " Params: " . json_encode($params_values_mysqli));
                    $stmt->close();
                    return false;
                }
            } catch (Exception $e) {
                error_log("Exception en User::update (mysqli): " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
                return false;
            }
        } else {
            error_log("User::update - Tipo de conexión desconocido o no soportado. ID: $id_f");
            return false;
        }
    }

    // MÉTODO FINDBYEMAIL (Adaptado para manejar PDO o mysqli)
    public function findByEmail($email) {
        if ($this->conn === null) {
            error_log("User::findByEmail - Conexión no establecida.");
            return null;
        }

        $query = "SELECT u.id, u.nombre, u.apellido, u.email, u.password AS password_hash, u.fotografia, u.telefono, u.activo, u.fecha_registro, u.fecha_modificacion,
                         u.rol_id, r.nombre_rol
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.email = ? LIMIT 1";

        if ($this->conn instanceof PDO) {
            try {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    error_log("Error al preparar la consulta findByEmail (PDO): " . implode(" ", $this->conn->errorInfo()));
                    return null;
                }
                $stmt->bindParam(1, $email, PDO::PARAM_STR);
                if (!$stmt->execute()) {
                    error_log("Error al ejecutar la consulta findByEmail (PDO): " . implode(" ", $stmt->errorInfo()));
                    $stmt->closeCursor();
                    return null;
                }
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $user_data ?: null;
            } catch (PDOException $e) {
                error_log("PDOException en User::findByEmail: " . $e->getMessage());
                return null;
            }
        } elseif ($this->conn instanceof mysqli) {
            try {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    error_log("Error al preparar la consulta findByEmail (mysqli): " . $this->conn->error);
                    return null;
                }
                $stmt->bind_param("s", $email);
                if (!$stmt->execute()) {
                    error_log("Error al ejecutar la consulta findByEmail (mysqli): " . $stmt->error);
                    $stmt->close();
                    return null;
                }
                $result = $stmt->get_result();
                $user_data = $result->fetch_assoc();
                $stmt->close();
                return $user_data ?: null;
            } catch (Exception $e) {
                error_log("Exception en User::findByEmail (mysqli): " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
                return null;
            }
        } else {
            error_log("User::findByEmail - Tipo de conexión desconocido o no soportado.");
            return null;
        }
    }

    // MÉTODO FINDBYID (Adaptado para manejar PDO o mysqli)
    public function findById($id) {
        if ($this->conn === null) {
            error_log("User::findById - Conexión no establecida.");
            return null;
        }
        $id_f = filter_var($id, FILTER_VALIDATE_INT);
        if ($id_f === false) {
            error_log("User::findById - ID inválido proporcionado: " . htmlspecialchars($id));
            return null;
        }

        $query = "SELECT u.id, u.nombre, u.apellido, u.email, u.password AS password_hash, u.fotografia, u.telefono, u.activo, u.fecha_registro, u.fecha_modificacion,
                         u.rol_id, r.nombre_rol
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.id = ? LIMIT 1";

        if ($this->conn instanceof PDO) {
            try {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    error_log("Error al preparar la consulta findById (PDO): " . implode(" ", $this->conn->errorInfo()));
                    return null;
                }
                $stmt->bindParam(1, $id_f, PDO::PARAM_INT);
                if (!$stmt->execute()) {
                    error_log("Error al ejecutar la consulta findById (PDO): " . implode(" ", $stmt->errorInfo()));
                    $stmt->closeCursor();
                    return null;
                }
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $user_data ?: null;
            } catch (PDOException $e) {
                error_log("PDOException en User::findById: " . $e->getMessage());
                return null;
            }
        } elseif ($this->conn instanceof mysqli) {
            try {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    error_log("Error al preparar la consulta findById (mysqli): " . $this->conn->error . " Query: " . $query);
                    return null;
                }
                $stmt->bind_param("i", $id_f);
                if (!$stmt->execute()) {
                    error_log("Error al ejecutar la consulta findById (mysqli): " . $stmt->error);
                    $stmt->close();
                    return null;
                }
                $result = $stmt->get_result();
                $user_data = $result->fetch_assoc();
                $stmt->close();
                return $user_data ?: null;
            } catch (Exception $e) {
                error_log("Exception en User::findById (mysqli): " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
                return null;
            }
        } else {
            error_log("User::findById - Tipo de conexión desconocido o no soportado.");
            return null;
        }
    }

    // MÉTODO GETALLUSERS (Llamado con PDO desde AdminController)
    public function getAllUsers($limit = null, $offset = 0, $orderBy = 'id', $orderDir = 'ASC') {
        if ($this->conn === null || !($this->conn instanceof PDO)) {
            error_log("User::getAllUsers - Conexión PDO no establecida o tipo incorrecto.");
            return [];
        }
        $query = "SELECT u.id, u.nombre, u.apellido, u.email, u.fecha_registro, u.fotografia, u.telefono, u.activo,
                         u.rol_id, r.nombre_rol
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id";

        $allowed_columns = ['id', 'nombre', 'apellido', 'email', 'fecha_registro', 'rol_id', 'activo', 'nombre_rol'];
        $orderBySanitized = "u.id"; 
        if (in_array($orderBy, $allowed_columns)) {
            $orderBySanitized = ($orderBy === 'nombre_rol' ? 'r.' : 'u.') . $orderBy;
        }

        $orderDirSanitized = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $query .= " ORDER BY " . $orderBySanitized . " " . $orderDirSanitized;

        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset > 0) {
                $query .= " OFFSET :offset";
            }
        }

        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error al preparar la consulta getAllUsers (PDO): " . implode(" ", $this->conn->errorInfo()));
                return [];
            }

            if ($limit !== null) {
                 $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                if ($offset > 0) {
                    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $usuarios;

        } catch (PDOException $e) {
            error_log("PDOException en User::getAllUsers: " . $e->getMessage());
            return [];
        }
    }

    public function getUsersWithRoles($limit = null, $offset = 0, $orderBy = 'id', $orderDir = 'ASC') {
        return $this->getAllUsers($limit, $offset, $orderBy, $orderDir);
    }

    // MÉTODO EMAILEXISTS (Adaptado para manejar PDO o mysqli)
    public function emailExists($email) {
        if ($this->conn === null) {
            error_log("User::emailExists - Conexión no establecida.");
            return false;
        }
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 1";

        if ($this->conn instanceof PDO) {
            try {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    error_log("Error al preparar la consulta emailExists (PDO): " . implode(" ", $this->conn->errorInfo()));
                    return false;
                }
                $stmt->bindParam(1, $email, PDO::PARAM_STR);
                $stmt->execute();
                $found = $stmt->fetchColumn() !== false;
                $stmt->closeCursor();
                return $found;
            } catch (PDOException $e) {
                error_log("PDOException en User::emailExists: " . $e->getMessage());
                return false;
            }
        } elseif ($this->conn instanceof mysqli) {
            try {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    error_log("Error al preparar la consulta emailExists (mysqli): " . $this->conn->error);
                    return false;
                }
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                $found = $stmt->num_rows > 0;
                $stmt->close();
                return $found;
            } catch (Exception $e) {
                error_log("Exception en User::emailExists (mysqli): " . $e->getMessage());
                if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
                return false;
            }
        } else {
            error_log("User::emailExists - Tipo de conexión desconocido o no soportado.");
            return false;
        }
    }

    // MÉTODO GETFOTOGRAFIAFILENAMEBYID (Asume mysqli, usado por UserController)
    public function getFotografiaFilenameById($id) {
        if ($this->conn === null || !($this->conn instanceof mysqli)) {
            error_log("User::getFotografiaFilenameById - Conexión mysqli no establecida o tipo incorrecto. ID: $id");
            return null;
        }
        $query = "SELECT fotografia FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error al preparar la consulta getFotografiaFilenameById (mysqli): " . $this->conn->error);
                return null;
            }
            $id_f = filter_var($id, FILTER_VALIDATE_INT);
            if($id_f === false) {
                error_log("User::getFotografiaFilenameById - ID inválido: " . htmlspecialchars($id));
                return null;
            }
            $stmt->bind_param("i", $id_f);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row ? $row['fotografia'] : null;
        } catch (Exception $e) {
            error_log("Exception en User::getFotografiaFilenameById (mysqli): " . $e->getMessage());
            if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
            return null;
        }
    }

    // MÉTODO CONTARUSUARIOS (Asume PDO, llamado desde AdminController)
    public function contarUsuarios() {
        if ($this->conn === null || !($this->conn instanceof PDO)) {
            error_log("User::contarUsuarios - Conexión PDO no establecida o tipo incorrecto.");
            return 0;
        }
        $query = "SELECT COUNT(id) as total FROM " . $this->table_name;
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Error al preparar la consulta contarUsuarios (PDO): " . implode(" ", $this->conn->errorInfo()));
                return 0;
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            error_log("PDOException en User::contarUsuarios: " . $e->getMessage());
            return 0;
        }
    }

    // MÉTODO CAMBIARESTADOACTIVACION (Asume PDO, llamado desde AdminController)
    public function cambiarEstadoActivacion($id, $estado) {
        if ($this->conn === null || !($this->conn instanceof PDO)) {
            error_log("User::cambiarEstadoActivacion - Conexión PDO no establecida o tipo incorrecto. ID: $id");
            return false;
        }
        $query = "UPDATE " . $this->table_name . " SET activo = :activo, fecha_modificacion = NOW() WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("User::cambiarEstadoActivacion - Error al preparar la consulta (PDO): " . implode(" ", $this->conn->errorInfo()));
                return false;
            }
            $id_f = filter_var($id, FILTER_VALIDATE_INT);
            $estado_f = filter_var($estado, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);

            if ($id_f === false || $estado_f === false) {
                error_log("User::cambiarEstadoActivacion - ID o estado inválido. ID: " . htmlspecialchars($id) . ", Estado: " . htmlspecialchars($estado));
                return false;
            }
            $stmt->bindParam(":activo", $estado_f, PDO::PARAM_INT);
            $stmt->bindParam(":id", $id_f, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $affected_rows = $stmt->rowCount();
                $stmt->closeCursor();
                return $affected_rows > 0;
            } else {
                error_log("User::cambiarEstadoActivacion - Error al ejecutar la consulta (PDO): " . implode(" ", $stmt->errorInfo()));
                $stmt->closeCursor();
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDOException en User::cambiarEstadoActivacion: " . $e->getMessage());
            return false;
        }
    }

    public function desactivar($id) {
        return $this->cambiarEstadoActivacion($id, 0);
    }

    public function activar($id) {
        return $this->cambiarEstadoActivacion($id, 1);
    }
}
?>