<?php
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/User.php'; // Asegúrate de que el nombre del archivo sea User.php (mayúscula inicial)

class AuthController {
    private $db;
    private $conn;
    private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
            $this->userModel = new User($this->conn); // Modelo User.php
        } catch (Exception $e) {
            error_log("AuthController: Error de conexión a BD - " . $e->getMessage());
            die("Error crítico: No se pudo conectar al sistema de autenticación.");
        }
    }

    public function showLoginForm($mensaje_error = null, $mensaje_exito = null) {
        $pageTitle = "Iniciar Sesión";
        // Si el usuario ya está logueado, redirigir según rol
        if (isset($_SESSION['usuario_id'])) {
            if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
                header("Location: " . BASE_URL . "dashboard");
            } else {
                header("Location: " . BASE_URL . "portal_servicios");
            }
            exit;
        }
        $viewPath = __DIR__ . '/../Views/auth/login.php';
        $data_for_view = compact('pageTitle', 'mensaje_error', 'mensaje_exito');
        extract($data_for_view);
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $email_val = $email; // Para repoblar el campo email en caso de error

        if (empty($email) || empty($password)) {
            $this->showLoginForm("Todos los campos son obligatorios.", null, ['email_val' => $email_val]);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol'] = $user['rol']; // <--- GUARDAR ROL EN SESIÓN

            // Redirigir según el rol
            if ($user['rol'] === 'admin') {
                header("Location: " . BASE_URL . "dashboard");
            } else {
                // Para cualquier otro rol (ej. 'cliente')
                header("Location: " . BASE_URL . "portal_servicios"); // <--- NUEVA RUTA PARA CLIENTES
            }
            exit;
        } else {
            $this->showLoginForm("Correo electrónico o contraseña incorrectos.", null, ['email_val' => $email_val]);
        }
    }

    public function showRegisterForm($mensaje_error = null, $datos_previos = [], $mensaje_exito = null) {
         // Si el usuario ya está logueado, redirigir según rol
        if (isset($_SESSION['usuario_id'])) {
            if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
                header("Location: " . BASE_URL . "dashboard");
            } else {
                header("Location: " . BASE_URL . "portal_servicios");
            }
            exit;
        }
        $pageTitle = "Registrarse";
        $viewPath = __DIR__ . '/../Views/auth/register.php';
        $data_for_view = compact('pageTitle', 'mensaje_error', 'datos_previos', 'mensaje_exito');
        extract($data_for_view);
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    public function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "register");
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $fotografia_info = $_FILES['fotografia'] ?? null;
        $nombre_foto = null;

        $datos_previos = ['nombre' => $nombre, 'email' => $email]; // Para repoblar el formulario

        if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
            $this->showRegisterForm("Todos los campos marcados con * son obligatorios.", $datos_previos);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showRegisterForm("El formato del correo electrónico no es válido.", $datos_previos);
            return;
        }
        if (strlen($password) < 6) {
            $this->showRegisterForm("La contraseña debe tener al menos 6 caracteres.", $datos_previos);
            return;
        }
        if ($password !== $confirm_password) {
            $this->showRegisterForm("Las contraseñas no coinciden.", $datos_previos);
            return;
        }
        
        if ($this->userModel->emailExists($email)) {
            $this->showRegisterForm("El correo electrónico ya está registrado. Intenta con otro.", $datos_previos);
            return;
        }

        // Manejo de subida de fotografía
        if ($fotografia_info && $fotografia_info['error'] == UPLOAD_ERR_OK) {
            $uploadDir = defined('APP_UPLOAD_DIR') ? APP_UPLOAD_DIR . DIRECTORY_SEPARATOR : __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
                error_log("Error: No se pudo crear el directorio de subidas: " . $uploadDir);
                $this->showRegisterForm("Error del servidor al procesar la imagen (directorio).", $datos_previos);
                return;
            }
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array(strtolower($fotografia_info['type']), $allowedTypes) && !in_array(strtolower(mime_content_type($fotografia_info['tmp_name'])), $allowedTypes)) {
                 $this->showRegisterForm("Tipo de archivo de imagen no permitido. Solo JPG, PNG, GIF.", $datos_previos);
                return;
            }
            if ($fotografia_info['size'] > 2097152) { // 2MB
                $this->showRegisterForm("El archivo de imagen es demasiado grande (máx 2MB).", $datos_previos);
                return;
            }
            $extension = strtolower(pathinfo($fotografia_info['name'], PATHINFO_EXTENSION));
            $nombre_foto = uniqid('user_', true) . '.' . $extension;
            $ruta_destino = $uploadDir . $nombre_foto;

            if (!move_uploaded_file($fotografia_info['tmp_name'], $ruta_destino)) {
                error_log("Error al mover el archivo subido a: " . $ruta_destino . " - Error PHP: " . $fotografia_info['error']);
                $nombre_foto = null; 
                $this->showRegisterForm("Error al guardar la imagen de perfil.", $datos_previos);
                return;
            }
        } elseif ($fotografia_info && $fotografia_info['error'] != UPLOAD_ERR_NO_FILE) {
            $this->showRegisterForm("Error al subir la imagen: código " . $fotografia_info['error'], $datos_previos);
            return;
        }

        // Crear usuario con rol 'cliente' por defecto
        $userId = $this->userModel->create($nombre, $email, $password, 'cliente', $nombre_foto); // <--- ROL 'cliente' PASADO EXPLÍCITAMENTE

        if ($userId) {
            // Opcional: Iniciar sesión automáticamente después del registro
            // $_SESSION['usuario_id'] = $userId;
            // $_SESSION['usuario_nombre'] = $nombre;
            // $_SESSION['usuario_rol'] = 'cliente'; // Asignar rol cliente
            // header("Location: " . BASE_URL . "portal_servicios");
            // exit;
            
            // O redirigir a login con mensaje de éxito
            header("Location: " . BASE_URL . "login?mensaje_exito=" . urlencode("¡Registro exitoso! Ahora puedes iniciar sesión."));
            exit;
        } else {
            // Si la creación del usuario falla, eliminar la foto si se subió
            if ($nombre_foto && file_exists($uploadDir . $nombre_foto)) {
                unlink($uploadDir . $nombre_foto);
            }
            $this->showRegisterForm("Error al registrar el usuario. Inténtalo de nuevo.", $datos_previos);
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "login?mensaje_exito=" . urlencode("Has cerrado sesión correctamente."));
        exit;
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>