<?php
// config.php ya está incluido por public/index.php
require_once __DIR__ . '/../Core/Database.php';

class UserController {
    private $db;
    private $conn;

    public function __construct() {
        // Siempre asegurar que el usuario esté logueado para acceder a estas funciones
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
        } catch (Exception $e) {
            error_log("UserController: Error de conexión a BD - " . $e->getMessage());
            // Manejar error de conexión, quizás mostrar una página de error
            // o redirigir con un mensaje.
            // Por ahora, un die() simple para desarrollo:
            die("Error crítico: No se pudo conectar a la base de datos para las operaciones de usuario.");
        }
    }

    /**
     * Muestra la página de perfil del usuario logueado.
     */
    public function showProfile() {
        $pageTitle = "Mi Perfil";
        $usuario_id = $_SESSION['usuario_id'];
        $userData = null;

        $sql = "SELECT id, nombre, email, fotografia, fecha_registro FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $usuario_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $userData = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        } else {
            error_log("Error al preparar la consulta para obtener datos del perfil: " . mysqli_error($this->conn));
            // Manejar el error, quizás mostrar un mensaje en la vista
        }

        if (!$userData) {
            // Usuario no encontrado, aunque debería existir si está en sesión.
            // Podría ser un error o una condición inesperada.
            // Redirigir o mostrar error.
            $_SESSION['mensaje_error'] = "No se pudieron cargar los datos de tu perfil.";
            header("Location: " . BASE_URL . "dashboard"); // O una página de error
            exit;
        }
        
        // Construir la URL de la fotografía si existe
        if (!empty($userData['fotografia'])) {
            $userData['fotografia_url'] = BASE_URL . 'uploads/' . htmlspecialchars($userData['fotografia']);
        } else {
            $userData['fotografia_url'] = BASE_URL . 'img/default-avatar.png'; // Una imagen por defecto
        }


        $viewPath = __DIR__ . '/../Views/user/profile.php'; // Necesitarás crear esta vista
        extract(['pageTitle' => $pageTitle, 'user' => $userData]); // Pasar datos a la vista
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    /**
     * Muestra el formulario para editar el perfil del usuario logueado.
     */
    public function editProfileForm() {
        $pageTitle = "Editar Mi Perfil";
        $usuario_id = $_SESSION['usuario_id'];
        $userData = null;

        // Obtener datos actuales del usuario para pre-rellenar el formulario
        $sql = "SELECT id, nombre, email, fotografia FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $usuario_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $userData = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        } else {
            error_log("Error al preparar la consulta para editar perfil: " . mysqli_error($this->conn));
        }
        
        if (!$userData) {
            $_SESSION['mensaje_error'] = "No se pudieron cargar los datos para editar tu perfil.";
            header("Location: " . BASE_URL . "profile"); // Volver al perfil
            exit;
        }
        
        if (!empty($userData['fotografia'])) {
            $userData['fotografia_url_actual'] = BASE_URL . 'uploads/' . htmlspecialchars($userData['fotografia']);
        }


        $viewPath = __DIR__ . '/../Views/user/edit_profile.php'; // Necesitarás crear esta vista
        // Pasar datos y posibles mensajes de error/éxito de una operación previa
        $data_for_view = [
            'pageTitle' => $pageTitle, 
            'user' => $userData,
            'mensaje_error' => $_SESSION['mensaje_error'] ?? null,
            'mensaje_exito' => $_SESSION['mensaje_exito'] ?? null,
        ];
        unset($_SESSION['mensaje_error'], $_SESSION['mensaje_exito']); // Limpiar mensajes flash

        extract($data_for_view);
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    /**
     * Procesa la actualización del perfil del usuario.
     * Se llamaría cuando se envía el formulario de edit_profile.php
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Solo permitir POST para actualizaciones
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }

        $usuario_id = $_SESSION['usuario_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        // Manejo de contraseña y foto sería más complejo

        // --- VALIDACIONES ---
        if (empty($nombre) || empty($email)) {
            $_SESSION['mensaje_error'] = "Nombre y correo electrónico son obligatorios.";
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['mensaje_error'] = "Formato de correo electrónico inválido.";
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }

        // Verificar si el nuevo email ya está en uso por OTRO usuario
        $sql_check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt_check = mysqli_prepare($this->conn, $sql_check_email);
        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "si", $email, $usuario_id);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                mysqli_stmt_close($stmt_check);
                $_SESSION['mensaje_error'] = "El correo electrónico ya está registrado por otro usuario.";
                header("Location: " . BASE_URL . "profile/edit");
                exit;
            }
            mysqli_stmt_close($stmt_check);
        } else {
            error_log("Error al verificar email en updateProfile: " . mysqli_error($this->conn));
            $_SESSION['mensaje_error'] = "Error del servidor al verificar el email.";
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }
        
        // Lógica para actualizar la foto (similar a handleRegister)
        $nombre_archivo_db_actual = null; // Obtener el nombre actual de la foto de la BD
        $sql_get_photo = "SELECT fotografia FROM usuarios WHERE id = ?";
        $stmt_get_photo = mysqli_prepare($this->conn, $sql_get_photo);
        if($stmt_get_photo) {
            mysqli_stmt_bind_param($stmt_get_photo, "i", $usuario_id);
            mysqli_stmt_execute($stmt_get_photo);
            mysqli_stmt_bind_result($stmt_get_photo, $nombre_archivo_db_actual);
            mysqli_stmt_fetch($stmt_get_photo);
            mysqli_stmt_close($stmt_get_photo);
        }

        $nombre_archivo_para_update = $nombre_archivo_db_actual; // Por defecto, mantener la foto actual

        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == UPLOAD_ERR_OK) {
            // ... (Lógica de validación y subida de la nueva foto, similar a handleRegister)
            // ... (Si se sube una nueva, eliminar la foto antigua del servidor si existe)
            // ... ($nombre_archivo_para_update = $nuevo_nombre_archivo_subido;)
            // Esta parte es un placeholder, necesitarías la lógica completa de subida de archivos aquí.
            // Por simplicidad, la omito por ahora, pero es crucial.
            // Ejemplo rápido (sin validaciones completas):
            $nombre_temporal_archivo = $_FILES['fotografia']['tmp_name'];
            $nombre_original_archivo = $_FILES['fotografia']['name'];
            $extension_archivo = strtolower(pathinfo($nombre_original_archivo, PATHINFO_EXTENSION));
            $nombre_archivo_unico = uniqid('user_', true) . '.' . $extension_archivo;
            $ruta_destino_completa = rtrim(APP_UPLOAD_DIR, '/') . '/' . $nombre_archivo_unico;

            if (move_uploaded_file($nombre_temporal_archivo, $ruta_destino_completa)) {
                // Eliminar foto antigua si existe y es diferente
                if ($nombre_archivo_db_actual && file_exists(rtrim(APP_UPLOAD_DIR, '/') . '/' . $nombre_archivo_db_actual)) {
                    if ($nombre_archivo_db_actual !== $nombre_archivo_unico) { // Asegurarse de no borrar la misma si se resube
                         unlink(rtrim(APP_UPLOAD_DIR, '/') . '/' . $nombre_archivo_db_actual);
                    }
                }
                $nombre_archivo_para_update = $nombre_archivo_unico;
            } else {
                 $_SESSION['mensaje_error'] = "Error al subir la nueva fotografía.";
                 header("Location: " . BASE_URL . "profile/edit");
                 exit;
            }
        }


        // --- ACTUALIZAR DATOS EN LA BD ---
        // Considerar si se actualiza la contraseña también (requeriría campos de contraseña actual y nueva)
        $sql_update = "UPDATE usuarios SET nombre = ?, email = ?, fotografia = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($this->conn, $sql_update);

        if ($stmt_update) {
            mysqli_stmt_bind_param($stmt_update, "sssi", $nombre, $email, $nombre_archivo_para_update, $usuario_id);
            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['mensaje_exito'] = "Perfil actualizado correctamente.";
                // Actualizar nombre en sesión si cambió
                if ($_SESSION['usuario_nombre'] !== $nombre) {
                    $_SESSION['usuario_nombre'] = $nombre;
                }
            } else {
                error_log("Error al actualizar perfil: " . mysqli_stmt_error($stmt_update));
                $_SESSION['mensaje_error'] = "Error al actualizar el perfil en la base de datos.";
            }
            mysqli_stmt_close($stmt_update);
        } else {
            error_log("Error al preparar la consulta UPDATE para perfil: " . mysqli_error($this->conn));
            $_SESSION['mensaje_error'] = "Error del servidor al procesar la actualización.";
        }

        header("Location: " . BASE_URL . "profile/edit"); // Redirigir de nuevo al formulario de edición para ver mensajes
        exit;
    }


    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>