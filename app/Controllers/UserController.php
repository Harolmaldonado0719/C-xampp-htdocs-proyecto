<?php
// config.php ya está incluido por public/index.php, que define BASE_URL y APP_UPLOAD_DIR
require_once __DIR__ . '/../Core/Database.php';
// require_once __DIR__ . '/../Models/User.php'; // Descomenta y usa si tienes un modelo User

class UserController {
    private $db;
    private $conn;
    // private $userModel; // Descomenta si decides usar el modelo aquí

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); 
        }

        // CORRECCIÓN: Usar la clave de sesión consistente 'user_id'
        if (!isset($_SESSION['user_id'])) { 
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder a esta sección.";
            header("Location: " . BASE_URL . "login");
            exit;
        }

        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
            // $this->userModel = new User($this->conn); // Instancia tu modelo aquí
        } catch (Exception $e) {
            error_log("UserController: Error de conexión a BD - " . $e->getMessage());
            // En un entorno de producción, no muestres die() con detalles sensibles.
            // Considera una página de error genérica.
            die("Error crítico: No se pudo conectar a la base de datos para las operaciones de usuario. Por favor, intente más tarde.");
        }
    }

    public function showProfile() {
        $pageTitle = "Mi Perfil";
        // CORRECCIÓN: Usar la clave de sesión consistente 'user_id'
        $usuario_id = $_SESSION['user_id']; 
        $userData = null;

        // Si usaras un modelo:
        // if ($this->userModel) {
        //     $userData = $this->userModel->findProfileDataById($usuario_id); // Un método que haga el JOIN
        // } else {
            // Consulta directa
            $sql = "SELECT u.id, u.nombre, u.email, u.fotografia, u.fecha_registro, r.nombre_rol 
                    FROM usuarios u
                    LEFT JOIN roles r ON u.rol_id = r.id
                    WHERE u.id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $usuario_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $userData = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
            } else {
                error_log("Error al preparar la consulta para obtener datos del perfil (showProfile): " . mysqli_error($this->conn));
            }
        // }

        if (!$userData) {
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar los datos de tu perfil. Es posible que la sesión haya expirado o el usuario no exista.";
            $this->redirectToDashboardPorRol(); // Este método ya hace exit;
            // No necesitas un exit adicional aquí si redirectToDashboardPorRol siempre hace exit.
        }
        
        // Construir URL de fotografía
        if (!empty($userData['fotografia'])) {
            // Asegúrate que APP_UPLOAD_DIR_PUBLIC_PATH esté definida en config.php y sea la ruta relativa correcta desde la raíz web a la carpeta uploads
            // Ejemplo: define('APP_UPLOAD_DIR_PUBLIC_PATH', 'uploads/'); si BASE_URL no incluye /public/
            // O define('APP_UPLOAD_DIR_PUBLIC_PATH', '../uploads/'); si BASE_URL incluye /public/ y uploads está al mismo nivel que public
            // Para tu estructura actual con BASE_URL terminando en /public/ y uploads dentro de public:
            $uploadPath = 'uploads/'; // Relativo a BASE_URL si BASE_URL es http://localhost/Proyecto-clip/public/
            $userData['fotografia_url'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($userData['fotografia']);
        } else {
            $userData['fotografia_url'] = BASE_URL . 'img/default-avatar.png'; 
        }
        
        $mensaje_exito_perfil = $_SESSION['mensaje_exito_perfil'] ?? null;
        $mensaje_error_perfil = $_SESSION['mensaje_error_perfil'] ?? null;
        unset($_SESSION['mensaje_exito_perfil'], $_SESSION['mensaje_error_perfil']);

        $viewPath = dirname(__DIR__) . '/Views/user/profile.php'; 
        extract(compact('pageTitle', 'userData', 'mensaje_exito_perfil', 'mensaje_error_perfil', 'viewPath'));
        include dirname(__DIR__) . '/Views/layouts/main_layout.php';
    }

    public function editProfileForm() {
        $pageTitle = "Editar Mi Perfil";
        // CORRECCIÓN: Usar la clave de sesión consistente 'user_id'
        $usuario_id = $_SESSION['user_id'];
        $userDataFromDB = null; 
        
        $sql = "SELECT id, nombre, email, fotografia FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $usuario_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $userDataFromDB = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        } else {
            error_log("Error al preparar la consulta para editar perfil (editProfileForm): " . mysqli_error($this->conn));
        }
        
        if (!$userDataFromDB) {
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar los datos para editar tu perfil.";
            // Redirigir al perfil normal, no al dashboard genérico, ya que el perfil podría existir.
            header("Location: " . BASE_URL . "profile"); 
            exit;
        }
        
        // Renombrar para claridad en la vista, $userDataFromDB es para la foto actual
        $currentPhotoData = $userDataFromDB; 
        if (!empty($currentPhotoData['fotografia'])) {
            $uploadPath = 'uploads/'; // Relativo a BASE_URL
            $currentPhotoData['fotografia_url_actual'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($currentPhotoData['fotografia']);
        } else {
            $currentPhotoData['fotografia_url_actual'] = BASE_URL . 'img/default-avatar.png';
        }

        $nombre_val = $_SESSION['form_data_perfil']['nombre'] ?? $userDataFromDB['nombre'];
        $email_val = $_SESSION['form_data_perfil']['email'] ?? $userDataFromDB['email'];
        $mensaje_error_perfil_edit = $_SESSION['mensaje_error_perfil_edit'] ?? null;
        unset($_SESSION['form_data_perfil'], $_SESSION['mensaje_error_perfil_edit']);

        // ----- INICIO DE LA CORRECCIÓN -----
        // 1. CREAR LA VARIABLE $userData AQUÍ:
        //    Asignamos el contenido de $currentPhotoData (que tiene la información de la foto)
        //    a una nueva variable llamada $userData. La vista espera $userData.
        $userData = $currentPhotoData;
        // ----- FIN DE LA CORRECCIÓN -----

        $viewPath = dirname(__DIR__) . '/Views/user/edit_profile.php';
        
        // 2. CORREGIR LA LLAMADA A compact() AQUÍ:
        //    Ahora pasamos 'userData' como un string, que es el nombre de la variable que acabamos de crear.
        extract(compact('pageTitle', 'userData', 'nombre_val', 'email_val', 'mensaje_error_perfil_edit', 'viewPath'));
        
        include dirname(__DIR__) . '/Views/layouts/main_layout.php';
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }

        // CORRECCIÓN: Usar la clave de sesión consistente 'user_id'
        $usuario_id = $_SESSION['user_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';
        
        $errors = []; // Acumular errores
        $_SESSION['form_data_perfil'] = ['nombre' => $nombre, 'email' => $email]; // Guardar para repoblar

        if (empty($nombre)) {
            $errors[] = "El nombre es obligatorio.";
        }
        if (empty($email)) {
            $errors[] = "El correo electrónico es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Formato de correo electrónico inválido.";
        } else {
            // Verificar si el email ya existe para OTRO usuario
            $sql_check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
            $stmt_check = mysqli_prepare($this->conn, $sql_check_email);
            if ($stmt_check) {
                mysqli_stmt_bind_param($stmt_check, "si", $email, $usuario_id);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);
                if (mysqli_stmt_num_rows($stmt_check) > 0) {
                    $errors[] = "El correo electrónico ya está registrado por otro usuario.";
                }
                mysqli_stmt_close($stmt_check);
            } else {
                error_log("Error al verificar email en updateProfile: " . mysqli_error($this->conn));
                $errors[] = "Error del servidor al verificar el email. Intente de nuevo.";
            }
        }
        
        $new_password_hash = null;
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $errors[] = "Debes ingresar tu contraseña actual para cambiarla.";
            } else {
                $sql_get_pass = "SELECT password FROM usuarios WHERE id = ?";
                $stmt_get_pass = mysqli_prepare($this->conn, $sql_get_pass);
                mysqli_stmt_bind_param($stmt_get_pass, "i", $usuario_id);
                mysqli_stmt_execute($stmt_get_pass);
                $result_pass = mysqli_stmt_get_result($stmt_get_pass);
                $user_pass_data = mysqli_fetch_assoc($result_pass);
                mysqli_stmt_close($stmt_get_pass);

                if (!$user_pass_data || !password_verify($current_password, $user_pass_data['password'])) {
                    $errors[] = "La contraseña actual es incorrecta.";
                } elseif (strlen($new_password) < 6) {
                    $errors[] = "La nueva contraseña debe tener al menos 6 caracteres.";
                } elseif ($new_password !== $confirm_new_password) {
                    $errors[] = "Las nuevas contraseñas no coinciden.";
                } else {
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
        }

        $nombre_archivo_final_para_db = null; // Nombre del archivo a guardar en BD
        // Obtener foto actual para posible eliminación y para mantenerla si no se sube nueva
        $sql_get_photo = "SELECT fotografia FROM usuarios WHERE id = ?";
        $stmt_get_photo = mysqli_prepare($this->conn, $sql_get_photo);
        mysqli_stmt_bind_param($stmt_get_photo, "i", $usuario_id);
        mysqli_stmt_execute($stmt_get_photo);
        $result_photo = mysqli_stmt_get_result($stmt_get_photo);
        $current_photo_data_from_db = mysqli_fetch_assoc($result_photo); // Renombrado para evitar confusión con $currentPhotoData de arriba
        mysqli_stmt_close($stmt_get_photo);
        $nombre_archivo_db_actual = $current_photo_data_from_db['fotografia'] ?? null;
        $nombre_archivo_final_para_db = $nombre_archivo_db_actual; // Por defecto, mantener la foto actual

        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = rtrim(APP_UPLOAD_DIR, '/') . '/'; // Asegurar trailing slash
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0775, true)) {
                    $errors[] = "Error al crear el directorio de subidas.";
                    // No continuar con la subida si el directorio no se puede crear
                }
            }
            
            if (is_dir($uploadDir)) { // Solo proceder si el directorio existe o se creó
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['fotografia']['tmp_name']); // Más seguro que $_FILES['fotografia']['type']
                $maxSize = 2 * 1024 * 1024; // 2MB

                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "Tipo de archivo de fotografía no permitido. Solo JPG, PNG, GIF.";
                } elseif ($_FILES['fotografia']['size'] > $maxSize) {
                    $errors[] = "La fotografía es demasiado grande (máximo 2MB).";
                } else {
                    $extension_archivo = strtolower(pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION));
                    $nombre_archivo_unico = uniqid('user_' . $usuario_id . '_', true) . '.' . $extension_archivo;
                    $ruta_destino_completa = $uploadDir . $nombre_archivo_unico;

                    if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_destino_completa)) {
                        // Eliminar foto antigua si existe y no es la default
                        if ($nombre_archivo_db_actual && $nombre_archivo_db_actual !== 'default-avatar.png' && file_exists($uploadDir . $nombre_archivo_db_actual)) {
                            @unlink($uploadDir . $nombre_archivo_db_actual);
                        }
                        $nombre_archivo_final_para_db = $nombre_archivo_unico; // Nueva foto subida
                    } else {
                        $errors[] = "Error al mover la nueva fotografía al directorio de subidas.";
                    }
                }
            }
        } elseif (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] != UPLOAD_ERR_NO_FILE) {
            // Hubo un error de subida diferente a "no se subió archivo"
            $errors[] = "Error al subir la fotografía. Código: " . $_FILES['fotografia']['error'];
        }

        // Si hay errores de validación o subida, redirigir de vuelta al formulario de edición
        if (!empty($errors)) {
            $_SESSION['mensaje_error_perfil_edit'] = implode("<br>", $errors);
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }

        // Si no hay errores, proceder a actualizar la base de datos
        $update_fields_sql = ["nombre = ?", "email = ?"];
        $update_values = [$nombre, $email];
        $types = "ss"; 

        if ($new_password_hash) {
            $update_fields_sql[] = "password = ?";
            $update_values[] = $new_password_hash;
            $types .= "s";
        }
        
        // Siempre actualizar el campo fotografia, incluso si es el mismo nombre o null
        $update_fields_sql[] = "fotografia = ?";
        $update_values[] = $nombre_archivo_final_para_db; // Puede ser el nuevo, el viejo, o null
        $types .= "s";

        $sql_update = "UPDATE usuarios SET " . implode(", ", $update_fields_sql) . " WHERE id = ?";
        $update_values[] = $usuario_id; 
        $types .= "i";

        $stmt_update = mysqli_prepare($this->conn, $sql_update);

        if ($stmt_update) {
            mysqli_stmt_bind_param($stmt_update, $types, ...$update_values);
            if (mysqli_stmt_execute($stmt_update)) {
                unset($_SESSION['form_data_perfil']);
                $_SESSION['mensaje_exito_perfil'] = "Perfil actualizado correctamente.";
                
                // Actualizar nombre en sesión si cambió
                if (isset($_SESSION['user_nombre']) && $_SESSION['user_nombre'] !== $nombre) {
                    $_SESSION['user_nombre'] = $nombre;
                }
                
                // CORRECCIÓN: Actualizar URL de fotografía en sesión
                $new_photo_url = BASE_URL . 'img/default-avatar.png';
                if ($nombre_archivo_final_para_db) {
                    $uploadPath = 'uploads/'; // Relativo a BASE_URL
                    $new_photo_url = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($nombre_archivo_final_para_db);
                }
                $_SESSION['user_fotografia_url'] = $new_photo_url; // Asumiendo que esta es la clave que usa el layout

                header("Location: " . BASE_URL . "profile"); 
                exit;
            } else {
                error_log("Error al actualizar perfil (execute): " . mysqli_stmt_error($stmt_update));
                $_SESSION['mensaje_error_perfil_edit'] = "Error al actualizar el perfil en la base de datos.";
            }
            mysqli_stmt_close($stmt_update);
        } else {
            error_log("Error al preparar la consulta UPDATE para perfil: " . mysqli_error($this->conn));
            $_SESSION['mensaje_error_perfil_edit'] = "Error del servidor al procesar la actualización.";
        }

        // Si la actualización falla por alguna razón, redirigir a editar con mensaje
        header("Location: " . BASE_URL . "profile/edit"); 
        exit;
    }

    private function redirectToDashboardPorRol() {
        // CORRECCIÓN: Usar la clave de sesión consistente 'user_rol_id'
        $rol_id_sesion = $_SESSION['user_rol_id'] ?? null;
        
        // Si user_rol_id no está en sesión pero user_id sí, intentar obtenerlo de la BD
        // Esto es una contingencia, idealmente user_rol_id siempre debería estar en sesión.
        if ($rol_id_sesion === null && isset($_SESSION['user_id'])) {
            $sql_get_rol = "SELECT rol_id FROM usuarios WHERE id = ?";
            $stmt_get_rol = mysqli_prepare($this->conn, $sql_get_rol);
            if ($stmt_get_rol) {
                mysqli_stmt_bind_param($stmt_get_rol, "i", $_SESSION['user_id']);
                mysqli_stmt_execute($stmt_get_rol);
                $result_rol = mysqli_stmt_get_result($stmt_get_rol);
                $user_rol_data = mysqli_fetch_assoc($result_rol);
                mysqli_stmt_close($stmt_get_rol);
                if ($user_rol_data && isset($user_rol_data['rol_id'])) {
                    $rol_id_sesion = $user_rol_data['rol_id'];
                    $_SESSION['user_rol_id'] = $rol_id_sesion; // Actualizar en sesión para futuras comprobaciones
                } else {
                    error_log("redirectToDashboardPorRol: No se pudo obtener rol_id para usuario_id: " . $_SESSION['user_id']);
                }
            } else {
                 error_log("redirectToDashboardPorRol: Error al preparar consulta para obtener rol_id: " . mysqli_error($this->conn));
            }
        }

        if ($rol_id_sesion !== null) {
            if (defined('ID_ROL_ADMIN') && $rol_id_sesion == ID_ROL_ADMIN) {
                header("Location: " . BASE_URL . "dashboard"); exit;
            } elseif (defined('ID_ROL_EMPLEADO') && $rol_id_sesion == ID_ROL_EMPLEADO) {
                header("Location: " . BASE_URL . "empleado/dashboard"); exit;
            } elseif (defined('ID_ROL_CLIENTE') && $rol_id_sesion == ID_ROL_CLIENTE) {
                header("Location: " . BASE_URL . "portal_servicios"); exit;
            } else {
                error_log("redirectToDashboardPorRol: rol_id_sesion (" . $rol_id_sesion . ") no coincide con roles definidos.");
            }
        } else {
            error_log("redirectToDashboardPorRol: rol_id_sesion es null, no se puede determinar el dashboard.");
        }
        
        // Fallback si todo lo demás falla o el rol no es reconocido
        $_SESSION['mensaje_error_global'] = "No se pudo determinar tu panel de control. Se cerrará la sesión.";
        // Forzar logout si no se puede determinar el dashboard
        unset($_SESSION['user_id'], $_SESSION['user_rol_id'], $_SESSION['user_nombre'], $_SESSION['user_fotografia_url']);
        header("Location: " . BASE_URL . "login"); 
        exit;
    }

    public function __destruct() {
        if (isset($this->db) && $this->db instanceof Database) { // Verificar que sea una instancia de Database
            $this->db->close();
        }
    }
}
?>